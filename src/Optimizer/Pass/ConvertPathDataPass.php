<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Optimizer\Util\NumberFormatter;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SegmentInterface;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;

/**
 * Optimizes SVG path data strings.
 *
 * This pass performs several optimizations on path 'd' attributes:
 * - Converts absolute coordinates to relative when shorter (and vice versa)
 * - Removes unnecessary whitespace and commas
 * - Removes redundant commands (consecutive same commands)
 * - Rounds numbers to the configured precision
 * - Converts L to H/V shorthand when applicable
 * - Removes unnecessary spaces between numbers
 *
 * Uses the parsed path infrastructure (PathParser, Segment objects) for
 * accurate per-segment absolute/relative comparison.
 *
 * Example:
 * Before: d="M 100.000 , 200.000 L 105.000 , 205.000 L 110.000 , 205.000"
 * After:  d="M100 200l5 5h5"
 */
final class ConvertPathDataPass extends AbstractOptimizerPass
{
    private readonly PathParser $parser;

    public function __construct(
        private readonly bool $removeRedundantCommands = true,
        private readonly int $precision = 3,
    ) {
        $this->parser = new PathParser();
    }

    public function getName(): string
    {
        return 'convert-path-data';
    }

    protected function processElement(ElementInterface $element): void
    {
        if ('path' === $element->getTagName() && $element->hasAttribute('d')) {
            $pathData = $element->getAttribute('d');
            if (null !== $pathData && '' !== trim($pathData)) {
                $optimized = $this->optimizePathData($pathData);
                if ($optimized !== $pathData) {
                    $element->setAttribute('d', $optimized);
                }
            }
        }
    }

    private function optimizePathData(string $pathData): string
    {
        $data = $this->parser->parse($pathData);

        $segments = $data->getSegments();
        if ([] === $segments) {
            return $pathData;
        }

        return $this->serializeOptimized($segments);
    }

    /**
     * Serializes segments with per-segment abs/rel comparison.
     *
     * For each segment, compute both the absolute and relative representations,
     * format them with precision, and pick the shorter one.
     *
     * @param SegmentInterface[] $segments
     */
    private function serializeOptimized(array $segments): string
    {
        $result = '';
        $currentPoint = new Point(0, 0);
        $subpathStart = new Point(0, 0);
        $lastCommand = '';
        $prevAbsCp2 = null;  // Last cubic CP2 (absolute) for C-to-S detection
        $prevAbsQCp = null;  // Last quadratic CP (absolute) for Q-to-T detection

        $epsilon = 10 ** -$this->precision;

        foreach ($segments as $segment) {
            $absStr = '';
            $relStr = '';
            $absCmd = '';
            $relCmd = '';
            $newCurrentPoint = $currentPoint;
            $newPrevAbsCp2 = null;
            $newPrevAbsQCp = null;

            if ($segment instanceof MoveTo) {
                $point = $this->resolveAbsolutePoint($segment, $currentPoint);
                $relPoint = $point->subtract($currentPoint);

                $absCmd = 'M';
                $absStr = $this->formatPoint($point);
                $relCmd = 'm';
                $relStr = $this->formatPoint($relPoint);

                $newCurrentPoint = $point;
                $subpathStart = $point;
            } elseif ($segment instanceof LineTo) {
                $point = $this->resolveAbsolutePoint($segment, $currentPoint);
                $relPoint = $point->subtract($currentPoint);

                // Try H/V shorthand
                $hShorthand = $this->tryHVShorthand($currentPoint, $point, $relPoint);
                if (null !== $hShorthand) {
                    $chosen = $this->chooseAndAppend($hShorthand[0], $hShorthand[1], $hShorthand[2], $hShorthand[3], $lastCommand, $this->removeRedundantCommands, $segment->isRelative());
                    $result .= $chosen[0];
                    $lastCommand = $chosen[1];
                    $currentPoint = $point;
                    $prevAbsCp2 = null;
                    $prevAbsQCp = null;
                    continue;
                }

                $absCmd = 'L';
                $absStr = $this->formatPoint($point);
                $relCmd = 'l';
                $relStr = $this->formatPoint($relPoint);

                $newCurrentPoint = $point;
            } elseif ($segment instanceof HorizontalLineTo) {
                $x = $segment->getX();
                $absX = $segment->isRelative() ? $currentPoint->x + $x : $x;
                $relX = $absX - $currentPoint->x;

                $absCmd = 'H';
                $absStr = $this->fmt($absX);
                $relCmd = 'h';
                $relStr = $this->fmt($relX);

                $newCurrentPoint = new Point($absX, $currentPoint->y);
            } elseif ($segment instanceof VerticalLineTo) {
                $y = $segment->getY();
                $absY = $segment->isRelative() ? $currentPoint->y + $y : $y;
                $relY = $absY - $currentPoint->y;

                $absCmd = 'V';
                $absStr = $this->fmt($absY);
                $relCmd = 'v';
                $relStr = $this->fmt($relY);

                $newCurrentPoint = new Point($currentPoint->x, $absY);
            } elseif ($segment instanceof CurveTo) {
                $cp1 = $segment->getControlPoint1();
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();

                if ($segment->isRelative()) {
                    $absCp1 = $currentPoint->add($cp1);
                    $absCp2 = $currentPoint->add($cp2);
                    $absPoint = $currentPoint->add($point);
                } else {
                    $absCp1 = $cp1;
                    $absCp2 = $cp2;
                    $absPoint = $point;
                }

                $relCp1 = $absCp1->subtract($currentPoint);
                $relCp2 = $absCp2->subtract($currentPoint);
                $relPoint = $absPoint->subtract($currentPoint);
                $newCurrentPoint = $absPoint;
                $newPrevAbsCp2 = $absCp2;

                // Try C-to-Q: cubic that is actually quadratic
                $qResult = $this->tryCubicToQuadratic($currentPoint, $absCp1, $absCp2, $absPoint, $epsilon);
                if (null !== $qResult) {
                    $absQCp = $qResult;
                    $relQCp = $absQCp->subtract($currentPoint);

                    $qAbsStr = $this->formatPoints($absQCp, $absPoint);
                    $qRelStr = $this->formatPoints($relQCp, $relPoint);

                    // Compare C vs Q: pick the shorter overall
                    $cAbsStr = $this->formatPoints($absCp1, $absCp2, $absPoint);
                    $cRelStr = $this->formatPoints($relCp1, $relCp2, $relPoint);
                    $bestC = $this->shortestOf('C', $cAbsStr, 'c', $cRelStr, $segment->isRelative());
                    $bestQ = $this->shortestOf('Q', $qAbsStr, 'q', $qRelStr, $segment->isRelative());

                    if (\strlen($bestQ[0].$bestQ[1]) <= \strlen($bestC[0].$bestC[1])) {
                        $absCmd = 'Q';
                        $absStr = $qAbsStr;
                        $relCmd = 'q';
                        $relStr = $qRelStr;
                        $newPrevAbsQCp = $absQCp;
                        $newPrevAbsCp2 = null;

                        $chosen = $this->chooseAndAppend($absCmd, $absStr, $relCmd, $relStr, $lastCommand, $this->removeRedundantCommands, $segment->isRelative());
                        $result .= $chosen[0];
                        $lastCommand = $chosen[1];
                        $currentPoint = $newCurrentPoint;
                        $prevAbsCp2 = $newPrevAbsCp2;
                        $prevAbsQCp = $newPrevAbsQCp;
                        continue;
                    }
                }

                // Try C-to-S: smooth cubic when CP1 is reflection of previous CP2
                if (null !== $prevAbsCp2) {
                    $reflected = new Point(
                        2 * $currentPoint->x - $prevAbsCp2->x,
                        2 * $currentPoint->y - $prevAbsCp2->y,
                    );
                    if ($absCp1->equals($reflected, $epsilon)) {
                        $sAbsStr = $this->formatPoints($absCp2, $absPoint);
                        $sRelStr = $this->formatPoints($relCp2, $relPoint);

                        $cAbsStr = $this->formatPoints($absCp1, $absCp2, $absPoint);
                        $cRelStr = $this->formatPoints($relCp1, $relCp2, $relPoint);
                        $bestC = $this->shortestOf('C', $cAbsStr, 'c', $cRelStr, $segment->isRelative());
                        $bestS = $this->shortestOf('S', $sAbsStr, 's', $sRelStr, $segment->isRelative());

                        if (\strlen($bestS[0].$bestS[1]) <= \strlen($bestC[0].$bestC[1])) {
                            $absCmd = 'S';
                            $absStr = $sAbsStr;
                            $relCmd = 's';
                            $relStr = $sRelStr;

                            $chosen = $this->chooseAndAppend($absCmd, $absStr, $relCmd, $relStr, $lastCommand, $this->removeRedundantCommands, $segment->isRelative());
                            $result .= $chosen[0];
                            $lastCommand = $chosen[1];
                            $currentPoint = $newCurrentPoint;
                            $prevAbsCp2 = $newPrevAbsCp2;
                            $prevAbsQCp = null;
                            continue;
                        }
                    }
                }

                // Default: emit as C
                $absCmd = 'C';
                $absStr = $this->formatPoints($absCp1, $absCp2, $absPoint);
                $relCmd = 'c';
                $relStr = $this->formatPoints($relCp1, $relCp2, $relPoint);
            } elseif ($segment instanceof SmoothCurveTo) {
                $cp2 = $segment->getControlPoint2();
                $point = $segment->getTargetPoint();

                if ($segment->isRelative()) {
                    $absCp2 = $currentPoint->add($cp2);
                    $absPoint = $currentPoint->add($point);
                    $relCp2 = $cp2;
                    $relPoint = $point;
                } else {
                    $absCp2 = $cp2;
                    $absPoint = $point;
                    $relCp2 = $cp2->subtract($currentPoint);
                    $relPoint = $point->subtract($currentPoint);
                }

                $absCmd = 'S';
                $absStr = $this->formatPoints($absCp2, $absPoint);
                $relCmd = 's';
                $relStr = $this->formatPoints($relCp2, $relPoint);

                $newCurrentPoint = $absPoint;
                $newPrevAbsCp2 = $absCp2;
            } elseif ($segment instanceof QuadraticCurveTo) {
                $cp = $segment->getControlPoint();
                $point = $segment->getTargetPoint();

                if ($segment->isRelative()) {
                    $absCp = $currentPoint->add($cp);
                    $absPoint = $currentPoint->add($point);
                    $relCp = $cp;
                    $relPoint = $point;
                } else {
                    $absCp = $cp;
                    $absPoint = $point;
                    $relCp = $cp->subtract($currentPoint);
                    $relPoint = $point->subtract($currentPoint);
                }

                $newCurrentPoint = $absPoint;
                $newPrevAbsQCp = $absCp;

                // Try Q-to-T: smooth quadratic when CP is reflection of previous QCP
                if (null !== $prevAbsQCp) {
                    $reflected = new Point(
                        2 * $currentPoint->x - $prevAbsQCp->x,
                        2 * $currentPoint->y - $prevAbsQCp->y,
                    );
                    if ($absCp->equals($reflected, $epsilon)) {
                        $tAbsStr = $this->formatPoint($absPoint);
                        $tRelStr = $this->formatPoint($relPoint);

                        $qAbsStr = $this->formatPoints($absCp, $absPoint);
                        $qRelStr = $this->formatPoints($relCp, $relPoint);
                        $bestQ = $this->shortestOf('Q', $qAbsStr, 'q', $qRelStr, $segment->isRelative());
                        $bestT = $this->shortestOf('T', $tAbsStr, 't', $tRelStr, $segment->isRelative());

                        if (\strlen($bestT[0].$bestT[1]) <= \strlen($bestQ[0].$bestQ[1])) {
                            $absCmd = 'T';
                            $absStr = $tAbsStr;
                            $relCmd = 't';
                            $relStr = $tRelStr;

                            $chosen = $this->chooseAndAppend($absCmd, $absStr, $relCmd, $relStr, $lastCommand, $this->removeRedundantCommands, $segment->isRelative());
                            $result .= $chosen[0];
                            $lastCommand = $chosen[1];
                            $currentPoint = $newCurrentPoint;
                            $prevAbsCp2 = null;
                            $prevAbsQCp = $newPrevAbsQCp;
                            continue;
                        }
                    }
                }

                $absCmd = 'Q';
                $absStr = $this->formatPoints($absCp, $absPoint);
                $relCmd = 'q';
                $relStr = $this->formatPoints($relCp, $relPoint);
            } elseif ($segment instanceof SmoothQuadraticCurveTo) {
                $point = $segment->getTargetPoint();

                if ($segment->isRelative()) {
                    $absPoint = $currentPoint->add($point);
                    $relPoint = $point;
                } else {
                    $absPoint = $point;
                    $relPoint = $point->subtract($currentPoint);
                }

                $absCmd = 'T';
                $absStr = $this->formatPoint($absPoint);
                $relCmd = 't';
                $relStr = $this->formatPoint($relPoint);

                $newCurrentPoint = $absPoint;
                // T implicitly reflects the previous Q control point
                if (null !== $prevAbsQCp) {
                    $newPrevAbsQCp = new Point(
                        2 * $currentPoint->x - $prevAbsQCp->x,
                        2 * $currentPoint->y - $prevAbsQCp->y,
                    );
                }
            } elseif ($segment instanceof ArcTo) {
                $point = $segment->getTargetPoint();
                $arcParams = $this->formatArcParams($segment);

                if ($segment->isRelative()) {
                    $absPoint = $currentPoint->add($point);
                    $relPoint = $point;
                } else {
                    $absPoint = $point;
                    $relPoint = $point->subtract($currentPoint);
                }

                $absCmd = 'A';
                $absStr = $arcParams.$this->formatArcEndpoint($absPoint);
                $relCmd = 'a';
                $relStr = $arcParams.$this->formatArcEndpoint($relPoint);

                $newCurrentPoint = $absPoint;
            } elseif ($segment instanceof ClosePath) {
                $result .= 'Z';
                $lastCommand = 'Z';
                $currentPoint = $subpathStart;
                $prevAbsCp2 = null;
                $prevAbsQCp = null;
                continue;
            }

            $chosen = $this->chooseAndAppend($absCmd, $absStr, $relCmd, $relStr, $lastCommand, $this->removeRedundantCommands, $segment->isRelative());
            $result .= $chosen[0];
            $lastCommand = $chosen[1];
            $currentPoint = $newCurrentPoint;
            $prevAbsCp2 = $newPrevAbsCp2;
            $prevAbsQCp = $newPrevAbsQCp;
        }

        return $result;
    }

    /**
     * Choose the shorter representation (abs vs rel) and format with optional command elision.
     *
     * When lengths are equal, prefers the original representation (abs or rel).
     *
     * @return array{0: string, 1: string} [serialized string, chosen command]
     */
    private function chooseAndAppend(string $absCmd, string $absStr, string $relCmd, string $relStr, string $lastCommand, bool $elideRedundant, bool $preferRelative = false): array
    {
        $absTotal = $absCmd.$absStr;
        $relTotal = $relCmd.$relStr;

        // Pick shorter; on tie, prefer the original representation
        if (\strlen($relTotal) < \strlen($absTotal) || (\strlen($relTotal) === \strlen($absTotal) && $preferRelative)) {
            $chosenCmd = $relCmd;
            $chosenStr = $relStr;
        } else {
            $chosenCmd = $absCmd;
            $chosenStr = $absStr;
        }

        // Elide command letter if same as previous
        if ($elideRedundant && $chosenCmd === $lastCommand && !in_array($chosenCmd, ['M', 'm', 'Z', 'z'], true)) {
            // Separator: space if coords don't start with '-' or '.'
            $sep = $this->needsSeparator($chosenStr) ? ' ' : '';

            return [$sep.$chosenStr, $chosenCmd];
        }

        return [$chosenCmd.$chosenStr, $chosenCmd];
    }

    /**
     * Check if we need a separator before coordinate string (for command elision).
     *
     * Only '-' is safe as implicit separator here because we don't know
     * whether the previous result's last number contains a dot.
     */
    private function needsSeparator(string $coords): bool
    {
        return '-' !== $coords[0];
    }

    /**
     * Resolve a segment's endpoint to absolute coordinates.
     */
    private function resolveAbsolutePoint(SegmentInterface $segment, Point $currentPoint): Point
    {
        /** @var Point $point */
        $point = $segment->getTargetPoint();

        return $segment->isRelative() ? $currentPoint->add($point) : $point;
    }

    /**
     * Try to use H/V shorthand for a line segment.
     *
     * @return array{0: string, 1: string, 2: string, 3: string}|null [absCmd, absStr, relCmd, relStr] or null
     */
    private function tryHVShorthand(Point $currentPoint, Point $absPoint, Point $relPoint): ?array
    {
        $dx = $relPoint->x;
        $dy = $relPoint->y;

        $epsilon = 10 ** -($this->precision + 1);

        if (abs($dy) < $epsilon) {
            // Horizontal line
            return ['H', $this->fmt($absPoint->x), 'h', $this->fmt($dx)];
        }

        if (abs($dx) < $epsilon) {
            // Vertical line
            return ['V', $this->fmt($absPoint->y), 'v', $this->fmt($dy)];
        }

        return null;
    }

    private function formatArcParams(ArcTo $arc): string
    {
        $rx = $this->fmt($arc->getRx());
        $ry = $this->fmt($arc->getRy());
        $rot = $this->fmt($arc->getXAxisRotation());
        $la = $arc->getLargeArcFlag() ? '1' : '0';
        $sw = $arc->getSweepFlag() ? '1' : '0';

        // Compact: use minimal separators, flags are single digits with no separator between them
        return $this->compactJoinValues($rx, $ry, $rot).' '.$la.$sw;
    }

    /**
     * Format arc endpoint with compact separator (flags followed by coords).
     */
    private function formatArcEndpoint(Point $point): string
    {
        $x = $this->fmt($point->x);
        $y = $this->fmt($point->y);

        // After sweep flag (0/1), negative/dot starts can serve as separator
        $sep = (str_starts_with($x, '-') || str_starts_with($x, '.')) ? '' : ' ';

        return $sep.$this->compactJoinValues($x, $y);
    }

    private function formatPoint(Point $point): string
    {
        return $this->formatPoints($point);
    }

    /**
     * Formats multiple points with compact separators between all values.
     *
     * Uses negative sign and dot as implicit separators where safe.
     */
    private function formatPoints(Point ...$points): string
    {
        $values = [];
        foreach ($points as $point) {
            $values[] = $this->fmt($point->x);
            $values[] = $this->fmt($point->y);
        }

        return $this->compactJoinValues(...$values);
    }

    /**
     * Returns the minimal separator needed between two formatted number strings.
     *
     * - No separator when the next value starts with '-' (sign is implicit separator)
     * - No separator when the next value starts with '.' AND the previous value
     *   already contains a '.', because the parser splits on the second dot
     * - Otherwise a space is needed
     */
    private function compactSeparator(string $prev, string $next): string
    {
        if (str_starts_with($next, '-')) {
            return '';
        }

        if (str_starts_with($next, '.') && str_contains($prev, '.')) {
            return '';
        }

        return ' ';
    }

    /**
     * Joins multiple formatted number strings with minimal separators.
     */
    private function compactJoinValues(string ...$values): string
    {
        $result = $values[0];
        for ($i = 1, $n = count($values); $i < $n; ++$i) {
            $result .= $this->compactSeparator($values[$i - 1], $values[$i]).$values[$i];
        }

        return $result;
    }

    private function fmt(float $value): string
    {
        return NumberFormatter::format($value, $this->precision, true);
    }

    /**
     * Detect if a cubic bezier is actually a quadratic bezier.
     *
     * A cubic C(P0, CP1, CP2, P3) is quadratic when:
     * CP1 = P0 + 2/3 * (QCP - P0) and CP2 = P3 + 2/3 * (QCP - P3)
     * We derive QCP from both equations and verify they match.
     *
     * @return Point|null The quadratic control point, or null if not quadratic
     */
    private function tryCubicToQuadratic(Point $p0, Point $absCp1, Point $absCp2, Point $p3, float $epsilon): ?Point
    {
        // QCP = (3*CP1 - P0) / 2
        $qcp1 = new Point(
            (3 * $absCp1->x - $p0->x) / 2,
            (3 * $absCp1->y - $p0->y) / 2,
        );

        // QCP = (3*CP2 - P3) / 2
        $qcp2 = new Point(
            (3 * $absCp2->x - $p3->x) / 2,
            (3 * $absCp2->y - $p3->y) / 2,
        );

        if ($qcp1->equals($qcp2, $epsilon)) {
            // Average for best precision
            return new Point(
                ($qcp1->x + $qcp2->x) / 2,
                ($qcp1->y + $qcp2->y) / 2,
            );
        }

        return null;
    }

    /**
     * Pick the shorter of abs/rel representations.
     *
     * @return array{0: string, 1: string} [command, args]
     */
    private function shortestOf(string $absCmd, string $absStr, string $relCmd, string $relStr, bool $preferRelative): array
    {
        $absLen = \strlen($absCmd) + \strlen($absStr);
        $relLen = \strlen($relCmd) + \strlen($relStr);

        if ($relLen < $absLen || ($relLen === $absLen && $preferRelative)) {
            return [$relCmd, $relStr];
        }

        return [$absCmd, $absStr];
    }
}
