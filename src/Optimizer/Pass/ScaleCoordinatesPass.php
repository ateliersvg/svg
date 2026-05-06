<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\PolylineElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Geometry\Transformation;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\PathTransformer;

/**
 * Scales SVG coordinates by a given factor.
 *
 * Typical usage: scale coordinates up before rounding to integers to preserve
 * relative precision (e.g. scale x10 then round to 0 decimals).
 */
final class ScaleCoordinatesPass extends AbstractOptimizerPass
{
    private readonly float $scaleFactor;
    private readonly PathParser $pathParser;
    private readonly PathTransformer $pathTransformer;

    /**
     * Attributes that represent coordinates or lengths to scale.
     */
    private const array SCALABLE_ATTRIBUTES = [
        'width', 'height',
        'x', 'y', 'x1', 'y1', 'x2', 'y2',
        'cx', 'cy', 'r', 'rx', 'ry',
        'stroke-width',
        'stroke-dashoffset',
    ];

    public function __construct(float $scaleFactor = 10.0)
    {
        if ($scaleFactor <= 0) {
            throw new InvalidArgumentException('Scale factor must be greater than zero.');
        }

        $this->scaleFactor = $scaleFactor;
        $this->pathParser = new PathParser();
        $this->pathTransformer = new PathTransformer();
    }

    public function getName(): string
    {
        return 'scale-coordinates';
    }

    public function getScaleFactor(): float
    {
        return $this->scaleFactor;
    }

    protected function processElement(ElementInterface $element): void
    {
        $this->scaleAttributes($element);
    }

    private function scaleAttributes(ElementInterface $element): void
    {
        if ($element instanceof SvgElement) {
            $this->scaleViewBox($element);
        }

        if ($element instanceof PathElement) {
            $this->scalePathData($element);
        }

        if ($element instanceof PolygonElement || $element instanceof PolylineElement) {
            $this->scalePoints($element);
        }

        foreach (self::SCALABLE_ATTRIBUTES as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $value = $element->getAttribute($attribute);

                if (null !== $value && is_numeric($value)) {
                    $scaled = $this->formatNumber((float) $value * $this->scaleFactor);
                    $element->setAttribute($attribute, $scaled);
                }
            }
        }

        $this->scaleStrokeDasharray($element);
    }

    private function scaleViewBox(SvgElement $svg): void
    {
        $viewBox = $svg->getAttribute('viewBox');

        if (null === $viewBox || '' === $viewBox) {
            return;
        }

        $parts = preg_split('/[\s,]+/', trim($viewBox));

        if (!is_array($parts) || 4 !== count($parts)) {
            return;
        }

        foreach ($parts as $part) {
            if (!is_numeric($part)) {
                return;
            }
        }

        $scaled = array_map(
            fn (string $part) => $this->formatNumber((float) $part * $this->scaleFactor),
            $parts
        );

        $svg->setAttribute('viewBox', implode(' ', $scaled));
    }

    private function scalePathData(PathElement $element): void
    {
        $pathData = $element->getPathData();

        if (null === $pathData || '' === $pathData) {
            return;
        }

        $data = $this->pathParser->parse($pathData);
        $scaledData = $this->pathTransformer->transform($data, Transformation::scale($this->scaleFactor));
        $element->setPathData($scaledData->toString());
    }

    private function scalePoints(ElementInterface $element): void
    {
        $points = $element->getAttribute('points');

        if (null === $points || '' === $points) {
            return;
        }

        $numbers = preg_split('/[\s,]+/', trim($points));

        assert(false !== $numbers);

        $scaled = [];

        foreach ($numbers as $number) {
            if ('' === $number) {
                continue;
            }

            if (is_numeric($number)) {
                $scaled[] = $this->formatNumber((float) $number * $this->scaleFactor);
            }
        }

        if ([] !== $scaled) {
            $element->setAttribute('points', implode(' ', $scaled));
        }
    }

    private function scaleStrokeDasharray(ElementInterface $element): void
    {
        if (!$element->hasAttribute('stroke-dasharray')) {
            return;
        }

        $dasharray = $element->getAttribute('stroke-dasharray');

        if (null === $dasharray || '' === $dasharray || 'none' === $dasharray) {
            return;
        }

        $parts = preg_split('/[,\s]+/', trim($dasharray));

        assert(false !== $parts);

        $scaled = array_map(
            fn (string $part) => is_numeric($part)
                ? $this->formatNumber((float) $part * $this->scaleFactor)
                : $part,
            array_filter($parts, fn (string $part) => '' !== $part)
        );

        if ([] !== $scaled) {
            $element->setAttribute('stroke-dasharray', implode(' ', $scaled));
        }
    }

    private function formatNumber(float $value): string
    {
        $formatted = rtrim(rtrim(sprintf('%.12F', $value), '0'), '.');

        return '-0' === $formatted ? '0' : $formatted;
    }
}
