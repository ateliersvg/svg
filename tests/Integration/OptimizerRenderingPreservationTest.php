<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;
use Atelier\Svg\Svg;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Optimizer::class)]
final class OptimizerRenderingPreservationTest extends TestCase
{
    #[DataProvider('presets')]
    public function testPreservesPaintOrder(string $preset): void
    {
        $root = $this->optimizeFixture('paint-order', $preset);
        $children = $root->getChildren();

        $this->assertCount(2, $children);
        $this->assertContains($children[0]->getAttribute('fill'), ['red', '#f00']);
        $this->assertContains($children[1]->getAttribute('fill'), ['blue', '#00f']);
    }

    #[DataProvider('presets')]
    public function testPreservesTenUnitStroke(string $preset): void
    {
        $root = $this->optimizeFixture('stroke-width', $preset);

        $this->assertSame('10', $root->getChildren()[0]->getAttribute('stroke-width'));
    }

    #[DataProvider('presetsPreservingFractionalOpacity')]
    public function testPreservesPerShapeOpacity(string $preset): void
    {
        $root = $this->optimizeFixture('opacity', $preset);
        $children = $root->getChildren();

        $this->assertFalse($root->hasAttribute('opacity'));
        $this->assertCount(2, $children);
        foreach ($children as $child) {
            $this->assertSame(0.5, (float) $child->getAttribute('opacity'));
        }
    }

    #[DataProvider('presets')]
    public function testPreservesStrokeScaling(string $preset): void
    {
        $root = $this->optimizeFixture('scaled-stroke', $preset);
        $child = $root->getChildren()[0];

        $this->assertSame('scale(2)', $child->getAttribute('transform'));
        $this->assertSame('2', $child->getAttribute('stroke-width'));
    }

    /** @return iterable<string, array{string}> */
    public static function presets(): iterable
    {
        foreach (['safe', 'default', 'web', 'aggressive'] as $preset) {
            yield $preset => [$preset];
        }
    }

    /** @return iterable<string, array{string}> */
    public static function presetsPreservingFractionalOpacity(): iterable
    {
        // Aggressive intentionally rounds numeric attributes to integers.
        foreach (['safe', 'default', 'web'] as $preset) {
            yield $preset => [$preset];
        }
    }

    private function optimizeFixture(string $name, string $preset): SvgElement
    {
        $input = file_get_contents(__DIR__.'/fixtures/input/optimizer-'.$name.'.svg');
        self::assertNotFalse($input);
        $svg = Svg::fromString($input)->optimizeWith(OptimizerPresets::get($preset));
        $root = $svg->getDocument()->getRootElement();
        self::assertNotNull($root);

        return $root;
    }
}
