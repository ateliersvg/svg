<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Builder;
use Atelier\Svg\Element\FluentPathBuilder;
use Atelier\Svg\Element\PathElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FluentPathBuilder::class)]
final class FluentPathBuilderTest extends TestCase
{
    public function testMoveTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $this->assertInstanceOf(FluentPathBuilder::class, $fluentPathBuilder);
        $result = $fluentPathBuilder->moveTo(10, 20);
        $this->assertInstanceOf(FluentPathBuilder::class, $result);
    }

    public function testLineTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->lineTo(50, 50)->end();

        $root = $builder->getDocument()->getRootElement();
        $path = $root->getChildren()[0];
        $this->assertInstanceOf(PathElement::class, $path);
        $pathData = $path->getPathData();
        $this->assertStringContainsString('L', $pathData);
    }

    public function testCurveTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->curveTo(10, 10, 20, 20, 30, 30)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('C', $path->getPathData());
    }

    public function testQuadraticCurveTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->quadraticCurveTo(10, 10, 20, 20)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('Q', $path->getPathData());
    }

    public function testArcTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->arcTo(25, 25, 0, false, true, 50, 50)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('A', $path->getPathData());
    }

    public function testHorizontalLineTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->horizontalLineTo(50)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('H', $path->getPathData());
    }

    public function testVerticalLineTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->verticalLineTo(50)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('V', $path->getPathData());
    }

    public function testSmoothCurveTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->smoothCurveTo(10, 10, 20, 20)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('S', $path->getPathData());
    }

    public function testSmoothQuadraticCurveTo(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->smoothQuadraticCurveTo(20, 20)->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('T', $path->getPathData());
    }

    public function testClosePath(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $fluentPathBuilder->moveTo(0, 0)->lineTo(50, 50)->closePath()->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('Z', $path->getPathData());
    }

    public function testCloseAlias(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $result = $fluentPathBuilder->moveTo(0, 0)->lineTo(50, 50)->close();

        // close() returns Builder, not FluentPathBuilder
        $this->assertInstanceOf(Builder::class, $result);
        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $this->assertStringContainsString('Z', $path->getPathData());
    }

    public function testEndReturnsBuilder(): void
    {
        $builder = new Builder();
        $fluentPathBuilder = $builder->svg(100, 100)->path();

        $result = $fluentPathBuilder->moveTo(0, 0)->end();

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function testComplexPath(): void
    {
        $builder = new Builder();
        $builder->svg(200, 200)
            ->path()
                ->moveTo(10, 10)
                ->lineTo(100, 10)
                ->curveTo(120, 10, 130, 30, 130, 50)
                ->quadraticCurveTo(130, 70, 110, 80)
                ->arcTo(20, 20, 0, false, true, 80, 90)
                ->horizontalLineTo(50)
                ->verticalLineTo(110)
                ->smoothCurveTo(20, 120, 10, 100)
                ->smoothQuadraticCurveTo(10, 50)
                ->closePath()
            ->end();

        $path = $builder->getDocument()->getRootElement()->getChildren()[0];
        $pathData = $path->getPathData();
        $this->assertStringContainsString('M', $pathData);
        $this->assertStringContainsString('L', $pathData);
        $this->assertStringContainsString('C', $pathData);
        $this->assertStringContainsString('Q', $pathData);
        $this->assertStringContainsString('A', $pathData);
        $this->assertStringContainsString('H', $pathData);
        $this->assertStringContainsString('V', $pathData);
        $this->assertStringContainsString('S', $pathData);
        $this->assertStringContainsString('T', $pathData);
        $this->assertStringContainsString('Z', $pathData);
    }
}
