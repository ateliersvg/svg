<?php

declare(strict_types=1);

namespace Atelier\Svg\Geometry;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Value\Transform;
use Atelier\Svg\Value\Transform\MatrixTransform;
use Atelier\Svg\Value\Transform\RotateTransform;
use Atelier\Svg\Value\Transform\ScaleTransform;
use Atelier\Svg\Value\Transform\TranslateTransform;
use Atelier\Svg\Value\TransformList;

/**
 * Fluent API for manipulating transforms on SVG elements.
 *
 * This helper class provides a chainable interface for building and
 * modifying transform attributes on SVG elements.
 */
final class TransformBuilder
{
    /** @var array<Transform> */
    private array $transforms = [];

    public function __construct(
        private readonly AbstractElement $element,
    ) {
        // Parse existing transforms
        $transformAttr = $element->getAttribute('transform');
        if (null !== $transformAttr) {
            $list = TransformList::parse($transformAttr);
            $this->transforms = $list->getTransforms();
        }
    }

    /**
     * Adds a translation transform.
     */
    public function translate(float $x, float $y = 0): self
    {
        $this->transforms[] = new TranslateTransform(
            \Atelier\Svg\Value\Length::parse($x),
            \Atelier\Svg\Value\Length::parse($y)
        );

        return $this;
    }

    /**
     * Adds a horizontal translation transform.
     */
    public function translateX(float $x): self
    {
        return $this->translate($x, 0);
    }

    /**
     * Adds a vertical translation transform.
     */
    public function translateY(float $y): self
    {
        return $this->translate(0, $y);
    }

    /**
     * Adds a scale transform.
     */
    public function scale(float $x, ?float $y = null): self
    {
        $this->transforms[] = new ScaleTransform($x, $y ?? $x);

        return $this;
    }

    /**
     * Adds a rotation transform.
     *
     * @param float      $angle Angle in degrees
     * @param float|null $cx    Center X (optional)
     * @param float|null $cy    Center Y (optional)
     */
    public function rotate(float $angle, ?float $cx = null, ?float $cy = null): self
    {
        $angleValue = \Atelier\Svg\Value\Angle::parse($angle);

        if (null !== $cx && null !== $cy) {
            $this->transforms[] = new RotateTransform(
                $angleValue,
                \Atelier\Svg\Value\Length::parse($cx),
                \Atelier\Svg\Value\Length::parse($cy)
            );
        } else {
            $this->transforms[] = new RotateTransform($angleValue);
        }

        return $this;
    }

    /**
     * Rotates around a specific point or anchor.
     *
     * @param float       $angle  Angle in degrees
     * @param float|null  $x      Center X (if using coordinates)
     * @param float|null  $y      Center Y (if using coordinates)
     * @param string|null $anchor Anchor point (e.g., 'center', 'top-left')
     */
    public function rotateAround(float $angle, ?float $x = null, ?float $y = null, ?string $anchor = null): self
    {
        // If anchor is specified, we need to calculate bbox and get the anchor point
        // For now, if x and y are provided, use them directly
        if (null !== $x && null !== $y) {
            return $this->rotate($angle, $x, $y);
        }

        // If anchor is specified but no coordinates, we'll need BoundingBoxCalculator
        // For now, just rotate around origin (will be enhanced when BoundingBoxCalculator is available)
        return $this->rotate($angle);
    }

    /**
     * Flips the element horizontally.
     *
     * @param float|null $axisX The X coordinate of the flip axis (defaults to element center)
     */
    public function flipHorizontal(?float $axisX = null): self
    {
        if (null !== $axisX) {
            // Flip around specific axis: translate(-axisX) → scale(-1, 1) → translate(axisX)
            $this->translate(-$axisX, 0);
            $this->scale(-1, 1);
            $this->translate($axisX, 0);
        } else {
            // Simple flip (will need bbox for proper centering)
            $this->scale(-1, 1);
        }

        return $this;
    }

    /**
     * Flips the element vertically.
     *
     * @param float|null $axisY The Y coordinate of the flip axis (defaults to element center)
     */
    public function flipVertical(?float $axisY = null): self
    {
        if (null !== $axisY) {
            // Flip around specific axis: translate(0, -axisY) → scale(1, -1) → translate(0, axisY)
            $this->translate(0, -$axisY);
            $this->scale(1, -1);
            $this->translate(0, $axisY);
        } else {
            // Simple flip (will need bbox for proper centering)
            $this->scale(1, -1);
        }

        return $this;
    }

    /**
     * Adds a skewX transform.
     *
     * @param float $angle Skew angle in degrees
     */
    public function skewX(float $angle): self
    {
        $this->transforms[] = new Transform\SkewXTransform(
            \Atelier\Svg\Value\Angle::parse($angle)
        );

        return $this;
    }

    /**
     * Adds a skewY transform.
     *
     * @param float $angle Skew angle in degrees
     */
    public function skewY(float $angle): self
    {
        $this->transforms[] = new Transform\SkewYTransform(
            \Atelier\Svg\Value\Angle::parse($angle)
        );

        return $this;
    }

    /**
     * Adds a matrix transform.
     */
    public function matrix(float $a, float $b, float $c, float $d, float $e, float $f): self
    {
        $this->transforms[] = new MatrixTransform($a, $b, $c, $d, $e, $f);

        return $this;
    }

    /**
     * Applies all transforms to the element.
     */
    public function apply(): AbstractElement
    {
        if (empty($this->transforms)) {
            $this->element->removeAttribute('transform');
        } else {
            $list = TransformList::fromArray($this->transforms);
            $this->element->setAttribute('transform', $list->toString());
        }

        return $this->element;
    }

    /**
     * Clears all transforms.
     */
    public function clear(): self
    {
        $this->transforms = [];

        return $this;
    }

    /**
     * Gets the current transform list.
     *
     * @return array<Transform>
     */
    public function getTransforms(): array
    {
        return $this->transforms;
    }

    /**
     * Converts all transforms to a single matrix.
     */
    public function toMatrix(): Matrix
    {
        $matrix = Transformation::identity();

        foreach ($this->transforms as $transform) {
            $matrix = $matrix->multiply($this->transformToMatrix($transform));
        }

        return $matrix;
    }

    /**
     * Gets the current transformation matrix.
     * Alias for toMatrix().
     */
    public function getMatrix(): Matrix
    {
        return $this->toMatrix();
    }

    /**
     * Checks if the current transforms represent an identity transformation.
     */
    public function isIdentity(): bool
    {
        return $this->toMatrix()->isIdentity();
    }

    /**
     * Resets all transforms to identity (empty transform list).
     */
    public function reset(): self
    {
        $this->transforms = [];

        return $this;
    }

    /**
     * Converts a Transform to a Matrix.
     */
    private function transformToMatrix(Transform $transform): Matrix
    {
        if ($transform instanceof MatrixTransform) {
            return new Matrix(
                $transform->getA(),
                $transform->getB(),
                $transform->getC(),
                $transform->getD(),
                $transform->getE(),
                $transform->getF()
            );
        }

        if ($transform instanceof TranslateTransform) {
            return Transformation::translate(
                $transform->getTx()->getValue(),
                $transform->getTy()->getValue()
            );
        }

        if ($transform instanceof ScaleTransform) {
            return Transformation::scale($transform->getSx(), $transform->getSy());
        }

        if ($transform instanceof RotateTransform) {
            $cx = $transform->getCx()?->getValue() ?? 0;
            $cy = $transform->getCy()?->getValue() ?? 0;

            return Transformation::rotate($transform->getAngle()->toDegrees(), $cx, $cy);
        }

        // For skew transforms, we need to implement the matrix conversion
        // SkewX: matrix(1 0 tan(a) 1 0 0)
        // SkewY: matrix(1 tan(a) 0 1 0 0)
        if ($transform instanceof Transform\SkewXTransform) {
            $angle = $transform->getAngle()->toDegrees();
            $tan = tan(deg2rad($angle));

            return new Matrix(1, 0, $tan, 1, 0, 0);
        }

        if (!$transform instanceof Transform\SkewYTransform) {
            throw new \LogicException(sprintf('Unsupported transform type: %s', $transform::class));
        }

        $angle = $transform->getAngle()->toDegrees();
        $tan = tan(deg2rad($angle));

        return new Matrix(1, $tan, 0, 1, 0, 0);
    }

    /**
     * Gets the translation component from the current transforms.
     *
     * @return array{0: float, 1: float} [x, y]
     */
    public function getTranslation(): array
    {
        $matrix = $this->toMatrix();

        return [$matrix->e, $matrix->f];
    }

    /**
     * Gets the scale component from the current transforms.
     *
     * @return array{0: float, 1: float} [sx, sy]
     */
    public function getScale(): array
    {
        $matrix = $this->toMatrix();

        // Extract scale from matrix
        $sx = sqrt($matrix->a * $matrix->a + $matrix->b * $matrix->b);
        $sy = sqrt($matrix->c * $matrix->c + $matrix->d * $matrix->d);

        return [$sx, $sy];
    }

    /**
     * Gets the rotation angle in degrees from the current transforms.
     */
    public function getRotation(): float
    {
        $matrix = $this->toMatrix();

        // Extract rotation from matrix
        $angle = atan2($matrix->b, $matrix->a);

        return rad2deg($angle);
    }

    /**
     * Replaces all transforms with a single translation.
     */
    public function setTranslation(float $x, float $y): self
    {
        // Find and update existing translate, or create new one
        $found = false;
        foreach ($this->transforms as $i => $transform) {
            if ($transform instanceof TranslateTransform) {
                $this->transforms[$i] = new TranslateTransform(
                    \Atelier\Svg\Value\Length::parse($x),
                    \Atelier\Svg\Value\Length::parse($y)
                );
                $found = true;
                break;
            }
        }

        if (!$found) {
            array_unshift($this->transforms, new TranslateTransform(
                \Atelier\Svg\Value\Length::parse($x),
                \Atelier\Svg\Value\Length::parse($y)
            ));
        }

        return $this;
    }

    /**
     * Sets the rotation angle.
     */
    public function setRotation(float $angle, ?float $cx = null, ?float $cy = null): self
    {
        $angleValue = \Atelier\Svg\Value\Angle::parse($angle);

        // Find and update existing rotate, or create new one
        $found = false;
        foreach ($this->transforms as $i => $transform) {
            if ($transform instanceof RotateTransform) {
                if (null !== $cx && null !== $cy) {
                    $this->transforms[$i] = new RotateTransform(
                        $angleValue,
                        \Atelier\Svg\Value\Length::parse($cx),
                        \Atelier\Svg\Value\Length::parse($cy)
                    );
                } else {
                    $this->transforms[$i] = new RotateTransform($angleValue);
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            if (null !== $cx && null !== $cy) {
                $this->transforms[] = new RotateTransform(
                    $angleValue,
                    \Atelier\Svg\Value\Length::parse($cx),
                    \Atelier\Svg\Value\Length::parse($cy)
                );
            } else {
                $this->transforms[] = new RotateTransform($angleValue);
            }
        }

        return $this;
    }

    /**
     * Sets the scale.
     */
    public function setScale(float $x, ?float $y = null): self
    {
        // Find and update existing scale, or create new one
        $found = false;
        foreach ($this->transforms as $i => $transform) {
            if ($transform instanceof ScaleTransform) {
                $this->transforms[$i] = new ScaleTransform($x, $y ?? $x);
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->transforms[] = new ScaleTransform($x, $y ?? $x);
        }

        return $this;
    }

    /**
     * Decomposes the current transforms into their components.
     *
     * @return array{translateX: float, translateY: float, scaleX: float, scaleY: float, rotation: float, skewX: float}
     */
    public function decompose(): array
    {
        return $this->toMatrix()->decompose();
    }
}
