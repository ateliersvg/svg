<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\PrecisionConfig;
use Atelier\Svg\Optimizer\Util\NumberFormatter;
use Atelier\Svg\Optimizer\Util\NumericAttributes;

/**
 * Optimization pass that rounds numeric values to a specified precision.
 *
 * This pass reduces file size by rounding numeric attribute values to a
 * specified number of decimal places, removing unnecessary precision that
 * doesn't affect visual appearance.
 *
 * Supports per-context precision: transforms and path data can use a
 * higher precision than coordinates to prevent compounding errors.
 *
 * @see PrecisionConfig For standard precision constants
 * @see NumericAttributes For the canonical list of numeric SVG attributes
 */
final class RoundValuesPass extends AbstractOptimizerPass
{
    private int $precision;
    private readonly int $transformPrecision;
    private readonly int $pathPrecision;

    /**
     * Creates a new RoundValuesPass.
     *
     * @param int      $precision          Decimal places for coordinate/dimension attributes (default: 2)
     * @param int|null $transformPrecision Decimal places for transform values (null = same as $precision)
     * @param int|null $pathPrecision      Decimal places for path data values (null = same as $precision)
     */
    public function __construct(
        int $precision = PrecisionConfig::COORDINATE_DEFAULT,
        ?int $transformPrecision = null,
        ?int $pathPrecision = null,
    ) {
        $this->precision = max(0, $precision);
        $this->transformPrecision = max(0, $transformPrecision ?? $precision);
        $this->pathPrecision = max(0, $pathPrecision ?? $precision);
    }

    public function getName(): string
    {
        return 'round-values';
    }

    /**
     * Gets the coordinate/dimension precision value.
     */
    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * Sets the coordinate/dimension precision value.
     */
    public function setPrecision(int $precision): self
    {
        $this->precision = max(0, $precision);

        return $this;
    }

    protected function processElement(ElementInterface $element): void
    {
        $this->roundNumericAttributes($element);
        $this->roundCompoundAttribute($element, 'transform', $this->transformPrecision);
        $this->roundCompoundAttribute($element, 'd', $this->pathPrecision);
        $this->roundCompoundAttribute($element, 'points', $this->precision);
        $this->roundCompoundAttribute($element, 'viewBox', $this->precision);
    }

    private function roundNumericAttributes(ElementInterface $element): void
    {
        foreach (NumericAttributes::ROUNDABLE as $attributeName) {
            if ($element->hasAttribute($attributeName)) {
                $value = $element->getAttribute($attributeName);

                if (null !== $value && is_numeric($value)) {
                    $element->setAttribute($attributeName, NumberFormatter::format((float) $value, $this->precision));
                }
            }
        }
    }

    /**
     * Rounds all numeric values inside a compound attribute (transform, d, points, viewBox).
     */
    private function roundCompoundAttribute(ElementInterface $element, string $attribute, int $precision): void
    {
        if (!$element->hasAttribute($attribute)) {
            return;
        }

        $value = $element->getAttribute($attribute);

        assert(null !== $value);

        $rounded = NumberFormatter::roundInAttribute($value, $precision);

        if ($rounded !== $value) {
            $element->setAttribute($attribute, $rounded);
        }
    }
}
