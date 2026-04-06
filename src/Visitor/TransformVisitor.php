<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;

/**
 * Visitor that applies transformations to SVG elements.
 *
 * This visitor can apply transformation matrices to elements, convert
 * transform attributes to matrix form, and merge multiple transformations.
 */
final class TransformVisitor extends AbstractVisitor
{
    /** @var array{a: float, b: float, c: float, d: float, e: float, f: float}|null */
    private ?array $transformMatrix = null;

    /**
     * Sets the transformation matrix to apply.
     *
     * @param array{a: float, b: float, c: float, d: float, e: float, f: float} $matrix
     *                                                                                  The 2D affine transformation matrix [a, b, c, d, e, f]
     */
    public function setTransformMatrix(array $matrix): self
    {
        $this->transformMatrix = $matrix;

        return $this;
    }

    /**
     * Gets the current transformation matrix.
     *
     * @return array{a: float, b: float, c: float, d: float, e: float, f: float}|null
     */
    public function getTransformMatrix(): ?array
    {
        return $this->transformMatrix;
    }

    /**
     * Performs the visit operation by applying the transformation to the element.
     *
     * @param ElementInterface $element The element to visit
     */
    protected function doVisit(ElementInterface $element): mixed
    {
        if (null !== $this->transformMatrix) {
            $this->applyTransformToElement($element, $this->transformMatrix);
        }

        return null;
    }

    /**
     * Applies a transformation matrix to an element.
     *
     * This method updates the element's transform attribute to include
     * the given transformation matrix.
     *
     * @param ElementInterface                                                  $element The element to transform
     * @param array{a: float, b: float, c: float, d: float, e: float, f: float} $matrix
     *                                                                                   The transformation matrix to apply
     */
    public function applyTransformToElement(ElementInterface $element, array $matrix): void
    {
        $existingTransform = $element->getAttribute('transform');

        if (null !== $existingTransform) {
            // Parse existing transform and merge with new matrix
            $existingMatrix = $this->parseTransformToMatrix($existingTransform);
            $mergedMatrix = $this->mergeMatrices($existingMatrix, $matrix);
            $element->setAttribute('transform', $this->matrixToString($mergedMatrix));
        } else {
            // No existing transform, just apply the new matrix
            $element->setAttribute('transform', $this->matrixToString($matrix));
        }
    }

    /**
     * Converts a transform attribute string to a matrix.
     *
     * This method parses SVG transform strings and converts them to
     * a 2D affine transformation matrix.
     *
     * @param string $transform The transform attribute value
     *
     * @return array{a: float, b: float, c: float, d: float, e: float, f: float}
     */
    public function parseTransformToMatrix(string $transform): array
    {
        // Check if it's already a matrix
        if (preg_match('/matrix\s*\(\s*([-\d.]+)\s*,?\s*([-\d.]+)\s*,?\s*([-\d.]+)\s*,?\s*([-\d.]+)\s*,?\s*([-\d.]+)\s*,?\s*([-\d.]+)\s*\)/', $transform, $matches)) {
            return [
                'a' => (float) $matches[1],
                'b' => (float) $matches[2],
                'c' => (float) $matches[3],
                'd' => (float) $matches[4],
                'e' => (float) $matches[5],
                'f' => (float) $matches[6],
            ];
        }

        // For simplicity, return identity matrix if we can't parse
        // A full implementation would handle translate, scale, rotate, etc.
        return $this->getIdentityMatrix();
    }

    /**
     * Merges two transformation matrices.
     *
     * This method multiplies two matrices to combine their transformations.
     *
     * @param array{a: float, b: float, c: float, d: float, e: float, f: float} $matrix1
     * @param array{a: float, b: float, c: float, d: float, e: float, f: float} $matrix2
     *
     * @return array{a: float, b: float, c: float, d: float, e: float, f: float}
     */
    public function mergeMatrices(array $matrix1, array $matrix2): array
    {
        // Matrix multiplication for 2D affine transformations
        // [a1 c1 e1]   [a2 c2 e2]   [a1*a2+c1*b2  a1*c2+c1*d2  a1*e2+c1*f2+e1]
        // [b1 d1 f1] × [b2 d2 f2] = [b1*a2+d1*b2  b1*c2+d1*d2  b1*e2+d1*f2+f1]
        // [0  0  1 ]   [0  0  1 ]   [0            0            1              ]

        return [
            'a' => $matrix1['a'] * $matrix2['a'] + $matrix1['c'] * $matrix2['b'],
            'b' => $matrix1['b'] * $matrix2['a'] + $matrix1['d'] * $matrix2['b'],
            'c' => $matrix1['a'] * $matrix2['c'] + $matrix1['c'] * $matrix2['d'],
            'd' => $matrix1['b'] * $matrix2['c'] + $matrix1['d'] * $matrix2['d'],
            'e' => $matrix1['a'] * $matrix2['e'] + $matrix1['c'] * $matrix2['f'] + $matrix1['e'],
            'f' => $matrix1['b'] * $matrix2['e'] + $matrix1['d'] * $matrix2['f'] + $matrix1['f'],
        ];
    }

    /**
     * Converts a transformation matrix to an SVG matrix() string.
     *
     * @param array{a: float, b: float, c: float, d: float, e: float, f: float} $matrix
     *
     * @return string The SVG transform attribute value
     */
    public function matrixToString(array $matrix): string
    {
        return sprintf(
            'matrix(%s %s %s %s %s %s)',
            $matrix['a'],
            $matrix['b'],
            $matrix['c'],
            $matrix['d'],
            $matrix['e'],
            $matrix['f']
        );
    }

    /**
     * Returns the identity matrix.
     *
     * @return array{a: float, b: float, c: float, d: float, e: float, f: float}
     */
    public function getIdentityMatrix(): array
    {
        return [
            'a' => 1.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 1.0,
            'e' => 0.0,
            'f' => 0.0,
        ];
    }
}
