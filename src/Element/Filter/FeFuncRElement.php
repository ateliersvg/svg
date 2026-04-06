<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractElement;

/**
 * Represents the <feFuncR> SVG filter primitive component.
 *
 * Defines the transfer function for the red component of the input graphic.
 * Must be a child of feComponentTransfer.
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feFuncRElement
 */
final class FeFuncRElement extends AbstractElement
{
    public function __construct()
    {
        parent::__construct('feFuncR');
    }

    /**
     * Set the transfer function type.
     *
     * - identity: C' = C
     * - table: Piecewise linear interpolation
     * - discrete: Stepwise function
     * - linear: C' = slope * C + intercept
     * - gamma: C' = amplitude * pow(C, exponent) + offset
     */
    public function setType(string $type): static
    {
        $this->setAttribute('type', $type);

        return $this;
    }

    /**
     * Get the transfer function type.
     */
    public function getType(): ?string
    {
        return $this->getAttribute('type');
    }

    /**
     * Set the table values (for table/discrete types).
     */
    public function setTableValues(string $tableValues): static
    {
        $this->setAttribute('tableValues', $tableValues);

        return $this;
    }

    /**
     * Get the table values.
     */
    public function getTableValues(): ?string
    {
        return $this->getAttribute('tableValues');
    }

    /**
     * Set the slope (for linear type).
     */
    public function setSlope(string|int|float $slope): static
    {
        $this->setAttribute('slope', (string) $slope);

        return $this;
    }

    /**
     * Get the slope.
     */
    public function getSlope(): ?string
    {
        return $this->getAttribute('slope');
    }

    /**
     * Set the intercept (for linear type).
     */
    public function setIntercept(string|int|float $intercept): static
    {
        $this->setAttribute('intercept', (string) $intercept);

        return $this;
    }

    /**
     * Get the intercept.
     */
    public function getIntercept(): ?string
    {
        return $this->getAttribute('intercept');
    }

    /**
     * Set the amplitude (for gamma type).
     */
    public function setAmplitude(string|int|float $amplitude): static
    {
        $this->setAttribute('amplitude', (string) $amplitude);

        return $this;
    }

    /**
     * Get the amplitude.
     */
    public function getAmplitude(): ?string
    {
        return $this->getAttribute('amplitude');
    }

    /**
     * Set the exponent (for gamma type).
     */
    public function setExponent(string|int|float $exponent): static
    {
        $this->setAttribute('exponent', (string) $exponent);

        return $this;
    }

    /**
     * Get the exponent.
     */
    public function getExponent(): ?string
    {
        return $this->getAttribute('exponent');
    }

    /**
     * Set the offset (for gamma type).
     */
    public function setOffset(string|int|float $offset): static
    {
        $this->setAttribute('offset', (string) $offset);

        return $this;
    }

    /**
     * Get the offset.
     */
    public function getOffset(): ?string
    {
        return $this->getAttribute('offset');
    }
}
