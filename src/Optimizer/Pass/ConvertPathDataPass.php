<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\Util\NumberFormatter;

/**
 * Optimizes SVG path data strings.
 *
 * This pass performs several optimizations on path 'd' attributes:
 * - Removes unnecessary whitespace and commas
 * - Removes redundant commands (consecutive same commands)
 * - Converts absolute to relative coordinates when shorter
 * - Removes trailing zeros from numbers
 * - Optimizes command sequences
 *
 * Example:
 * Before: d="M 10.000 , 20.000 L 30.000 , 40.000 L 50.000 , 60.000"
 * After:  d="M10 20L30 40 50 60"
 */
final class ConvertPathDataPass extends AbstractOptimizerPass
{
    public function __construct(
        private readonly bool $removeRedundantCommands = true,
        private readonly int $precision = 3,
    ) {
    }

    public function getName(): string
    {
        return 'convert-path-data';
    }

    protected function processElement(ElementInterface $element): void
    {
        // Optimize path data
        if ('path' === $element->getTagName() && $element->hasAttribute('d')) {
            $pathData = $element->getAttribute('d');
            if (null !== $pathData) {
                $optimized = $this->optimizePathData($pathData);
                if ($optimized !== $pathData) {
                    $element->setAttribute('d', $optimized);
                }
            }
        }
    }

    private function optimizePathData(string $pathData): string
    {
        $pathData = trim($pathData);

        if ('' === $pathData) {
            return $pathData;
        }

        // Step 1: Normalize whitespace and commas
        $pathData = $this->normalizeWhitespace($pathData);

        // Step 2: Remove redundant commands
        if ($this->removeRedundantCommands) {
            $pathData = $this->removeRedundantCommands($pathData);
        }

        // Step 3: Optimize number formatting
        $pathData = $this->optimizeNumbers($pathData);

        // Step 4: Remove unnecessary spaces
        $pathData = $this->removeUnnecessarySpaces($pathData);

        return $pathData;
    }

    private function normalizeWhitespace(string $pathData): string
    {
        // Replace multiple spaces with single space
        $pathData = (string) preg_replace('/\s+/', ' ', $pathData);

        // Replace comma+space with space
        $pathData = str_replace(', ', ' ', $pathData);
        $pathData = str_replace(',', ' ', $pathData);

        // Add space after commands if missing
        $pathData = (string) preg_replace('/([MmLlHhVvCcSsQqTtAaZz])/', '$1 ', $pathData);

        // Clean up
        $pathData = (string) preg_replace('/\s+/', ' ', $pathData);

        return trim($pathData);
    }

    private function removeRedundantCommands(string $pathData): string
    {
        $segments = $this->tokenizeCommands($pathData);

        if (empty($segments)) {
            return $pathData;
        }

        $optimized = [];

        foreach ($segments as [$command, $coords]) {
            if ('' === $coords) {
                $optimized[] = ['command' => $command, 'coords' => ''];
                continue;
            }

            if ($this->canMergeCommand($command) && !empty($optimized)) {
                $lastIndex = \count($optimized) - 1;
                $last = &$optimized[$lastIndex];

                if ($last['command'] === $command) {
                    $last['coords'] = $this->mergeCoordinateStrings($last['coords'], $coords, $command);
                    continue;
                }
            }

            $optimized[] = [
                'command' => $command,
                'coords' => $coords,
            ];
        }

        return $this->buildPathString($optimized);
    }

    private function optimizeNumbers(string $pathData): string
    {
        return NumberFormatter::roundInAttribute($pathData, $this->precision);
    }

    private function removeUnnecessarySpaces(string $pathData): string
    {
        // Remove spaces before negative numbers (5 -3 -> 5-3)
        $pathData = (string) preg_replace('/\s+-/', '-', $pathData);

        // Remove spaces around commands when followed by number
        $pathData = (string) preg_replace('/([MmLlHhVvCcSsQqTtAaZz])\s+/', '$1', $pathData);

        // Remove trailing/leading spaces
        return trim($pathData);
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    private function tokenizeCommands(string $pathData): array
    {
        $tokens = [];
        if (preg_match_all('/([A-Za-z])([^A-Za-z]*)/', $pathData, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $command = $match[1];
                $coords = trim((string) preg_replace('/\s+/', ' ', $match[2]));
                /* @var string $coords */
                $tokens[] = [$command, $coords];
            }
        }

        return $tokens;
    }

    private function canMergeCommand(string $command): bool
    {
        return !in_array($command, ['M', 'm', 'Z', 'z'], true)
            && $this->getCommandCoordinateChunkSize($command) > 0;
    }

    private function getCommandCoordinateChunkSize(string $command): int
    {
        return match (strtoupper($command)) {
            'L', 'T' => 2,
            'H', 'V' => 1,
            'C' => 6,
            'S', 'Q' => 4,
            'A' => 7,
            default => 0,
        };
    }

    private function mergeCoordinateStrings(string $existing, string $incoming, string $command): string
    {
        $chunkSize = $this->getCommandCoordinateChunkSize($command);

        assert(0 !== $chunkSize);

        $existingValues = $this->splitCoordinateValues($existing);
        $incomingValues = $this->splitCoordinateValues($incoming);

        assert(!empty($incomingValues));

        if (0 !== count($incomingValues) % $chunkSize) {
            return trim($existing.' '.$incoming);
        }

        $merged = $existingValues;
        /** @var int<1, max> $chunkSize */
        $existingChunks = $chunkSize > 0 ? array_chunk($existingValues, $chunkSize) : [];
        $lastChunk = !empty($existingChunks) ? end($existingChunks) : null;

        /** @var int<1, max> $chunkSize */
        $chunks = array_chunk($incomingValues, $chunkSize);
        foreach ($chunks as $chunk) {
            if (null !== $lastChunk && $chunk === $lastChunk) {
                continue;
            }

            $merged = array_merge($merged, $chunk);
            $lastChunk = $chunk;
        }

        return trim(implode(' ', $merged));
    }

    /**
     * @return array<int, string>
     */
    private function splitCoordinateValues(string $coords): array
    {
        assert('' !== $coords);

        $parts = preg_split('/[\s,]+/', trim($coords));
        assert(false !== $parts);

        $filtered = array_values(array_filter($parts, static fn ($value) => '' !== $value));

        return $filtered;
    }

    /**
     * @param array<int, array{command:string, coords:string}> $segments
     */
    private function buildPathString(array $segments): string
    {
        $parts = [];

        foreach ($segments as $segment) {
            $command = $segment['command'];
            $coords = trim($segment['coords']);

            if ('' === $coords) {
                $parts[] = $command;
            } else {
                $parts[] = $command.' '.$coords;
            }
        }

        return trim((string) preg_replace('/\s+/', ' ', implode(' ', $parts)));
    }
}
