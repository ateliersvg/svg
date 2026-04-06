<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\PatternBuilder;
use Atelier\Svg\Element\Gradient\PatternElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatternBuilder::class)]
final class PatternBuilderTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(400, 300);
    }

    public function testCreateReturnsBuilder(): void
    {
        $builder = PatternBuilder::create($this->document, 'test-pattern');

        $this->assertInstanceOf(PatternBuilder::class, $builder);
    }

    public function testGetPatternReturnsPatternElement(): void
    {
        $builder = PatternBuilder::create($this->document, 'test-pattern');
        $pattern = $builder->getPattern();

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('test-pattern', $pattern->getId());
    }

    public function testSize(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->size(20, 30);

        $pattern = $builder->getPattern();
        $this->assertSame('20', $pattern->getAttribute('width'));
        $this->assertSame('30', $pattern->getAttribute('height'));
    }

    public function testPosition(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->position(5, 10);

        $pattern = $builder->getPattern();
        $this->assertSame('5', $pattern->getAttribute('x'));
        $this->assertSame('10', $pattern->getAttribute('y'));
    }

    public function testBounds(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->bounds(1, 2, 30, 40);

        $pattern = $builder->getPattern();
        $this->assertSame('1', $pattern->getAttribute('x'));
        $this->assertSame('2', $pattern->getAttribute('y'));
        $this->assertSame('30', $pattern->getAttribute('width'));
        $this->assertSame('40', $pattern->getAttribute('height'));
    }

    public function testUnits(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->units('userSpaceOnUse');

        $pattern = $builder->getPattern();
        $this->assertSame('userSpaceOnUse', $pattern->getAttribute('patternUnits'));
    }

    public function testContentUnits(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->contentUnits('objectBoundingBox');

        $pattern = $builder->getPattern();
        $this->assertSame('objectBoundingBox', $pattern->getAttribute('patternContentUnits'));
    }

    public function testTransform(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->transform('rotate(45)');

        $pattern = $builder->getPattern();
        $this->assertSame('rotate(45)', $pattern->getAttribute('patternTransform'));
    }

    public function testViewBox(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->viewBox(0, 0, 100, 100);

        $pattern = $builder->getPattern();
        $this->assertSame('0 0 100 100', $pattern->getAttribute('viewBox'));
    }

    public function testAddElement(): void
    {
        $rect = new RectElement();
        $builder = PatternBuilder::create($this->document, 'p')
            ->addElement($rect);

        $children = $builder->getPattern()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testAddRect(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addRect(0, 0, 10, 10, '#f00');

        $children = $builder->getPattern()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertSame('#f00', $children[0]->getAttribute('fill'));
    }

    public function testAddRectWithStroke(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addRect(0, 0, 10, 10, '#f00', '#000');

        $rect = $builder->getPattern()->getChildren()[0];
        $this->assertSame('#000', $rect->getAttribute('stroke'));
    }

    public function testAddRectWithoutFillOrStroke(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addRect(0, 0, 10, 10);

        $rect = $builder->getPattern()->getChildren()[0];
        $this->assertNull($rect->getAttribute('fill'));
        $this->assertNull($rect->getAttribute('stroke'));
    }

    public function testAddCircle(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addCircle(5, 5, 3, '#00f');

        $children = $builder->getPattern()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);
        $this->assertSame('#00f', $children[0]->getAttribute('fill'));
    }

    public function testAddCircleWithStroke(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addCircle(5, 5, 3, '#00f', '#000');

        $circle = $builder->getPattern()->getChildren()[0];
        $this->assertSame('#000', $circle->getAttribute('stroke'));
    }

    public function testAddCircleWithoutFillOrStroke(): void
    {
        $builder = PatternBuilder::create($this->document, 'p')
            ->addCircle(5, 5, 3);

        $circle = $builder->getPattern()->getChildren()[0];
        $this->assertNull($circle->getAttribute('fill'));
        $this->assertNull($circle->getAttribute('stroke'));
    }

    public function testAddToDefs(): void
    {
        PatternBuilder::create($this->document, 'p')
            ->size(10, 10)
            ->addToDefs();

        $root = $this->document->getRootElement();
        $defs = $root->getChildren()[0];
        $this->assertInstanceOf(DefsElement::class, $defs);
        $this->assertCount(1, $defs->getChildren());
    }

    public function testAddToDefsReusesExistingDefs(): void
    {
        // Add first pattern
        PatternBuilder::create($this->document, 'p1')
            ->size(10, 10)
            ->addToDefs();

        // Add second pattern - should reuse same defs
        PatternBuilder::create($this->document, 'p2')
            ->size(20, 20)
            ->addToDefs();

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }

    public function testCreateDots(): void
    {
        $pattern = PatternBuilder::createDots($this->document, 'dots', 10, 2, '#f00');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('dots', $pattern->getId());
    }

    public function testCreateDotsWithBackground(): void
    {
        $pattern = PatternBuilder::createDots($this->document, 'dots-bg', 10, 2, '#f00', '#fff');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        // Background rect + circle dot
        $children = $pattern->getChildren();
        $this->assertCount(2, $children);
    }

    public function testCreateStripes(): void
    {
        $pattern = PatternBuilder::createStripes($this->document, 'stripes', 10, 5, '#000');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('stripes', $pattern->getId());
    }

    public function testCreateStripesWithBackground(): void
    {
        $pattern = PatternBuilder::createStripes($this->document, 'stripes-bg', 10, 5, '#000', '#fff');

        $children = $pattern->getChildren();
        $this->assertCount(2, $children);
    }

    public function testCreateDiagonalStripes(): void
    {
        $pattern = PatternBuilder::createDiagonalStripes($this->document, 'diag', 20, 10, '#000');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('diag', $pattern->getId());
    }

    public function testCreateDiagonalStripesWithBackground(): void
    {
        $pattern = PatternBuilder::createDiagonalStripes($this->document, 'diag-bg', 20, 10, '#000', '#fff');

        $children = $pattern->getChildren();
        $this->assertCount(2, $children);
    }

    public function testCreateCheckerboard(): void
    {
        $pattern = PatternBuilder::createCheckerboard($this->document, 'check', 20, '#000', '#fff');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('check', $pattern->getId());
        // Background + 2 checker squares
        $this->assertCount(3, $pattern->getChildren());
    }

    public function testCreateGrid(): void
    {
        $pattern = PatternBuilder::createGrid($this->document, 'grid', 20, 1, '#ccc');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('grid', $pattern->getId());
    }

    public function testCreateGridWithBackground(): void
    {
        $pattern = PatternBuilder::createGrid($this->document, 'grid-bg', 20, 1, '#ccc', '#fff');

        $children = $pattern->getChildren();
        $this->assertCount(3, $children);
    }

    public function testCreateCrosshatch(): void
    {
        $pattern = PatternBuilder::createCrosshatch($this->document, 'cross', 20, 1, '#000');

        $this->assertInstanceOf(PatternElement::class, $pattern);
        $this->assertSame('cross', $pattern->getId());
    }

    public function testCreateCrosshatchWithBackground(): void
    {
        $pattern = PatternBuilder::createCrosshatch($this->document, 'cross-bg', 20, 1, '#000', '#fff');

        $children = $pattern->getChildren();
        $this->assertCount(3, $children);
    }

    public function testConstructorWithExistingPattern(): void
    {
        $pattern = new PatternElement();
        $pattern->setId('existing');

        $builder = new PatternBuilder($this->document, $pattern);

        $this->assertSame($pattern, $builder->getPattern());
    }

    public function testAddToDefsReusesCachedDefs(): void
    {
        $builder = PatternBuilder::create($this->document, 'cached-test');

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
        $builder = PatternBuilder::create($document, 'pattern1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $builder->addToDefs();
    }
}
