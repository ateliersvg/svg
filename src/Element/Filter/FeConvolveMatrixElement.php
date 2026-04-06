<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

/**
 * Represents the <feConvolveMatrix> SVG filter primitive.
 *
 * Applies a matrix convolution filter effect. Used for blur, sharpen, emboss,
 * edge detection, and other image processing effects.
 *
 * Example:
 * <feConvolveMatrix order="3" kernelMatrix="0 -1 0 -1 5 -1 0 -1 0"/>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feConvolveMatrixElement
 */
final class FeConvolveMatrixElement extends AbstractFilterPrimitiveElement
{
    public function __construct()
    {
        parent::__construct('feConvolveMatrix');
    }

    /**
     * Set the convolution matrix order.
     *
     * Indicates the number of cells in each dimension for the kernel matrix.
     * Format: <number-optional-number> (e.g., "3" or "3 5")
     */
    public function setOrder(string|int $order): static
    {
        $this->setAttribute('order', (string) $order);

        return $this;
    }

    /**
     * Get the convolution matrix order.
     */
    public function getOrder(): ?string
    {
        return $this->getAttribute('order');
    }

    /**
     * Set the kernel matrix values.
     *
     * A list of numbers that make up the kernel matrix for the convolution.
     * Values are separated by whitespace and/or a comma.
     */
    public function setKernelMatrix(string $kernelMatrix): static
    {
        $this->setAttribute('kernelMatrix', $kernelMatrix);

        return $this;
    }

    /**
     * Get the kernel matrix values.
     */
    public function getKernelMatrix(): ?string
    {
        return $this->getAttribute('kernelMatrix');
    }

    /**
     * Set the divisor for the kernel matrix.
     *
     * After applying the kernel matrix, the resulting value is divided by this value.
     * Default is the sum of all values in kernelMatrix, or 1 if that sum is 0.
     */
    public function setDivisor(string|int|float $divisor): static
    {
        $this->setAttribute('divisor', (string) $divisor);

        return $this;
    }

    /**
     * Get the divisor.
     */
    public function getDivisor(): ?string
    {
        return $this->getAttribute('divisor');
    }

    /**
     * Set the bias for the convolution.
     *
     * After applying divisor, this value is added to the result.
     */
    public function setBias(string|int|float $bias): static
    {
        $this->setAttribute('bias', (string) $bias);

        return $this;
    }

    /**
     * Get the bias.
     */
    public function getBias(): ?string
    {
        return $this->getAttribute('bias');
    }

    /**
     * Set the target for the convolution.
     *
     * Determines the positioning of the kernel matrix relative to a given pixel.
     */
    public function setTargetX(string|int $targetX): static
    {
        $this->setAttribute('targetX', (string) $targetX);

        return $this;
    }

    /**
     * Get the targetX.
     */
    public function getTargetX(): ?string
    {
        return $this->getAttribute('targetX');
    }

    /**
     * Set the target Y position.
     */
    public function setTargetY(string|int $targetY): static
    {
        $this->setAttribute('targetY', (string) $targetY);

        return $this;
    }

    /**
     * Get the targetY.
     */
    public function getTargetY(): ?string
    {
        return $this->getAttribute('targetY');
    }

    /**
     * Set the edge mode.
     *
     * - duplicate: Duplicates edge pixels
     * - wrap: Wraps the image
     * - none: Uses transparent black
     */
    public function setEdgeMode(string $edgeMode): static
    {
        $this->setAttribute('edgeMode', $edgeMode);

        return $this;
    }

    /**
     * Get the edge mode.
     */
    public function getEdgeMode(): ?string
    {
        return $this->getAttribute('edgeMode');
    }

    /**
     * Set whether to preserve alpha.
     */
    public function setPreserveAlpha(bool $preserveAlpha): static
    {
        $this->setAttribute('preserveAlpha', $preserveAlpha ? 'true' : 'false');

        return $this;
    }

    /**
     * Get whether alpha is preserved.
     */
    public function getPreserveAlpha(): ?string
    {
        return $this->getAttribute('preserveAlpha');
    }
}
