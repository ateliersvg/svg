<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that applies transform attributes to element coordinates.
 *
 * This pass converts simple transform operations (translate, scale) into coordinate
 * changes, allowing the transform attribute to be removed. This reduces file size
 * and simplifies the DOM structure.
 *
 * Supports:
 * - translate() on shapes (rect, circle, ellipse, line) and paths
 * - scale() on shapes with position attributes
 *
 * Does not support:
 * - rotate() (too complex, changes coordinates significantly)
 * - Multiple transform operations
 * - skew() transforms
 */
final class ConvertTransformPass extends AbstractOptimizerPass
{
    /**
     * Creates a new ConvertTransformPass.
     *
     * @param bool $convertTranslate Whether to convert translate transforms
     * @param bool $convertScale     Whether to convert scale transforms
     * @param bool $convertRotate    Whether to convert rotate transforms (default: false)
     * @param bool $convertOnPaths   Whether to convert transforms on path elements
     * @param bool $convertOnShapes  Whether to convert transforms on shape elements
     */
    public function __construct(
        private readonly bool $convertTranslate = true,
        private readonly bool $convertScale = true,
        private readonly bool $convertRotate = false,
        private readonly bool $convertOnPaths = true,
        private readonly bool $convertOnShapes = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'convert-transform';
    }

    /**
     * Processes an element to convert transform attributes to coordinate changes.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // Try to convert the transform on this element
        if ($element->hasAttribute('transform')) {
            $this->convertTransform($element);
        }
    }

    /**
     * Attempts to convert a transform attribute on an element.
     *
     * @param ElementInterface $element The element with a transform attribute
     */
    private function convertTransform(ElementInterface $element): void
    {
        $transform = $element->getAttribute('transform');
        assert(null !== $transform);

        // Parse the transform
        $parsedTransform = $this->parseTransform($transform);
        if (null === $parsedTransform) {
            return; // Cannot parse or unsupported
        }

        $tagName = $element->getTagName();
        $converted = false;

        // Try to apply the transform based on element type
        if ($this->isShapeElement($tagName) && $this->convertOnShapes) {
            $converted = $this->applyTransformToShape($element, $parsedTransform);
        } elseif ('path' === $tagName && $this->convertOnPaths) {
            $converted = $this->applyTransformToPath($element, $parsedTransform);
        }

        // If successfully converted, remove the transform attribute
        if ($converted) {
            $element->removeAttribute('transform');
        }
    }

    /**
     * Parses a transform attribute value.
     *
     * @param string $transform The transform attribute value
     *
     * @return array{type: string, values: array<float>}|null Parsed transform or null if unsupported
     */
    private function parseTransform(string $transform): ?array
    {
        $transform = trim($transform);

        // Match translate(x, y) or translate(x)
        if (preg_match('/^translate\s*\(\s*([^,\s]+)(?:\s*,\s*([^)]+))?\s*\)$/', $transform, $matches)) {
            if (!$this->convertTranslate) {
                return null;
            }
            $x = (float) $matches[1];
            $y = isset($matches[2]) ? (float) $matches[2] : 0.0;

            return ['type' => 'translate', 'values' => [$x, $y]];
        }

        // Match scale(x, y) or scale(s)
        if (preg_match('/^scale\s*\(\s*([^,\s]+)(?:\s*,\s*([^)]+))?\s*\)$/', $transform, $matches)) {
            if (!$this->convertScale) {
                return null;
            }
            $sx = (float) $matches[1];
            $sy = isset($matches[2]) ? (float) $matches[2] : $sx;

            return ['type' => 'scale', 'values' => [$sx, $sy]];
        }

        // Match rotate(angle) or rotate(angle, cx, cy)
        if (preg_match('/^rotate\s*\(\s*([^,\s]+)(?:\s*,\s*([^,\s]+)\s*,\s*([^)]+))?\s*\)$/', $transform, $matches)) {
            if (!$this->convertRotate) {
                return null;
            }

            // Rotate is complex and disabled by default
            return null;
        }

        // Multiple transforms or unsupported format
        return null;
    }

    /**
     * Checks if a tag name represents a shape element.
     *
     * @param string $tagName The tag name to check
     *
     * @return bool True if it's a shape element
     */
    private function isShapeElement(string $tagName): bool
    {
        return in_array($tagName, ['rect', 'circle', 'ellipse', 'line'], true);
    }

    /**
     * Applies a transform to a shape element.
     *
     * @param ElementInterface                          $element   The shape element
     * @param array{type: string, values: array<float>} $transform The parsed transform
     *
     * @return bool True if the transform was successfully applied
     */
    private function applyTransformToShape(ElementInterface $element, array $transform): bool
    {
        return match ($transform['type']) {
            'translate' => $this->applyTranslateToShape($element, $transform['values'][0], $transform['values'][1]),
            'scale' => $this->applyScaleToShape($element, $transform['values'][0], $transform['values'][1]),
            default => false,
        };
    }

    /**
     * Applies a translate transform to a shape element.
     *
     * @param ElementInterface $element The shape element
     * @param float            $tx      Translation X
     * @param float            $ty      Translation Y
     *
     * @return bool True if successfully applied
     */
    private function applyTranslateToShape(ElementInterface $element, float $tx, float $ty): bool
    {
        $tagName = $element->getTagName();

        return match ($tagName) {
            'rect' => $this->translateRect($element, $tx, $ty),
            'circle' => $this->translateCircle($element, $tx, $ty),
            'ellipse' => $this->translateEllipse($element, $tx, $ty),
            'line' => $this->translateLine($element, $tx, $ty),
            default => false,
        };
    }

    /**
     * Applies a scale transform to a shape element.
     *
     * @param ElementInterface $element The shape element
     * @param float            $sx      Scale X
     * @param float            $sy      Scale Y
     *
     * @return bool True if successfully applied
     */
    private function applyScaleToShape(ElementInterface $element, float $sx, float $sy): bool
    {
        // Don't scale with negative values (would flip the element)
        if ($sx <= 0 || $sy <= 0) {
            return false;
        }

        $tagName = $element->getTagName();

        return match ($tagName) {
            'rect' => $this->scaleRect($element, $sx, $sy),
            'circle' => $this->scaleCircle($element, $sx, $sy),
            'ellipse' => $this->scaleEllipse($element, $sx, $sy),
            'line' => $this->scaleLine($element, $sx, $sy),
            default => false,
        };
    }

    /**
     * Translates a rect element.
     */
    private function translateRect(ElementInterface $element, float $tx, float $ty): bool
    {
        $x = (float) ($element->getAttribute('x') ?? 0);
        $y = (float) ($element->getAttribute('y') ?? 0);

        $element->setAttribute('x', $x + $tx);
        $element->setAttribute('y', $y + $ty);

        return true;
    }

    /**
     * Translates a circle element.
     */
    private function translateCircle(ElementInterface $element, float $tx, float $ty): bool
    {
        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);

        $element->setAttribute('cx', $cx + $tx);
        $element->setAttribute('cy', $cy + $ty);

        return true;
    }

    /**
     * Translates an ellipse element.
     */
    private function translateEllipse(ElementInterface $element, float $tx, float $ty): bool
    {
        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);

        $element->setAttribute('cx', $cx + $tx);
        $element->setAttribute('cy', $cy + $ty);

        return true;
    }

    /**
     * Translates a line element.
     */
    private function translateLine(ElementInterface $element, float $tx, float $ty): bool
    {
        $x1 = (float) ($element->getAttribute('x1') ?? 0);
        $y1 = (float) ($element->getAttribute('y1') ?? 0);
        $x2 = (float) ($element->getAttribute('x2') ?? 0);
        $y2 = (float) ($element->getAttribute('y2') ?? 0);

        $element->setAttribute('x1', $x1 + $tx);
        $element->setAttribute('y1', $y1 + $ty);
        $element->setAttribute('x2', $x2 + $tx);
        $element->setAttribute('y2', $y2 + $ty);

        return true;
    }

    /**
     * Scales a rect element.
     */
    private function scaleRect(ElementInterface $element, float $sx, float $sy): bool
    {
        $x = (float) ($element->getAttribute('x') ?? 0);
        $y = (float) ($element->getAttribute('y') ?? 0);
        $width = (float) ($element->getAttribute('width') ?? 0);
        $height = (float) ($element->getAttribute('height') ?? 0);

        $element->setAttribute('x', $x * $sx);
        $element->setAttribute('y', $y * $sy);
        $element->setAttribute('width', $width * $sx);
        $element->setAttribute('height', $height * $sy);

        return true;
    }

    /**
     * Scales a circle element.
     */
    private function scaleCircle(ElementInterface $element, float $sx, float $sy): bool
    {
        // Can only scale if sx === sy (otherwise it becomes an ellipse)
        if (abs($sx - $sy) > 0.0001) {
            return false;
        }

        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);
        $r = (float) ($element->getAttribute('r') ?? 0);

        $element->setAttribute('cx', $cx * $sx);
        $element->setAttribute('cy', $cy * $sy);
        $element->setAttribute('r', $r * $sx);

        return true;
    }

    /**
     * Scales an ellipse element.
     */
    private function scaleEllipse(ElementInterface $element, float $sx, float $sy): bool
    {
        $cx = (float) ($element->getAttribute('cx') ?? 0);
        $cy = (float) ($element->getAttribute('cy') ?? 0);
        $rx = (float) ($element->getAttribute('rx') ?? 0);
        $ry = (float) ($element->getAttribute('ry') ?? 0);

        $element->setAttribute('cx', $cx * $sx);
        $element->setAttribute('cy', $cy * $sy);
        $element->setAttribute('rx', $rx * $sx);
        $element->setAttribute('ry', $ry * $sy);

        return true;
    }

    /**
     * Scales a line element.
     */
    private function scaleLine(ElementInterface $element, float $sx, float $sy): bool
    {
        $x1 = (float) ($element->getAttribute('x1') ?? 0);
        $y1 = (float) ($element->getAttribute('y1') ?? 0);
        $x2 = (float) ($element->getAttribute('x2') ?? 0);
        $y2 = (float) ($element->getAttribute('y2') ?? 0);

        $element->setAttribute('x1', $x1 * $sx);
        $element->setAttribute('y1', $y1 * $sy);
        $element->setAttribute('x2', $x2 * $sx);
        $element->setAttribute('y2', $y2 * $sy);

        return true;
    }

    /**
     * Applies a transform to a path element.
     *
     * @param ElementInterface                          $element   The path element
     * @param array{type: string, values: array<float>} $transform The parsed transform
     *
     * @return bool True if the transform was successfully applied
     */
    private function applyTransformToPath(ElementInterface $element, array $transform): bool
    {
        $d = $element->getAttribute('d');
        if (null === $d || '' === $d) {
            return false;
        }

        if ('translate' === $transform['type']) {
            $newD = $this->translatePath($d, $transform['values'][0], $transform['values'][1]);
            if (null !== $newD) {
                $element->setAttribute('d', $newD);

                return true;
            }
        }

        // Scale on paths is more complex and not implemented
        return false;
    }

    /**
     * Translates all coordinates in a path.
     *
     * @param string $d  The path data
     * @param float  $tx Translation X
     * @param float  $ty Translation Y
     *
     * @return string|null The translated path or null if failed
     */
    private function translatePath(string $d, float $tx, float $ty): ?string
    {
        // Simple path translation: add tx/ty to all absolute coordinates
        // This is a simplified implementation that handles common cases

        // Match path commands with their coordinates
        $result = preg_replace_callback(
            '/([MLHVCSQTAZ])([^MLHVCSQTAZ]*)/i',
            function ($matches) use ($tx, $ty) {
                $command = $matches[1];
                $coordsWithSpace = $matches[2];
                $coords = trim($coordsWithSpace);

                // Detect trailing whitespace to preserve it
                $trailingSpace = '';
                if ('' !== $coordsWithSpace && $coordsWithSpace !== $coords) {
                    // Check if there's trailing whitespace
                    if (preg_match('/\s+$/', $coordsWithSpace, $spaceMatch)) {
                        $trailingSpace = $spaceMatch[0];
                    }
                }

                // Skip if no coordinates
                if ('' === $coords) {
                    return $command.$trailingSpace;
                }

                // For relative commands (lowercase), don't translate
                if (ctype_lower($command)) {
                    return $matches[0];
                }

                // Parse coordinates
                $numbers = preg_split('/[\s,]+/', $coords, -1, PREG_SPLIT_NO_EMPTY);
                assert(false !== $numbers);

                // Translate based on command type
                $translatedNumbers = [];
                switch (strtoupper($command)) {
                    case 'M':
                    case 'L':
                    case 'T':
                        // x y pairs
                        for ($i = 0; $i < count($numbers); $i += 2) {
                            if (isset($numbers[$i + 1])) {
                                $translatedNumbers[] = (float) $numbers[$i] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 1] + $ty;
                            }
                        }
                        break;

                    case 'H':
                        // Horizontal line (x only)
                        foreach ($numbers as $num) {
                            $translatedNumbers[] = (float) $num + $tx;
                        }
                        break;

                    case 'V':
                        // Vertical line (y only)
                        foreach ($numbers as $num) {
                            $translatedNumbers[] = (float) $num + $ty;
                        }
                        break;

                    case 'C':
                        // Cubic bezier: x1 y1 x2 y2 x y
                        for ($i = 0; $i < count($numbers); $i += 6) {
                            if (isset($numbers[$i + 5])) {
                                $translatedNumbers[] = (float) $numbers[$i] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 1] + $ty;
                                $translatedNumbers[] = (float) $numbers[$i + 2] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 3] + $ty;
                                $translatedNumbers[] = (float) $numbers[$i + 4] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 5] + $ty;
                            }
                        }
                        break;

                    case 'S':
                    case 'Q':
                        // Smooth cubic bezier or quadratic: x1 y1 x y
                        for ($i = 0; $i < count($numbers); $i += 4) {
                            if (isset($numbers[$i + 3])) {
                                $translatedNumbers[] = (float) $numbers[$i] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 1] + $ty;
                                $translatedNumbers[] = (float) $numbers[$i + 2] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 3] + $ty;
                            }
                        }
                        break;

                    case 'A':
                        // Arc: rx ry rotation large-arc sweep x y
                        for ($i = 0; $i < count($numbers); $i += 7) {
                            if (isset($numbers[$i + 6])) {
                                // rx, ry, rotation, flags stay the same
                                $translatedNumbers[] = $numbers[$i];
                                $translatedNumbers[] = $numbers[$i + 1];
                                $translatedNumbers[] = $numbers[$i + 2];
                                $translatedNumbers[] = $numbers[$i + 3];
                                $translatedNumbers[] = $numbers[$i + 4];
                                // Translate endpoint
                                $translatedNumbers[] = (float) $numbers[$i + 5] + $tx;
                                $translatedNumbers[] = (float) $numbers[$i + 6] + $ty;
                            }
                        }
                        break;

                    case 'Z':
                        // Close path - no coordinates
                        return $command;
                }

                if (empty($translatedNumbers)) {
                    return $command.$trailingSpace;
                }

                return $command.' '.implode(' ', $translatedNumbers).$trailingSpace;
            },
            $d
        );

        return $result;
    }
}
