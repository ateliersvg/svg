<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Path\PathBuilder as SvgPathBuilder;

/**
 * Proxy class that provides fluent path building and returns to the main Builder.
 *
 * This class wraps the PathBuilder and allows seamless integration with the Builder fluent API.
 */
final readonly class FluentPathBuilder
{
    private SvgPathBuilder $pathBuilder;

    public function __construct(
        private Builder $builder,
        private PathElement $pathElement,
    ) {
        $this->pathBuilder = SvgPathBuilder::new();
    }

    /**
     * Move to a point.
     */
    public function moveTo(float $x, float $y, bool $relative = false): self
    {
        $this->pathBuilder->moveTo($x, $y, $relative);

        return $this;
    }

    /**
     * Draw a line to a point.
     */
    public function lineTo(float $x, float $y, bool $relative = false): self
    {
        $this->pathBuilder->lineTo($x, $y, $relative);

        return $this;
    }

    /**
     * Draw a cubic Bezier curve.
     */
    public function curveTo(
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        float $x,
        float $y,
        bool $relative = false,
    ): self {
        $this->pathBuilder->curveTo($x1, $y1, $x2, $y2, $x, $y, $relative);

        return $this;
    }

    /**
     * Draw a quadratic Bezier curve.
     */
    public function quadraticCurveTo(float $x1, float $y1, float $x, float $y, bool $relative = false): self
    {
        $this->pathBuilder->quadraticCurveTo($x1, $y1, $x, $y, $relative);

        return $this;
    }

    /**
     * Draw an arc.
     */
    public function arcTo(
        float $rx,
        float $ry,
        float $xAxisRotation,
        bool $largeArcFlag,
        bool $sweepFlag,
        float $x,
        float $y,
        bool $relative = false,
    ): self {
        $this->pathBuilder->arcTo($rx, $ry, $xAxisRotation, $largeArcFlag, $sweepFlag, $x, $y, $relative);

        return $this;
    }

    /**
     * Draw a horizontal line.
     */
    public function horizontalLineTo(float $x, bool $relative = false): self
    {
        $this->pathBuilder->horizontalLineTo($x, $relative);

        return $this;
    }

    /**
     * Draw a vertical line.
     */
    public function verticalLineTo(float $y, bool $relative = false): self
    {
        $this->pathBuilder->verticalLineTo($y, $relative);

        return $this;
    }

    /**
     * Draw a smooth cubic Bezier curve.
     */
    public function smoothCurveTo(float $x2, float $y2, float $x, float $y, bool $relative = false): self
    {
        $this->pathBuilder->smoothCurveTo($x2, $y2, $x, $y, $relative);

        return $this;
    }

    /**
     * Draw a smooth quadratic Bezier curve.
     */
    public function smoothQuadraticCurveTo(float $x, float $y, bool $relative = false): self
    {
        $this->pathBuilder->smoothQuadraticCurveTo($x, $y, $relative);

        return $this;
    }

    /**
     * Close the path.
     */
    public function closePath(): self
    {
        $this->pathBuilder->closePath();

        return $this;
    }

    /**
     * Finishes path building and returns to the main Builder.
     */
    public function end(): Builder
    {
        // Set the path data on the element
        $this->pathElement->setPathData($this->pathBuilder->getPathData());

        // Return to the main builder
        return $this->builder;
    }

    /**
     * Alias for closePath() followed by end().
     */
    public function close(): Builder
    {
        $this->closePath();

        return $this->end();
    }
}
