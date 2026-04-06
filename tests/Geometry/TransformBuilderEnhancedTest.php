<?php

namespace Atelier\Svg\Tests\Geometry;

use Atelier\Svg\Document;
use Atelier\Svg\Geometry\TransformBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformBuilder::class)]
final class TransformBuilderEnhancedTest extends TestCase
{
    public function testFlipHorizontal(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->flipHorizontal()->apply();

        $matrix = $rect->transform()->getMatrix();
        $this->assertEquals(-1, $matrix->a);
        $this->assertEquals(1, $matrix->d);
    }

    public function testFlipVertical(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->flipVertical()->apply();

        $matrix = $rect->transform()->getMatrix();
        $this->assertEquals(1, $matrix->a);
        $this->assertEquals(-1, $matrix->d);
    }

    public function testFlipHorizontalWithAxis(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->flipHorizontal(100)->apply();

        $matrix = $rect->transform()->getMatrix();
        $this->assertEquals(-1, $matrix->a);
    }

    public function testRotateAround(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->rotateAround(45, x: 100, y: 100)->apply();

        $transform = $rect->getAttribute('transform');
        $this->assertStringContainsString('rotate', $transform ?? '');
    }

    public function testIsIdentity(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $this->assertTrue($rect->transform()->isIdentity());

        $rect->transform()->rotate(45)->apply();
        $this->assertFalse($rect->transform()->isIdentity());
    }

    public function testReset(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->rotate(45)->scale(2)->apply();
        $this->assertFalse($rect->transform()->isIdentity());

        $rect->transform()->reset()->apply();
        $this->assertTrue($rect->transform()->isIdentity());
    }

    public function testGetMatrix(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->translate(10, 20)->apply();

        $matrix = $rect->transform()->getMatrix();
        $this->assertEquals(10, $matrix->e);
        $this->assertEquals(20, $matrix->f);
    }

    public function testClear(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()->rotate(45)->apply();
        $this->assertNotNull($rect->getAttribute('transform'));

        $rect->transform()->clear()->apply();
        $this->assertNull($rect->getAttribute('transform'));
    }

    public function testChainedTransforms(): void
    {
        $doc = Document::create();
        $rect = $doc->rect(x: 10, y: 10, width: 50, height: 30);

        $rect->transform()
            ->translate(10, 20)
            ->rotate(45)
            ->scale(2)
            ->apply();

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('rotate', $transform);
        $this->assertStringContainsString('scale', $transform);
    }
}
