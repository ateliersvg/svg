<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\PrecisionConfig;
use Atelier\Svg\Optimizer\Util\NumberFormatter;
use Atelier\Svg\Optimizer\Util\NumericAttributes;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\PathUtils;

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
        $this->roundPathData($element, $this->pathPrecision);
        $this->roundCompoundAttribute($element, 'points', $this->precision);
        $this->roundCompoundAttribute($element, 'viewBox', $this->precision);
    }

    /**
     * Rounds path data in absolute space.
     *
     * A relative path is a running sum, so rounding each delta on its own lets the error
     * accumulate: twenty steps of `l1.4 0` rounded to whole units land eight units short.
     * Resolving to absolute first bounds the error at half a unit per point, whatever the
     * subpath length. The original command style is restored afterwards.
     */
    private function roundPathData(ElementInterface $element, int $precision): void
    {
        if (!$element->hasAttribute('d')) {
            return;
        }

        $value = $element->getAttribute('d');

        if (null === $value || '' === trim($value)) {
            return;
        }

        $parser = new PathParser();

        try {
            $data = $parser->parse($value);
        } catch (\Throwable) {
            // Unparseable path data is left exactly as found.
            return;
        }

        $wasRelative = false;

        foreach ($data->getSegments() as $segment) {
            if ($segment->isRelative()) {
                $wasRelative = true;
                break;
            }
        }

        $absolute = PathUtils::toAbsolute($data)->toString();
        $rounded = NumberFormatter::roundInAttribute($absolute, $precision);

        try {
            $result = $parser->parse($rounded);
        } catch (\Throwable) {
            return;
        }

        if ($wasRelative) {
            $result = PathUtils::toRelative($result);
        }

        $final = $result->toString();

        if ($final !== $value) {
            $element->setAttribute('d', $final);
        }
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
