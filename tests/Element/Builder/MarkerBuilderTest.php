<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\MarkerBuilder;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\MarkerElement;
use Atelier\Svg\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkerBuilder::class)]
final class MarkerBuilderTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(400, 300);
    }

    public function testCreate(): void
    {
        $builder = MarkerBuilder::create($this->document, 'marker1');

        $this->assertInstanceOf(MarkerBuilder::class, $builder);
        $marker = $builder->getMarker();
        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('marker1', $marker->getId());
    }

    public function testSize(): void
    {
        $builder = MarkerBuilder::create($this->document, 'm')
            ->size(10, 8);

        $marker = $builder->getMarker();
        $this->assertSame('10', $marker->getAttribute('markerWidth'));
        $this->assertSame('8', $marker->getAttribute('markerHeight'));
    }

    public function testRefPoint(): void
    {
        $builder = MarkerBuilder::create($this->document, 'm')
            ->refPoint(5, 5);

        $marker = $builder->getMarker();
        $this->assertSame('5', $marker->getAttribute('refX'));
        $this->assertSame('5', $marker->getAttribute('refY'));
    }

    public function testViewBox(): void
    {
        $builder = MarkerBuilder::create($this->document, 'm')
            ->viewBox('0 0 10 10');

        $marker = $builder->getMarker();
        $this->assertSame('0 0 10 10', $marker->getAttribute('viewBox'));
    }

    public function testAutoOrient(): void
    {
        $builder = MarkerBuilder::create($this->document, 'm')
            ->autoOrient();

        $marker = $builder->getMarker();
        $this->assertSame('auto', $marker->getAttribute('orient'));
    }

    public function testColor(): void
    {
        $builder = MarkerBuilder::create($this->document, 'm')
            ->color('#ff0000');

        $marker = $builder->getMarker();
        $this->assertSame('#ff0000', $marker->getAttribute('fill'));
    }

    public function testAddToDefs(): void
    {
        MarkerBuilder::create($this->document, 'm')
            ->size(10, 10)
            ->addToDefs();

        $root = $this->document->getRootElement();
        $defs = $root->getChildren()[0];
        $this->assertInstanceOf(DefsElement::class, $defs);
    }

    public function testAddToDefsReusesExistingDefs(): void
    {
        MarkerBuilder::create($this->document, 'm1')->addToDefs();
        MarkerBuilder::create($this->document, 'm2')->addToDefs();

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }

    public function testArrow(): void
    {
        $marker = MarkerBuilder::arrow($this->document, 'arrow', '#000', 10);

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('arrow', $marker->getId());
        $this->assertSame('auto', $marker->getAttribute('orient'));
        $this->assertNotEmpty($marker->getChildren());
    }

    public function testCircleMarker(): void
    {
        $marker = MarkerBuilder::circle($this->document, 'circle-m', '#f00', 5);

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('circle-m', $marker->getId());
        $this->assertNotEmpty($marker->getChildren());
    }

    public function testDot(): void
    {
        $marker = MarkerBuilder::dot($this->document, 'dot-m', '#00f', 3);

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('dot-m', $marker->getId());
    }

    public function testSquare(): void
    {
        $marker = MarkerBuilder::square($this->document, 'sq', '#0f0', 8);

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('sq', $marker->getId());
        $this->assertNotEmpty($marker->getChildren());
    }

    public function testDiamond(): void
    {
        $marker = MarkerBuilder::diamond($this->document, 'dia', '#ff0', 10);

        $this->assertInstanceOf(MarkerElement::class, $marker);
        $this->assertSame('dia', $marker->getId());
        $this->assertNotEmpty($marker->getChildren());
    }

    public function testConstructorWithExistingMarker(): void
    {
        $marker = new MarkerElement();
        $marker->setId('existing');

        $builder = new MarkerBuilder($this->document, $marker);

        $this->assertSame($marker, $builder->getMarker());
    }

    public function testFluentChaining(): void
    {
        $builder = MarkerBuilder::create($this->document, 'chain')
            ->size(12, 12)
            ->refPoint(6, 6)
            ->viewBox('0 0 12 12')
            ->autoOrient()
            ->color('#333')
            ->addToDefs();

        $marker = $builder->getMarker();
        $this->assertSame('chain', $marker->getId());
        $this->assertSame('auto', $marker->getAttribute('orient'));
        $this->assertSame('#333', $marker->getAttribute('fill'));
    }

    public function testAddToDefsReusesCachedDefs(): void
    {
        $builder = MarkerBuilder::create($this->document, 'cached-test');

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
        $builder = MarkerBuilder::create($document, 'marker1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $builder->addToDefs();
    }
}
