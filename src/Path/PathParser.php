<?php

declare(strict_types=1);

namespace Atelier\Svg\Path;

use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;

/**
 * Parses SVG path data strings into Data objects.
 *
 * Handles all SVG path commands (M, L, C, Q, A, Z, etc.)
 * in both absolute and relative forms.
 */
final class PathParser
{
    /**
     * Parse a path data string into a Data object.
     *
     * @param string $pathData The SVG path data string (e.g., "M 10,10 L 50,50 Z")
     */
    public function parse(string $pathData): Data
    {
        $segments = [];
        $pathData = trim($pathData);

        if (empty($pathData)) {
            return new Data([]);
        }

        // Tokenize the path data
        $tokens = $this->tokenize($pathData);
        $i = 0;

        while ($i < count($tokens)) {
            $command = $tokens[$i];
            ++$i;

            // Skip if not a command
            if (!$this->isCommand($command)) {
                continue;
            }

            // Parse the segment based on command
            switch (strtoupper($command)) {
                case 'M': // MoveTo
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new MoveTo($command, new Point($x, $y));
                    break;

                case 'L': // LineTo
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new LineTo($command, new Point($x, $y));
                    break;

                case 'H': // HorizontalLineTo
                    $x = (float) $tokens[$i++];
                    $segments[] = new HorizontalLineTo($command, $x);
                    break;

                case 'V': // VerticalLineTo
                    $y = (float) $tokens[$i++];
                    $segments[] = new VerticalLineTo($command, $y);
                    break;

                case 'C': // CurveTo
                    $x1 = (float) $tokens[$i++];
                    $y1 = (float) $tokens[$i++];
                    $x2 = (float) $tokens[$i++];
                    $y2 = (float) $tokens[$i++];
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new CurveTo(
                        $command,
                        new Point($x1, $y1),
                        new Point($x2, $y2),
                        new Point($x, $y)
                    );
                    break;

                case 'S': // SmoothCurveTo
                    $x2 = (float) $tokens[$i++];
                    $y2 = (float) $tokens[$i++];
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new SmoothCurveTo(
                        $command,
                        new Point($x2, $y2),
                        new Point($x, $y)
                    );
                    break;

                case 'Q': // QuadraticCurveTo
                    $x1 = (float) $tokens[$i++];
                    $y1 = (float) $tokens[$i++];
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new QuadraticCurveTo(
                        $command,
                        new Point($x1, $y1),
                        new Point($x, $y)
                    );
                    break;

                case 'T': // SmoothQuadraticCurveTo
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new SmoothQuadraticCurveTo($command, new Point($x, $y));
                    break;

                case 'A': // ArcTo
                    $rx = (float) $tokens[$i++];
                    $ry = (float) $tokens[$i++];
                    $xAxisRotation = (float) $tokens[$i++];
                    $largeArcFlag = (bool) (int) $tokens[$i++];
                    $sweepFlag = (bool) (int) $tokens[$i++];
                    $x = (float) $tokens[$i++];
                    $y = (float) $tokens[$i++];
                    $segments[] = new ArcTo(
                        $command,
                        $rx,
                        $ry,
                        $xAxisRotation,
                        $largeArcFlag,
                        $sweepFlag,
                        new Point($x, $y)
                    );
                    break;

                case 'Z': // ClosePath
                    $segments[] = new ClosePath($command);
                    break;
            }
        }

        return new Data($segments);
    }

    /**
     * Tokenize the path data string into an array of commands and numbers.
     *
     * @return array<string>
     */
    private function tokenize(string $pathData): array
    {
        // Replace command letters with spaces around them for easier splitting
        $pathData = (string) preg_replace('/([MmLlHhVvCcSsQqTtAaZz])/', ' $1 ', $pathData);

        // Replace commas with spaces
        $pathData = str_replace(',', ' ', $pathData);

        // Split by whitespace and filter out empty strings
        $tokens = preg_split('/\s+/', $pathData);
        assert(false !== $tokens);
        $tokens = array_filter($tokens, fn ($token) => '' !== $token);

        return array_values($tokens);
    }

    /**
     * Check if a token is a path command.
     */
    private function isCommand(string $token): bool
    {
        return 1 === preg_match('/^[MmLlHhVvCcSsQqTtAaZz]$/', $token);
    }
}
