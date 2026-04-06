<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Geometry\TransformBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformBuilder::class)]
final class TransformBuilderTest extends TestCase
{
    public function testTranslate(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->apply();

        $this->assertEquals('translate(10,20)', $element->getAttribute('transform'));
    }

    public function testScale(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->scale(2)->apply();

        $this->assertEquals('scale(2)', $element->getAttribute('transform'));
    }

    public function testRotate(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(45)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45)', $transform);
    }

    public function testRotateWithCenter(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(45, 50, 50)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45,50,50)', $transform);
    }

    public function testChainedTransforms(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(45)->scale(1.5)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('rotate', $transform);
        $this->assertStringContainsString('scale', $transform);
    }

    public function testGetTranslation(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->apply();

        $translation = $helper->getTranslation();
        $this->assertEquals(10, $translation[0]);
        $this->assertEquals(20, $translation[1]);
    }

    public function testGetRotation(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(45)->apply();

        $rotation = $helper->getRotation();
        $this->assertEquals(45, round($rotation));
    }

    public function testSetTranslation(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->apply();
        $helper->setTranslation(30, 40)->apply();

        $translation = $helper->getTranslation();
        $this->assertEquals(30, $translation[0]);
        $this->assertEquals(40, $translation[1]);
    }

    public function testClear(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(45)->clear()->apply();

        $this->assertNull($element->getAttribute('transform'));
    }

    public function testMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->matrix(1, 0, 0, 1, 10, 20)->apply();

        $this->assertEquals('matrix(1,0,0,1,10,20)', $element->getAttribute('transform'));
    }

    public function testParseExistingTransform(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20) rotate(45)');

        $helper = new TransformBuilder($element);
        $transforms = $helper->getTransforms();

        $this->assertCount(2, $transforms);
    }

    public function testToMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);
        $matrix = $helper->toMatrix();

        $this->assertEquals(10, $matrix->e);
        $this->assertEquals(20, $matrix->f);
    }

    public function testSkewX(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewX(30)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('skewX(30)', $transform);
    }

    public function testSkewY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewY(45)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('skewY(45)', $transform);
    }

    public function testSkewXMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewX(45);
        $matrix = $helper->toMatrix();

        $this->assertEqualsWithDelta(1, $matrix->a, 0.0001);
        $this->assertEqualsWithDelta(0, $matrix->b, 0.0001);
        $this->assertEqualsWithDelta(tan(deg2rad(45)), $matrix->c, 0.0001);
        $this->assertEqualsWithDelta(1, $matrix->d, 0.0001);
    }

    public function testSkewYMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewY(45);
        $matrix = $helper->toMatrix();

        $this->assertEqualsWithDelta(1, $matrix->a, 0.0001);
        $this->assertEqualsWithDelta(tan(deg2rad(45)), $matrix->b, 0.0001);
        $this->assertEqualsWithDelta(0, $matrix->c, 0.0001);
        $this->assertEqualsWithDelta(1, $matrix->d, 0.0001);
    }

    public function testFlipHorizontalWithoutAxis(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->flipHorizontal()->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('scale(-1,1)', $transform);
    }

    public function testFlipHorizontalWithAxis(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->flipHorizontal(50.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('scale(-1,1)', $transform);
    }

    public function testFlipVerticalWithoutAxis(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->flipVertical()->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('scale(1,-1)', $transform);
    }

    public function testFlipVerticalWithAxis(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->flipVertical(100.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('scale(1,-1)', $transform);
    }

    public function testRotateAroundWithCoordinates(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotateAround(90, 50.0, 50.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(90,50,50)', $transform);
    }

    public function testRotateAroundWithoutCoordinates(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotateAround(45)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45)', $transform);
    }

    public function testRotateAroundWithAnchorButNoCoordinates(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotateAround(30, null, null, 'center')->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(30)', $transform);
    }

    public function testDecompose(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->scale(2)->rotate(45);
        $decomposition = $helper->decompose();

        $this->assertArrayHasKey('translateX', $decomposition);
        $this->assertArrayHasKey('translateY', $decomposition);
        $this->assertArrayHasKey('scaleX', $decomposition);
        $this->assertArrayHasKey('scaleY', $decomposition);
        $this->assertArrayHasKey('rotation', $decomposition);
        $this->assertArrayHasKey('skewX', $decomposition);
    }

    public function testDecomposeTranslateOnly(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(15, 25);
        $decomposition = $helper->decompose();

        $this->assertEqualsWithDelta(15, $decomposition['translateX'], 0.0001);
        $this->assertEqualsWithDelta(25, $decomposition['translateY'], 0.0001);
        $this->assertEqualsWithDelta(1, $decomposition['scaleX'], 0.0001);
        $this->assertEqualsWithDelta(1, $decomposition['scaleY'], 0.0001);
        $this->assertEqualsWithDelta(0, $decomposition['rotation'], 0.0001);
    }

    public function testGetScale(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->scale(3, 4);
        $scale = $helper->getScale();

        $this->assertEqualsWithDelta(3, $scale[0], 0.0001);
        $this->assertEqualsWithDelta(4, $scale[1], 0.0001);
    }

    public function testGetScaleUniform(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->scale(2.5);
        $scale = $helper->getScale();

        $this->assertEqualsWithDelta(2.5, $scale[0], 0.0001);
        $this->assertEqualsWithDelta(2.5, $scale[1], 0.0001);
    }

    public function testGetRotationValue(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(90);
        $rotation = $helper->getRotation();

        $this->assertEqualsWithDelta(90, $rotation, 0.0001);
    }

    public function testGetTranslationValues(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(100, 200);
        $translation = $helper->getTranslation();

        $this->assertEqualsWithDelta(100, $translation[0], 0.0001);
        $this->assertEqualsWithDelta(200, $translation[1], 0.0001);
    }

    public function testSetTranslationWithoutExisting(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->scale(2);
        $helper->setTranslation(50, 60)->apply();

        $translation = $helper->getTranslation();
        $this->assertEqualsWithDelta(50, $translation[0], 0.0001);
        $this->assertEqualsWithDelta(60, $translation[1], 0.0001);
    }

    public function testSetRotationReplacesExisting(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(30)->apply();
        $helper->setRotation(60)->apply();

        $rotation = $helper->getRotation();
        $this->assertEqualsWithDelta(60, $rotation, 0.0001);
    }

    public function testSetRotationWithCenter(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->setRotation(45, 100.0, 100.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45,100,100)', $transform);
    }

    public function testSetRotationReplacesExistingWithCenter(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->rotate(30)->apply();
        $helper->setRotation(90, 50.0, 50.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(90,50,50)', $transform);
    }

    public function testSetRotationWithoutExisting(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);
        $helper->setRotation(45)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45)', $transform);
    }

    public function testSetRotationWithoutExistingWithCenter(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);
        $helper->setRotation(45, 25.0, 25.0)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45,25,25)', $transform);
    }

    public function testSetScaleReplacesExisting(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->scale(2)->apply();
        $helper->setScale(3)->apply();

        $scale = $helper->getScale();
        $this->assertEqualsWithDelta(3, $scale[0], 0.0001);
        $this->assertEqualsWithDelta(3, $scale[1], 0.0001);
    }

    public function testSetScaleNonUniform(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->setScale(2, 3)->apply();

        $scale = $helper->getScale();
        $this->assertEqualsWithDelta(2, $scale[0], 0.0001);
        $this->assertEqualsWithDelta(3, $scale[1], 0.0001);
    }

    public function testSetScaleWithoutExisting(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);
        $helper->setScale(5)->apply();

        $scale = $helper->getScale();
        $this->assertEqualsWithDelta(5, $scale[0], 0.0001);
        $this->assertEqualsWithDelta(5, $scale[1], 0.0001);
    }

    public function testGetMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);
        $matrix = $helper->getMatrix();

        $this->assertEqualsWithDelta(10, $matrix->e, 0.0001);
        $this->assertEqualsWithDelta(20, $matrix->f, 0.0001);
    }

    public function testGetMatrixMatchesToMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(5, 10)->scale(2)->rotate(30);

        $matrixA = $helper->toMatrix();
        $matrixB = $helper->getMatrix();

        $this->assertEqualsWithDelta($matrixA->a, $matrixB->a, 0.0001);
        $this->assertEqualsWithDelta($matrixA->b, $matrixB->b, 0.0001);
        $this->assertEqualsWithDelta($matrixA->c, $matrixB->c, 0.0001);
        $this->assertEqualsWithDelta($matrixA->d, $matrixB->d, 0.0001);
        $this->assertEqualsWithDelta($matrixA->e, $matrixB->e, 0.0001);
        $this->assertEqualsWithDelta($matrixA->f, $matrixB->f, 0.0001);
    }

    public function testIsIdentityWithNoTransforms(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $this->assertTrue($helper->isIdentity());
    }

    public function testIsIdentityWithIdentityMatrix(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->matrix(1, 0, 0, 1, 0, 0);

        $this->assertTrue($helper->isIdentity());
    }

    public function testIsIdentityReturnsFalse(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20);

        $this->assertFalse($helper->isIdentity());
    }

    public function testReset(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(45)->scale(2);
        $helper->reset();

        $this->assertEmpty($helper->getTransforms());
        $this->assertTrue($helper->isIdentity());
    }

    public function testResetAndApply(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->apply();
        $this->assertNotNull($element->getAttribute('transform'));

        $helper->reset()->apply();
        $this->assertNull($element->getAttribute('transform'));
    }

    public function testTranslateX(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translateX(42)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('42', $transform);
    }

    public function testTranslateY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translateY(99)->apply();

        $this->assertEquals('translate(0,99)', $element->getAttribute('transform'));
    }

    public function testGetMatrixThrowsOnUnsupportedTransformType(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $unsupported = new class implements \Atelier\Svg\Value\Transform {
            public function toString(): string
            {
                return 'unsupported()';
            }
        };

        $ref = new \ReflectionProperty($helper, 'transforms');
        $ref->setValue($helper, [$unsupported]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unsupported transform type');

        $helper->getMatrix();
    }
}
