<?php

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\FilterBuilder;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterBuilder::class)]
final class FilterBuilderTest extends TestCase
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

    public function testCreateFilter(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'test-filter');
        $filter = $helper->getFilter();

        $this->assertEquals('test-filter', $filter->getId());
        $this->assertEquals('filter', $filter->getTagName());
    }

    public function testGaussianBlur(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'blur')
            ->gaussianBlur(5);

        $filter = $helper->getFilter();
        $children = iterator_to_array($filter->getChildren());

        $this->assertCount(1, $children);
        $this->assertEquals('feGaussianBlur', $children[0]->getTagName());
        $this->assertEquals('5', $children[0]->getAttribute('stdDeviation'));
    }

    public function testCreateDropShadow(): void
    {
        $filter = FilterBuilder::createDropShadow($this->getDoc(), 'shadow', 2, 2, 4, '#000', 0.5);

        $this->assertEquals('shadow', $filter->getId());
        $this->assertCount(5, iterator_to_array($filter->getChildren()));
    }

    public function testCreateGlow(): void
    {
        $filter = FilterBuilder::createGlow($this->getDoc(), 'glow', '#3b82f6', 3, 0.8);

        $this->assertEquals('glow', $filter->getId());
        $this->assertCount(4, iterator_to_array($filter->getChildren()));
    }

    public function testCreateBlur(): void
    {
        $filter = FilterBuilder::createBlur($this->getDoc(), 'blur', 10);

        $this->assertEquals('blur', $filter->getId());
        $children = iterator_to_array($filter->getChildren());
        $this->assertCount(1, $children);
        $this->assertEquals('10', $children[0]->getAttribute('stdDeviation'));
    }

    public function testOffset(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'offset-filter')
            ->offset(5, 10);

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('feOffset', $children[0]->getTagName());
        $this->assertEquals('5', $children[0]->getAttribute('dx'));
        $this->assertEquals('10', $children[0]->getAttribute('dy'));
    }

    public function testColorMatrix(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'color-filter')
            ->colorMatrix('saturate', 1.5);

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('feColorMatrix', $children[0]->getTagName());
        $this->assertEquals('saturate', $children[0]->getAttribute('type'));
        $this->assertEquals('1.5', $children[0]->getAttribute('values'));
    }

    public function testCreateDesaturate(): void
    {
        $filter = FilterBuilder::createDesaturate($this->getDoc(), 'desat', 0.5);

        $this->assertEquals('desat', $filter->getId());
        $children = iterator_to_array($filter->getChildren());
        $this->assertCount(1, $children);
        $this->assertEquals('feColorMatrix', $children[0]->getTagName());
        $this->assertEquals('saturate', $children[0]->getAttribute('type'));
        $this->assertEquals('0.5', $children[0]->getAttribute('values'));
    }

    public function testBlend(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'blend-filter')
            ->blend('multiply', 'SourceGraphic', 'BackgroundImage', 'blended');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('feBlend', $children[0]->getTagName());
        $this->assertEquals('multiply', $children[0]->getAttribute('mode'));
        $this->assertEquals('SourceGraphic', $children[0]->getAttribute('in'));
        $this->assertEquals('BackgroundImage', $children[0]->getAttribute('in2'));
        $this->assertEquals('blended', $children[0]->getAttribute('result'));
    }

    public function testComposite(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'comp-filter')
            ->composite('in', 'SourceGraphic', 'BackgroundImage', 'composited');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('feComposite', $children[0]->getTagName());
        $this->assertEquals('in', $children[0]->getAttribute('operator'));
        $this->assertEquals('SourceGraphic', $children[0]->getAttribute('in'));
        $this->assertEquals('BackgroundImage', $children[0]->getAttribute('in2'));
        $this->assertEquals('composited', $children[0]->getAttribute('result'));
    }

    public function testFlood(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'flood-filter')
            ->flood('#ff0000', 0.7, 'flooded');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('feFlood', $children[0]->getTagName());
        $this->assertEquals('#ff0000', $children[0]->getAttribute('flood-color'));
        $this->assertEquals('0.7', $children[0]->getAttribute('flood-opacity'));
        $this->assertEquals('flooded', $children[0]->getAttribute('result'));
    }

    public function testGaussianBlurWithInAndResult(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'blur-filter')
            ->gaussianBlur(3, 'SourceAlpha', 'blurred');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('SourceAlpha', $children[0]->getAttribute('in'));
        $this->assertEquals('blurred', $children[0]->getAttribute('result'));
    }

    public function testOffsetWithInAndResult(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'offset-filter')
            ->offset(2, 3, 'blurred', 'offsetResult');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('blurred', $children[0]->getAttribute('in'));
        $this->assertEquals('offsetResult', $children[0]->getAttribute('result'));
    }

    public function testColorMatrixWithInAndResult(): void
    {
        $helper = FilterBuilder::create($this->getDoc(), 'cm-filter')
            ->colorMatrix('hueRotate', 90, 'SourceGraphic', 'rotated');

        $children = iterator_to_array($helper->getFilter()->getChildren());
        $this->assertEquals('SourceGraphic', $children[0]->getAttribute('in'));
        $this->assertEquals('rotated', $children[0]->getAttribute('result'));
        $this->assertEquals('90', $children[0]->getAttribute('values'));
    }

    public function testApplyFilter(): void
    {
        FilterBuilder::createBlur($this->getDoc(), 'blur', 5);

        $rect = new RectElement();
        $rect->applyFilter('blur');

        $this->assertEquals('url(#blur)', $rect->getAttribute('filter'));
    }

    public function testApplyFilterWithUrlFormat(): void
    {
        $rect = new RectElement();
        $rect->applyFilter('url(#test)');

        $this->assertEquals('url(#test)', $rect->getAttribute('filter'));
    }

    public function testRemoveFilter(): void
    {
        $rect = new RectElement();
        $rect->applyFilter('blur');
        $rect->removeFilter();

        $this->assertNull($rect->getAttribute('filter'));
    }
}
