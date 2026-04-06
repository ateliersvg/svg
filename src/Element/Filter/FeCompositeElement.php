<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feComposite> SVG filter primitive.
 *
 * This filter primitive performs combination of two input images pixel-wise
 * in image space using Porter-Duff compositing operations.
 *
 * Common uses:
 * - Masking operations
 * - Advanced alpha channel manipulation
 * - Creating knockout effects
 * - Arithmetic compositing
 *
 * Example:
 * <feComposite in="SourceGraphic" in2="blur" operator="over"/>
 * <feComposite in="SourceGraphic" in2="mask" operator="in"/>
 * <feComposite in="img1" in2="img2" operator="arithmetic" k1="0" k2="1" k3="1" k4="0"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feCompositeElement
 */
final class FeCompositeElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feComposite');
    }

    /**
     * Set the second input graphic.
     */
    public function setIn2(string $in2): static
    {
        $this->setAttribute('in2', $in2);

        return $this;
    }

    /**
     * Get the second input graphic.
     */
    public function getIn2(): ?string
    {
        return $this->getAttribute('in2');
    }

    /**
     * Set the compositing operator.
     *
     * Porter-Duff operators:
     * - over: Default, source over destination
     * - in: Source in destination (mask operation)
     * - out: Source out (inverse mask)
     * - atop: Source atop destination
     * - xor: Exclusive OR
     * - lighter: Add colors (deprecated, use arithmetic instead)
     * - arithmetic: Use k1,k2,k3,k4 values for custom formula
     *
     * Arithmetic formula: result = k1*i1*i2 + k2*i1 + k3*i2 + k4
     */
    public function setOperator(string $operator): static
    {
        $this->setAttribute('operator', $operator);

        return $this;
    }

    /**
     * Get the compositing operator.
     */
    public function getOperator(): ?string
    {
        return $this->getAttribute('operator');
    }

    /**
     * Set the k1 value for arithmetic operator.
     *
     * Used in formula: result = k1*i1*i2 + k2*i1 + k3*i2 + k4
     */
    public function setK1(string|int|float $k1): static
    {
        $this->setAttribute('k1', (string) $k1);

        return $this;
    }

    /**
     * Get the k1 value.
     */
    public function getK1(): ?string
    {
        return $this->getAttribute('k1');
    }

    /**
     * Set the k2 value for arithmetic operator.
     */
    public function setK2(string|int|float $k2): static
    {
        $this->setAttribute('k2', (string) $k2);

        return $this;
    }

    /**
     * Get the k2 value.
     */
    public function getK2(): ?string
    {
        return $this->getAttribute('k2');
    }

    /**
     * Set the k3 value for arithmetic operator.
     */
    public function setK3(string|int|float $k3): static
    {
        $this->setAttribute('k3', (string) $k3);

        return $this;
    }

    /**
     * Get the k3 value.
     */
    public function getK3(): ?string
    {
        return $this->getAttribute('k3');
    }

    /**
     * Set the k4 value for arithmetic operator.
     */
    public function setK4(string|int|float $k4): static
    {
        $this->setAttribute('k4', (string) $k4);

        return $this;
    }

    /**
     * Get the k4 value.
     */
    public function getK4(): ?string
    {
        return $this->getAttribute('k4');
    }
}
