<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Geometry\TransformBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the new Transform API features.
 */
#[CoversClass(TransformBuilder::class)]
final class TransformBuilderApiTest extends TestCase
{
    public function testTranslateX(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translateX(50)->apply();

        $this->assertStringContainsString('translate(50', $element->getAttribute('transform'));
    }

    public function testTranslateY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translateY(30)->apply();

        $this->assertEquals('translate(0,30)', $element->getAttribute('transform'));
    }

    public function testTranslateXAndY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translateX(50)->translateY(30)->apply();

        $transform = $element->getAttribute('transform');
        // Both translate transforms should be present
        $this->assertMatchesRegularExpression('/translate\([^)]*50/', $transform);
        $this->assertMatchesRegularExpression('/translate\([^)]*30/', $transform);
    }

    public function testSkewX(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewX(15)->apply();

        $this->assertStringContainsString('skewX(15)', $element->getAttribute('transform'));
    }

    public function testSkewY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewY(20)->apply();

        $this->assertStringContainsString('skewY(20)', $element->getAttribute('transform'));
    }

    public function testSkewXAndY(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->skewX(15)->skewY(20)->apply();

        $transform = $element->getAttribute('transform');
        $this->assertStringContainsString('skewX(15)', $transform);
        $this->assertStringContainsString('skewY(20)', $transform);
    }

    public function testDecompose(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(30)->scale(1.5, 1.2)->apply();

        $decomposed = $helper->decompose();

        $this->assertIsArray($decomposed);
        $this->assertArrayHasKey('translateX', $decomposed);
        $this->assertArrayHasKey('translateY', $decomposed);
        $this->assertArrayHasKey('scaleX', $decomposed);
        $this->assertArrayHasKey('scaleY', $decomposed);
        $this->assertArrayHasKey('rotation', $decomposed);
        $this->assertArrayHasKey('skewX', $decomposed);
    }

    public function testDecomposeValues(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        // Simple translation
        $helper->translate(100, 50)->apply();
        $decomposed = $helper->decompose();

        $this->assertEquals(100, $decomposed['translateX']);
        $this->assertEquals(50, $decomposed['translateY']);
        $this->assertEquals(1, round($decomposed['scaleX'], 2));
        $this->assertEquals(1, round($decomposed['scaleY'], 2));
        $this->assertEquals(0, round($decomposed['rotation'], 2));
    }

    public function testComplexTransformChain(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper
            ->translateX(10)
            ->translateY(20)
            ->rotate(45)
            ->scale(2)
            ->skewX(5)
            ->apply();

        $transform = $element->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('rotate', $transform);
        $this->assertStringContainsString('scale', $transform);
        $this->assertStringContainsString('skewX', $transform);
    }

    public function testGetTransforms(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(45)->scale(1.5)->apply();

        $transforms = $helper->getTransforms();
        $this->assertCount(3, $transforms);
    }

    public function testResetClearsTransforms(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->rotate(45)->apply();
        $this->assertNotNull($element->getAttribute('transform'));

        $helper->reset()->apply();
        $this->assertNull($element->getAttribute('transform'));
    }

    public function testGetMatrixAlias(): void
    {
        $element = new RectElement();
        $helper = new TransformBuilder($element);

        $helper->translate(10, 20)->apply();

        $matrix1 = $helper->toMatrix();
        $matrix2 = $helper->getMatrix();

        $this->assertEquals($matrix1->e, $matrix2->e);
        $this->assertEquals($matrix1->f, $matrix2->f);
    }
}
