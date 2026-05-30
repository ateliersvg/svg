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
        $parsing = true;

        while ($parsing && $i < count($tokens)) {
            $command = $tokens[$i];
            ++$i;

            // Skip if not a command
            if (!$this->isCommand($command)) {
                continue;
            }

            // Handle the command and any implicit repeats
            do {
                $tokenCount = count($tokens);

                switch (strtoupper($command)) {
                    case 'M': // MoveTo
                        if ($i + 2 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
                        $x = (float) $tokens[$i++];
                        $y = (float) $tokens[$i++];
                        $segments[] = new MoveTo($command, new Point($x, $y));
                        // Per SVG spec, implicit repeats after M become L
                        $command = ctype_upper($command) ? 'L' : 'l';
                        break;

                    case 'L': // LineTo
                        if ($i + 2 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
                        $x = (float) $tokens[$i++];
                        $y = (float) $tokens[$i++];
                        $segments[] = new LineTo($command, new Point($x, $y));
                        break;

                    case 'H': // HorizontalLineTo
                        if ($i + 1 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
                        $x = (float) $tokens[$i++];
                        $segments[] = new HorizontalLineTo($command, $x);
                        break;

                    case 'V': // VerticalLineTo
                        if ($i + 1 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
                        $y = (float) $tokens[$i++];
                        $segments[] = new VerticalLineTo($command, $y);
                        break;

                    case 'C': // CurveTo
                        if ($i + 6 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
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
                        if ($i + 4 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
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
                        if ($i + 4 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
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
                        if ($i + 2 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
                        $x = (float) $tokens[$i++];
                        $y = (float) $tokens[$i++];
                        $segments[] = new SmoothQuadraticCurveTo($command, new Point($x, $y));
                        break;

                    case 'A': // ArcTo
                        if ($i + 7 > $tokenCount) {
                            $parsing = false;
                            break;
                        }
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
            } while ($parsing && 'Z' !== strtoupper($command) && $i < count($tokens) && !$this->isCommand($tokens[$i]));
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
        // Match command letters and SVG numbers directly. Numbers may abut without a
        // separator when the boundary is unambiguous (e.g. ".97-.105" or "0.5.5"), so a
        // whitespace/comma split is not enough: the grammar itself marks the boundary,
        // a sign or a second decimal point starts a new number. The exponent sign of
        // scientific notation must stay inside the number, hence the trailing group.
        $pattern = '/[MmLlHhVvCcSsQqTtAaZz]|[+-]?(?:\d*\.\d+|\d+\.?)(?:[eE][+-]?\d+)?/';
        $matched = preg_match_all($pattern, $pathData, $matches);
        assert(false !== $matched);

        // Arc flags are the one boundary the grammar cannot express on its own,
        // so they are split afterwards, with the command context at hand.
        return $this->expandArcFlags($matches[0]);
    }

    /**
     * Split glued arc flags into single-character tokens.
     *
     * In an arc command the large-arc and sweep flags are each a single "0" or
     * "1" and may abut the following parameter with no separator (e.g. minified
     * "a6 4 10 0114 10" packs flags 0 and 1 in front of the coordinate 14). The
     * tokenizer cannot see this because a flag slot is only special given the
     * command context, so the split is done here using the per-arc parameter
     * index (7 parameters per arc, flags at positions 3 and 4).
     *
     * @param array<string> $tokens
     *
     * @return array<string>
     */
    private function expandArcFlags(array $tokens): array
    {
        $result = [];
        $command = null;
        $argIndex = 0;

        foreach ($tokens as $token) {
            if ($this->isCommand($token)) {
                $command = $token;
                $argIndex = 0;
                $result[] = $token;
                continue;
            }

            if (null === $command || 'A' !== strtoupper($command)) {
                $result[] = $token;
                ++$argIndex;
                continue;
            }

            // Drain the token: a flag slot consumes one character, every other
            // slot consumes the whole remaining token.
            $buffer = $token;
            while ('' !== $buffer) {
                $position = $argIndex % 7;
                if (3 === $position || 4 === $position) {
                    $result[] = $buffer[0];
                    $buffer = substr($buffer, 1);
                } else {
                    $result[] = $buffer;
                    $buffer = '';
                }
                ++$argIndex;
            }
        }

        return $result;
    }

    /**
     * Check if a token is a path command.
     */
    private function isCommand(string $token): bool
    {
        return 1 === preg_match('/^[MmLlHhVvCcSsQqTtAaZz]$/', $token);
    }
}
