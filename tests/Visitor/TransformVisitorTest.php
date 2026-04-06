<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Visitor\TransformVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformVisitor::class)]
final class TransformVisitorTest extends TestCase
{
    public function testSetAndGetTransformMatrix(): void
    {
        $visitor = new TransformVisitor();
        $matrix = [
            'a' => 1.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 1.0,
            'e' => 10.0,
            'f' => 20.0,
        ];

        $result = $visitor->setTransformMatrix($matrix);

        $this->assertSame($visitor, $result);
        $this->assertSame($matrix, $visitor->getTransformMatrix());
    }

    public function testGetIdentityMatrix(): void
    {
        $visitor = new TransformVisitor();
        $identity = $visitor->getIdentityMatrix();

        $this->assertSame(1.0, $identity['a']);
        $this->assertSame(0.0, $identity['b']);
        $this->assertSame(0.0, $identity['c']);
        $this->assertSame(1.0, $identity['d']);
        $this->assertSame(0.0, $identity['e']);
        $this->assertSame(0.0, $identity['f']);
    }

    public function testApplyTransformToElementWithoutExistingTransform(): void
    {
        $visitor = new TransformVisitor();
        $element = new PathElement();
        $matrix = [
            'a' => 2.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 2.0,
            'e' => 10.0,
            'f' => 20.0,
        ];

        $visitor->applyTransformToElement($element, $matrix);

        $this->assertSame('matrix(2 0 0 2 10 20)', $element->getAttribute('transform'));
    }

    public function testApplyTransformToElementWithExistingTransform(): void
    {
        $visitor = new TransformVisitor();
        $element = new PathElement();
        $element->setAttribute('transform', 'matrix(1 0 0 1 5 5)');

        $matrix = [
            'a' => 2.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 2.0,
            'e' => 0.0,
            'f' => 0.0,
        ];

        $visitor->applyTransformToElement($element, $matrix);

        // The existing transform should be merged with the new one
        $this->assertNotNull($element->getAttribute('transform'));
        $this->assertStringContainsString('matrix', $element->getAttribute('transform'));
    }

    public function testMatrixToString(): void
    {
        $visitor = new TransformVisitor();
        $matrix = [
            'a' => 1.5,
            'b' => 0.5,
            'c' => -0.5,
            'd' => 1.5,
            'e' => 100.0,
            'f' => 200.0,
        ];

        $result = $visitor->matrixToString($matrix);

        $this->assertSame('matrix(1.5 0.5 -0.5 1.5 100 200)', $result);
    }

    public function testParseTransformToMatrixWithMatrixString(): void
    {
        $visitor = new TransformVisitor();
        $transform = 'matrix(2 0 0 2 10 20)';

        $result = $visitor->parseTransformToMatrix($transform);

        $this->assertSame(2.0, $result['a']);
        $this->assertSame(0.0, $result['b']);
        $this->assertSame(0.0, $result['c']);
        $this->assertSame(2.0, $result['d']);
        $this->assertSame(10.0, $result['e']);
        $this->assertSame(20.0, $result['f']);
    }

    public function testParseTransformToMatrixWithCommas(): void
    {
        $visitor = new TransformVisitor();
        $transform = 'matrix(1.5, 0.5, -0.5, 1.5, 100, 200)';

        $result = $visitor->parseTransformToMatrix($transform);

        $this->assertSame(1.5, $result['a']);
        $this->assertSame(0.5, $result['b']);
        $this->assertSame(-0.5, $result['c']);
        $this->assertSame(1.5, $result['d']);
        $this->assertSame(100.0, $result['e']);
        $this->assertSame(200.0, $result['f']);
    }

    public function testParseTransformToMatrixWithInvalidStringReturnsIdentity(): void
    {
        $visitor = new TransformVisitor();
        $transform = 'translate(10 20)';

        $result = $visitor->parseTransformToMatrix($transform);

        // Should return identity matrix for unsupported transforms
        $identity = $visitor->getIdentityMatrix();
        $this->assertSame($identity, $result);
    }

    public function testMergeMatrices(): void
    {
        $visitor = new TransformVisitor();
        $matrix1 = [
            'a' => 2.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 2.0,
            'e' => 10.0,
            'f' => 20.0,
        ];
        $matrix2 = [
            'a' => 1.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 1.0,
            'e' => 5.0,
            'f' => 5.0,
        ];

        $result = $visitor->mergeMatrices($matrix1, $matrix2);

        $this->assertSame(2.0, $result['a']);
        $this->assertSame(0.0, $result['b']);
        $this->assertSame(0.0, $result['c']);
        $this->assertSame(2.0, $result['d']);
        $this->assertSame(20.0, $result['e']); // 2*5 + 0*5 + 10
        $this->assertSame(30.0, $result['f']); // 0*5 + 2*5 + 20
    }

    public function testMergeIdentityMatrices(): void
    {
        $visitor = new TransformVisitor();
        $identity = $visitor->getIdentityMatrix();

        $result = $visitor->mergeMatrices($identity, $identity);

        $this->assertSame($identity, $result);
    }

    public function testVisitAppliesTransform(): void
    {
        $visitor = new TransformVisitor();
        $element = new PathElement();
        $matrix = [
            'a' => 3.0,
            'b' => 0.0,
            'c' => 0.0,
            'd' => 3.0,
            'e' => 15.0,
            'f' => 25.0,
        ];

        $visitor->setTransformMatrix($matrix);
        $visitor->visit($element);

        $this->assertSame('matrix(3 0 0 3 15 25)', $element->getAttribute('transform'));
    }

    public function testVisitWithoutTransformMatrixDoesNothing(): void
    {
        $visitor = new TransformVisitor();
        $element = new PathElement();

        $visitor->visit($element);

        $this->assertNull($element->getAttribute('transform'));
    }
}
