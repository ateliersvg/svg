<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractElement::class)]
final class AbstractElementFilterAndPaintTest extends TestCase
{
    public function testApplyFilterWithId(): void
    {
        $element = new RectElement();
        $result = $element->applyFilter('blur');

        $this->assertSame($element, $result);
        $this->assertSame('url(#blur)', $element->getAttribute('filter'));
    }

    public function testApplyFilterWithUrl(): void
    {
        $element = new RectElement();
        $element->applyFilter('url(#shadow)');

        $this->assertSame('url(#shadow)', $element->getAttribute('filter'));
    }

    public function testRemoveFilter(): void
    {
        $element = new RectElement();
        $element->applyFilter('blur');
        $result = $element->removeFilter();

        $this->assertSame($element, $result);
        $this->assertNull($element->getAttribute('filter'));
    }

    public function testGetFilterIdFromUrlFormat(): void
    {
        $element = new RectElement();
        $element->applyFilter('blur');

        $this->assertSame('blur', $element->getFilterId());
    }

    public function testGetFilterIdReturnsNullWhenNoFilter(): void
    {
        $element = new RectElement();

        $this->assertNull($element->getFilterId());
    }

    public function testGetFilterIdWithRawValue(): void
    {
        $element = new RectElement();
        $element->setAttribute('filter', 'some-raw-value');

        $this->assertSame('some-raw-value', $element->getFilterId());
    }

    public function testSetFillPaintServerWithId(): void
    {
        $element = new RectElement();
        $result = $element->setFillPaintServer('gradient1');

        $this->assertSame($element, $result);
        $this->assertSame('url(#gradient1)', $element->getAttribute('fill'));
    }

    public function testSetFillPaintServerWithUrl(): void
    {
        $element = new RectElement();
        $element->setFillPaintServer('url(#pattern1)');

        $this->assertSame('url(#pattern1)', $element->getAttribute('fill'));
    }

    public function testSetStrokePaintServerWithId(): void
    {
        $element = new RectElement();
        $result = $element->setStrokePaintServer('gradient2');

        $this->assertSame($element, $result);
        $this->assertSame('url(#gradient2)', $element->getAttribute('stroke'));
    }

    public function testSetStrokePaintServerWithUrl(): void
    {
        $element = new RectElement();
        $element->setStrokePaintServer('url(#pattern2)');

        $this->assertSame('url(#pattern2)', $element->getAttribute('stroke'));
    }

    public function testSetOpacity(): void
    {
        $element = new RectElement();
        $result = $element->setOpacity(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getAttribute('opacity'));
    }

    public function testGetOpacity(): void
    {
        $element = new RectElement();
        $element->setOpacity(0.75);

        $this->assertSame(0.75, $element->getOpacity());
    }

    public function testGetOpacityReturnsNullWhenNotSet(): void
    {
        $element = new RectElement();

        $this->assertNull($element->getOpacity());
    }
}
