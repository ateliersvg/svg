<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\Util\NumberFormatter;
use Atelier\Svg\Optimizer\Util\NumericAttributes;

/**
 * Cleans up numeric values in SVG attributes.
 *
 * This pass:
 * - Rounds numbers to specified precision
 * - Removes trailing zeros after decimal point
 * - Removes leading zeros
 * - Converts 0.5 to .5 (when beneficial)
 * - Removes unnecessary decimals from integers
 *
 * Example:
 * Before: x="10.00000" y="0.50000" width="100.0"
 * After:  x="10" y=".5" width="100"
 *
 * @see NumericAttributes For the canonical list of numeric SVG attributes
 * @see NumberFormatter For the shared formatting logic
 */
final class CleanupNumericValuesPass extends AbstractOptimizerPass
{
    public function __construct(
        private readonly int $precision = 3,
        private readonly bool $removeLeadingZero = true,
    ) {
    }

    public function getName(): string
    {
        return 'cleanup-numeric-values';
    }

    protected function processElement(ElementInterface $element): void
    {
        // Clean up simple numeric attributes
        foreach (NumericAttributes::ALL as $attr) {
            if ($element->hasAttribute($attr)) {
                $value = $element->getAttribute($attr);
                if (null !== $value && is_numeric($value)) {
                    $cleaned = NumberFormatter::format((float) $value, $this->precision, $this->removeLeadingZero);
                    if ($cleaned !== $value) {
                        $element->setAttribute($attr, $cleaned);
                    }
                }
            }
        }

        // Clean up compound attributes (space/comma-separated numbers)
        $this->cleanupCompoundAttribute($element, 'viewBox');
        $this->cleanupCompoundAttribute($element, 'points');

        // Clean up path data
        if ($element->hasAttribute('d')) {
            $d = $element->getAttribute('d');
            if (null !== $d) {
                $cleaned = NumberFormatter::roundInAttribute($d, $this->precision, $this->removeLeadingZero);
                if ($cleaned !== $d) {
                    $element->setAttribute('d', $cleaned);
                }
            }
        }
    }

    /**
     * Cleans up a compound attribute whose value is a list of space/comma-separated numbers.
     */
    private function cleanupCompoundAttribute(ElementInterface $element, string $attribute): void
    {
        if (!$element->hasAttribute($attribute)) {
            return;
        }

        $value = $element->getAttribute($attribute);
        assert(null !== $value);

        $parts = preg_split('/[\s,]+/', trim($value));
        assert(false !== $parts);

        $cleaned = [];
        foreach ($parts as $part) {
            if ('' !== $part && is_numeric($part)) {
                $cleaned[] = NumberFormatter::format((float) $part, $this->precision, $this->removeLeadingZero);
            } else {
                $cleaned[] = $part;
            }
        }

        $result = implode(' ', $cleaned);
        if ($result !== $value) {
            $element->setAttribute($attribute, $result);
        }
    }
}
