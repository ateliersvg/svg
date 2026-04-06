<?php

namespace Atelier\Svg\Tests\Element\Gradient;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\GradientBuilder;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GradientBuilder::class)]
final class GradientBuilderTest extends TestCase
{
    private ?Document $doc = null;

    protected function setUp(): void
    {
        $this->doc = Document::create();
    }

    private function getDoc(): Document
    {
        $doc = $this->doc;
        $this->assertNotNull($doc);

        return $doc;
    }

    public function testCreateLinearGradient(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad1');
        $gradient = $helper->getGradient();

        $this->assertEquals('grad1', $gradient->getId());
        $this->assertEquals('linearGradient', $gradient->getTagName());
    }

    public function testCreateRadialGradient(): void
    {
        $helper = GradientBuilder::createRadial($this->getDoc(), 'grad2');
        $gradient = $helper->getGradient();

        $this->assertEquals('grad2', $gradient->getId());
        $this->assertEquals('radialGradient', $gradient->getTagName());
    }

    public function testLinearGradientFromTo(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad')
            ->from(0, 0)
            ->to(100, 100);

        $gradient = $helper->getGradient();
        $this->assertEquals('0', $gradient->getAttribute('x1'));
        $this->assertEquals('0', $gradient->getAttribute('y1'));
        $this->assertEquals('100', $gradient->getAttribute('x2'));
        $this->assertEquals('100', $gradient->getAttribute('y2'));
    }

    public function testRadialGradientCenterRadius(): void
    {
        $helper = GradientBuilder::createRadial($this->getDoc(), 'grad')
            ->center(50, 50)
            ->radius(30);

        $gradient = $helper->getGradient();
        $this->assertEquals('50', $gradient->getAttribute('cx'));
        $this->assertEquals('50', $gradient->getAttribute('cy'));
        $this->assertEquals('30', $gradient->getAttribute('r'));
    }

    public function testAddStop(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad')
            ->addStop(0, '#ff0000')
            ->addStop(100, '#0000ff', 0.5);

        $gradient = $helper->getGradient();
        $stops = iterator_to_array($gradient->getChildren());

        $this->assertCount(2, $stops);
        $this->assertEquals('0', $stops[0]->getAttribute('offset'));
        $this->assertEquals('#ff0000', $stops[0]->getAttribute('stop-color'));
        $this->assertEquals('0.5', $stops[1]->getAttribute('stop-opacity'));
    }

    public function testHorizontalGradient(): void
    {
        $gradient = GradientBuilder::horizontal($this->getDoc(), 'grad', '#000', '#fff');

        $this->assertEquals('grad', $gradient->getId());
        $this->assertEquals('0', $gradient->getAttribute('x1'));
        $this->assertEquals('100', $gradient->getAttribute('x2'));
        $this->assertCount(2, iterator_to_array($gradient->getChildren()));
    }

    public function testVerticalGradient(): void
    {
        $gradient = GradientBuilder::vertical($this->getDoc(), 'grad', '#000', '#fff');

        $this->assertEquals('0', $gradient->getAttribute('y1'));
        $this->assertEquals('100', $gradient->getAttribute('y2'));
    }

    public function testDiagonalGradient(): void
    {
        $gradient = GradientBuilder::diagonal($this->getDoc(), 'grad', '#000', '#fff');

        $this->assertEquals('0', $gradient->getAttribute('x1'));
        $this->assertEquals('100', $gradient->getAttribute('x2'));
        $this->assertEquals('0', $gradient->getAttribute('y1'));
        $this->assertEquals('100', $gradient->getAttribute('y2'));
    }

    public function testRadialGradient(): void
    {
        $gradient = GradientBuilder::radial($this->getDoc(), 'grad', '#000', '#fff');

        $this->assertEquals('50', $gradient->getAttribute('cx'));
        $this->assertEquals('50', $gradient->getAttribute('cy'));
        $this->assertEquals('50', $gradient->getAttribute('r'));
    }

    public function testFocal(): void
    {
        $helper = GradientBuilder::createRadial($this->getDoc(), 'grad')
            ->center(50, 50)
            ->radius(50)
            ->focal(30, 30);

        $gradient = $helper->getGradient();
        $this->assertEquals('30', $gradient->getAttribute('fx'));
        $this->assertEquals('30', $gradient->getAttribute('fy'));
    }

    public function testSpreadMethod(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad')
            ->spreadMethod('reflect');

        $gradient = $helper->getGradient();
        $this->assertEquals('reflect', $gradient->getAttribute('spreadMethod'));
    }

    public function testTransform(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad')
            ->transform('rotate(45)');

        $gradient = $helper->getGradient();
        $this->assertEquals('rotate(45)', $gradient->getAttribute('gradientTransform'));
    }

    public function testFromThrowsOnRadialGradient(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        GradientBuilder::createRadial($this->getDoc(), 'grad')->from(0, 0);
    }

    public function testToThrowsOnRadialGradient(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        GradientBuilder::createRadial($this->getDoc(), 'grad')->to(100, 100);
    }

    public function testCenterThrowsOnLinearGradient(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        GradientBuilder::createLinear($this->getDoc(), 'grad')->center(50, 50);
    }

    public function testRadiusThrowsOnLinearGradient(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        GradientBuilder::createLinear($this->getDoc(), 'grad')->radius(50);
    }

    public function testFocalThrowsOnLinearGradient(): void
    {
        $this->expectException(\Atelier\Svg\Exception\LogicException::class);
        GradientBuilder::createLinear($this->getDoc(), 'grad')->focal(30, 30);
    }

    public function testAddToDefs(): void
    {
        $helper = GradientBuilder::createLinear($this->getDoc(), 'grad')
            ->addStop(0, '#000')
            ->addToDefs();

        $root = $this->getDoc()->getRootElement();
        $this->assertNotNull($root);

        $defs = iterator_to_array($root->getChildren())[0];
        $this->assertEquals('defs', $defs->getTagName());
    }

    public function testSetFillPaintServer(): void
    {
        GradientBuilder::horizontal($this->getDoc(), 'grad', '#000', '#fff');

        $rect = new RectElement();
        $rect->setFillPaintServer('grad');

        $this->assertEquals('url(#grad)', $rect->getAttribute('fill'));
    }
}
