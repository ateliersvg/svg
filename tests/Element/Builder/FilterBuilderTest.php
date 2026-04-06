<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\FilterBuilder;
use Atelier\Svg\Element\Filter\FilterElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterBuilder::class)]
final class FilterBuilderTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(400, 300);
    }

    public function testCreate(): void
    {
        $builder = FilterBuilder::create($this->document, 'filter1');

        $this->assertInstanceOf(FilterBuilder::class, $builder);
        $filter = $builder->getFilter();
        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('filter1', $filter->getId());
    }

    public function testGaussianBlur(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->gaussianBlur(5.0);

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testGaussianBlurWithInAndResult(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->gaussianBlur(5.0, 'SourceAlpha', 'blur');

        $blur = $builder->getFilter()->getChildren()[0];
        $this->assertSame('SourceAlpha', $blur->getAttribute('in'));
        $this->assertSame('blur', $blur->getAttribute('result'));
    }

    public function testOffset(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->offset(2.0, 3.0);

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testOffsetWithInAndResult(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->offset(2.0, 3.0, 'blur', 'offsetBlur');

        $offset = $builder->getFilter()->getChildren()[0];
        $this->assertSame('blur', $offset->getAttribute('in'));
        $this->assertSame('offsetBlur', $offset->getAttribute('result'));
    }

    public function testColorMatrix(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->colorMatrix('saturate', 0.5);

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testColorMatrixWithAllOptions(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->colorMatrix('hueRotate', '90', 'SourceGraphic', 'hue');

        $cm = $builder->getFilter()->getChildren()[0];
        $this->assertSame('hueRotate', $cm->getAttribute('type'));
        $this->assertSame('90', $cm->getAttribute('values'));
        $this->assertSame('SourceGraphic', $cm->getAttribute('in'));
        $this->assertSame('hue', $cm->getAttribute('result'));
    }

    public function testColorMatrixWithoutValues(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->colorMatrix('luminanceToAlpha');

        $cm = $builder->getFilter()->getChildren()[0];
        $this->assertSame('luminanceToAlpha', $cm->getAttribute('type'));
        $this->assertNull($cm->getAttribute('values'));
    }

    public function testBlend(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->blend('multiply');

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testBlendWithAllOptions(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->blend('screen', 'SourceGraphic', 'shadow', 'blended');

        $blend = $builder->getFilter()->getChildren()[0];
        $this->assertSame('screen', $blend->getAttribute('mode'));
        $this->assertSame('SourceGraphic', $blend->getAttribute('in'));
        $this->assertSame('shadow', $blend->getAttribute('in2'));
        $this->assertSame('blended', $blend->getAttribute('result'));
    }

    public function testComposite(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->composite('in');

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testCompositeWithAllOptions(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->composite('over', 'color', 'offsetBlur', 'shadow');

        $composite = $builder->getFilter()->getChildren()[0];
        $this->assertSame('over', $composite->getAttribute('operator'));
        $this->assertSame('color', $composite->getAttribute('in'));
        $this->assertSame('offsetBlur', $composite->getAttribute('in2'));
        $this->assertSame('shadow', $composite->getAttribute('result'));
    }

    public function testFlood(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->flood('#000', 0.5);

        $children = $builder->getFilter()->getChildren();
        $this->assertCount(1, $children);
    }

    public function testFloodWithResult(): void
    {
        $builder = FilterBuilder::create($this->document, 'f')
            ->flood('#f00', 0.8, 'color');

        $flood = $builder->getFilter()->getChildren()[0];
        $this->assertSame('color', $flood->getAttribute('result'));
    }

    public function testAddToDefs(): void
    {
        FilterBuilder::create($this->document, 'f')
            ->gaussianBlur(5.0)
            ->addToDefs();

        $root = $this->document->getRootElement();
        $defs = $root->getChildren()[0];
        $this->assertInstanceOf(DefsElement::class, $defs);
    }

    public function testAddToDefsReusesExistingDefs(): void
    {
        FilterBuilder::create($this->document, 'f1')->addToDefs();
        FilterBuilder::create($this->document, 'f2')->addToDefs();

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }

    public function testCreateDropShadow(): void
    {
        $filter = FilterBuilder::createDropShadow($this->document, 'shadow', 2, 2, 4, '#000', 0.3);

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('shadow', $filter->getId());
        // blur + offset + flood + composite + blend = 5 children
        $this->assertCount(5, $filter->getChildren());
    }

    public function testCreateGlow(): void
    {
        $filter = FilterBuilder::createGlow($this->document, 'glow', '#3b82f6', 2, 0.8);

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('glow', $filter->getId());
        // blur + flood + composite + blend = 4 children
        $this->assertCount(4, $filter->getChildren());
    }

    public function testCreateBlur(): void
    {
        $filter = FilterBuilder::createBlur($this->document, 'blur', 5);

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('blur', $filter->getId());
        $this->assertCount(1, $filter->getChildren());
    }

    public function testCreateDesaturate(): void
    {
        $filter = FilterBuilder::createDesaturate($this->document, 'desat', 1.0);

        $this->assertInstanceOf(FilterElement::class, $filter);
        $this->assertSame('desat', $filter->getId());
        $this->assertCount(1, $filter->getChildren());
    }

    public function testConstructorWithExistingFilter(): void
    {
        $filter = new FilterElement();
        $filter->setId('existing');

        $builder = new FilterBuilder($this->document, $filter);

        $this->assertSame($filter, $builder->getFilter());
    }

    public function testAddToDefsReusesCachedDefs(): void
    {
        $builder = FilterBuilder::create($this->document, 'cached-test');

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
        $builder = FilterBuilder::create($document, 'filter1');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        $builder->addToDefs();
    }
}
