<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\GradientBuilder;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\LogicException;
use Atelier\Svg\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GradientBuilder::class)]
final class GradientBuilderTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(400, 300);
    }

    public function testCreateLinear(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'grad1');

        $this->assertInstanceOf(GradientBuilder::class, $builder);
        $gradient = $builder->getGradient();
        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertSame('grad1', $gradient->getId());
    }

    public function testCreateRadial(): void
    {
        $builder = GradientBuilder::createRadial($this->document, 'grad2');

        $gradient = $builder->getGradient();
        $this->assertInstanceOf(RadialGradientElement::class, $gradient);
        $this->assertSame('grad2', $gradient->getId());
    }

    public function testFrom(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->from(0, 0);

        $gradient = $builder->getGradient();
        $this->assertSame('0', $gradient->getAttribute('x1'));
        $this->assertSame('0', $gradient->getAttribute('y1'));
    }

    public function testFromThrowsForRadialGradient(): void
    {
        $this->expectException(LogicException::class);
        GradientBuilder::createRadial($this->document, 'g')->from(0, 0);
    }

    public function testTo(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->to(100, 100);

        $gradient = $builder->getGradient();
        $this->assertSame('100', $gradient->getAttribute('x2'));
        $this->assertSame('100', $gradient->getAttribute('y2'));
    }

    public function testToThrowsForRadialGradient(): void
    {
        $this->expectException(LogicException::class);
        GradientBuilder::createRadial($this->document, 'g')->to(100, 100);
    }

    public function testCenter(): void
    {
        $builder = GradientBuilder::createRadial($this->document, 'g')
            ->center(50, 50);

        $gradient = $builder->getGradient();
        $this->assertSame('50', $gradient->getAttribute('cx'));
        $this->assertSame('50', $gradient->getAttribute('cy'));
    }

    public function testCenterThrowsForLinearGradient(): void
    {
        $this->expectException(LogicException::class);
        GradientBuilder::createLinear($this->document, 'g')->center(50, 50);
    }

    public function testRadius(): void
    {
        $builder = GradientBuilder::createRadial($this->document, 'g')
            ->radius(50);

        $gradient = $builder->getGradient();
        $this->assertSame('50', $gradient->getAttribute('r'));
    }

    public function testRadiusThrowsForLinearGradient(): void
    {
        $this->expectException(LogicException::class);
        GradientBuilder::createLinear($this->document, 'g')->radius(50);
    }

    public function testFocal(): void
    {
        $builder = GradientBuilder::createRadial($this->document, 'g')
            ->focal(30, 30);

        $gradient = $builder->getGradient();
        $this->assertSame('30', $gradient->getAttribute('fx'));
        $this->assertSame('30', $gradient->getAttribute('fy'));
    }

    public function testFocalThrowsForLinearGradient(): void
    {
        $this->expectException(LogicException::class);
        GradientBuilder::createLinear($this->document, 'g')->focal(30, 30);
    }

    public function testAddStop(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->addStop(0, '#000')
            ->addStop(100, '#fff');

        $children = $builder->getGradient()->getChildren();
        $this->assertCount(2, $children);
    }

    public function testAddStopWithOpacity(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->addStop(50, '#f00', 0.5);

        $stop = $builder->getGradient()->getChildren()[0];
        $this->assertSame('0.5', $stop->getAttribute('stop-opacity'));
    }

    public function testSpreadMethod(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->spreadMethod('reflect');

        $this->assertSame('reflect', $builder->getGradient()->getAttribute('spreadMethod'));
    }

    public function testUnits(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->units('userSpaceOnUse');

        $this->assertSame('userSpaceOnUse', $builder->getGradient()->getAttribute('gradientUnits'));
    }

    public function testTransform(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'g')
            ->transform('rotate(45)');

        $this->assertSame('rotate(45)', $builder->getGradient()->getAttribute('gradientTransform'));
    }

    public function testAddToDefs(): void
    {
        GradientBuilder::createLinear($this->document, 'g')
            ->addStop(0, '#000')
            ->addToDefs();

        $root = $this->document->getRootElement();
        $defs = $root->getChildren()[0];
        $this->assertInstanceOf(DefsElement::class, $defs);
    }

    public function testAddToDefsReusesExistingDefs(): void
    {
        GradientBuilder::createLinear($this->document, 'g1')->addToDefs();
        GradientBuilder::createLinear($this->document, 'g2')->addToDefs();

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }

    public function testHorizontal(): void
    {
        $gradient = GradientBuilder::horizontal($this->document, 'h', '#000', '#fff');

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertSame('h', $gradient->getId());
    }

    public function testVertical(): void
    {
        $gradient = GradientBuilder::vertical($this->document, 'v', '#000', '#fff');

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertSame('v', $gradient->getId());
    }

    public function testDiagonal(): void
    {
        $gradient = GradientBuilder::diagonal($this->document, 'd', '#000', '#fff');

        $this->assertInstanceOf(LinearGradientElement::class, $gradient);
        $this->assertSame('d', $gradient->getId());
    }

    public function testRadialPreset(): void
    {
        $gradient = GradientBuilder::radial($this->document, 'r', '#fff', '#000');

        $this->assertInstanceOf(RadialGradientElement::class, $gradient);
        $this->assertSame('r', $gradient->getId());
    }

    public function testConstructorWithExistingGradient(): void
    {
        $gradient = new LinearGradientElement();
        $gradient->setId('existing');

        $builder = new GradientBuilder($this->document, $gradient);

        $this->assertSame($gradient, $builder->getGradient());
    }

    public function testAddToDefsReusesCachedDefs(): void
    {
        $builder = GradientBuilder::createLinear($this->document, 'cached-test');

        $builder->addToDefs();
        $builder->addToDefs();

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }

    public function testAddToDefsThrowsWhenDocumentHasNoRootElement(): void
    {
        $document = new Document();
        $builder = GradientBuilder::createLinear($document, 'grad1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $builder->addToDefs();
    }
}
