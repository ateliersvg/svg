<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Filter;

use Atelier\Svg\Element\AbstractContainerElement;

/**
 * Represents the <feComponentTransfer> SVG filter primitive.
 *
 * Performs component-wise remapping of data. Allows operations like brightness
 * adjustment, contrast adjustment, color balance, or thresholding.
 *
 * Contains feFuncR, feFuncG, feFuncB, and/or feFuncA child elements.
 *
 * Example:
 * <feComponentTransfer>
 *   <feFuncR type="linear" slope="1.5" intercept="0.2"/>
 *   <feFuncG type="linear" slope="1.5" intercept="0.2"/>
 *   <feFuncB type="linear" slope="1.5" intercept="0.2"/>
 * </feComponentTransfer>
 *
 * @see https://www.w3.org/TR/SVG2/filters.html#feComponentTransferElement
 */
final class FeComponentTransferElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('feComponentTransfer');
    }

    /**
     * Set the input for the primitive.
     */
    public function setIn(string $in): static
    {
        $this->setAttribute('in', $in);

        return $this;
    }

    /**
     * Get the input.
     */
    public function getIn(): ?string
    {
        return $this->getAttribute('in');
    }

    /**
     * Set the result identifier.
     */
    public function setResult(string $result): static
    {
        $this->setAttribute('result', $result);

        return $this;
    }

    /**
     * Get the result identifier.
     */
    public function getResult(): ?string
    {
        return $this->getAttribute('result');
    }
}
