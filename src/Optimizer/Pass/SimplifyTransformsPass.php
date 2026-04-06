<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\Util\NumberFormatter;

/**
 * Optimization pass that simplifies transform attributes.
 *
 * This pass:
 * - Removes identity transforms (translate(0,0), scale(1,1), rotate(0))
 * - Simplifies transform values (removes trailing zeros, unnecessary decimals)
 * - Removes transform attribute when it has no effect
 *
 * Benefits:
 * - Reduces file size by removing redundant transforms
 * - Cleaner, more readable SVG markup
 * - Potentially faster rendering (fewer transforms to apply)
 */
final class SimplifyTransformsPass extends AbstractOptimizerPass
{
    /**
     * Creates a new SimplifyTransformsPass.
     *
     * @param int  $precision      Decimal precision for transform values (default: 3)
     * @param bool $removeDefaults Whether to remove identity transforms (default: true)
     */
    public function __construct(
        private readonly int $precision = 3,
        private readonly bool $removeDefaults = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'simplify-transforms';
    }

    /**
     * Processes an element to simplify transforms.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // Process this element's transform
        if ($element->hasAttribute('transform')) {
            $transform = $element->getAttribute('transform');
            if (null !== $transform) {
                if ('' === trim($transform)) {
                    // Empty transform, remove it
                    $element->removeAttribute('transform');
                } else {
                    $simplified = $this->simplifyTransform($transform);

                    if (null === $simplified || '' === $simplified) {
                        // Transform simplifies to nothing, remove it
                        $element->removeAttribute('transform');
                    } elseif ($simplified !== $transform) {
                        // Transform was simplified, update it
                        $element->setAttribute('transform', $simplified);
                    }
                }
            }
        }
    }

    /**
     * Simplifies a transform string.
     *
     * @param string $transform The transform string
     *
     * @return string|null Simplified transform, or null if it's an identity transform
     */
    private function simplifyTransform(string $transform): ?string
    {
        $transform = trim($transform);

        // Check for identity transforms
        if ($this->removeDefaults && $this->isIdentityTransform($transform)) {
            return null;
        }

        // Simplify numbers in the transform
        $simplified = $this->simplifyTransformNumbers($transform);

        return $simplified;
    }

    /**
     * Checks if a transform is an identity transform (has no effect).
     *
     * @param string $transform The transform string
     *
     * @return bool True if it's an identity transform
     */
    private function isIdentityTransform(string $transform): bool
    {
        // Check for common identity transforms
        $identityPatterns = [
            '/^translate\s*\(\s*0\s*,?\s*0?\s*\)$/i',           // translate(0) or translate(0,0)
            '/^translate\s*\(\s*0\s*\)$/i',                      // translate(0)
            '/^scale\s*\(\s*1\s*,?\s*1?\s*\)$/i',               // scale(1) or scale(1,1)
            '/^scale\s*\(\s*1\s*\)$/i',                          // scale(1)
            '/^rotate\s*\(\s*0\s*(?:,\s*[^)]+)?\s*\)$/i',       // rotate(0) or rotate(0, cx, cy)
            '/^skewX\s*\(\s*0\s*\)$/i',                          // skewX(0)
            '/^skewY\s*\(\s*0\s*\)$/i',                          // skewY(0)
            '/^matrix\s*\(\s*1\s*,\s*0\s*,\s*0\s*,\s*1\s*,\s*0\s*,\s*0\s*\)$/i', // matrix(1,0,0,1,0,0)
        ];

        foreach ($identityPatterns as $pattern) {
            if (preg_match($pattern, $transform)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Simplifies numbers in a transform string.
     *
     * Uses a comprehensive pattern that also handles + prefixes and scientific notation,
     * which can appear in SVG transforms (e.g. from matrix decomposition).
     */
    private function simplifyTransformNumbers(string $transform): string
    {
        return preg_replace_callback(
            '/[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?/',
            fn ($matches) => NumberFormatter::format((float) $matches[0], $this->precision),
            $transform
        ) ?? $transform;
    }
}
