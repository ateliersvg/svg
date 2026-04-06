<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use Atelier\Svg\Element\Filter\FilterElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterElement::class)]
final class FilterElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $filter = new FilterElement();

        $this->assertSame('filter', $filter->getTagName());
    }

    public function testCanContainFilterPrimitives(): void
    {
        $filter = new FilterElement();
        $blur = new FeGaussianBlurElement();

        $filter->appendChild($blur);

        $this->assertTrue($filter->hasChildren());
        $this->assertSame(1, $filter->getChildCount());
        $this->assertSame($blur, $filter->getChildren()[0]);
    }

    public function testSetAndGetX(): void
    {
        $filter = new FilterElement();
        $result = $filter->setX('-10%');

        $this->assertSame($filter, $result);
        $this->assertSame('-10%', $filter->getX());
    }

    public function testSetAndGetY(): void
    {
        $filter = new FilterElement();
        $result = $filter->setY('-10%');

        $this->assertSame($filter, $result);
        $this->assertSame('-10%', $filter->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $filter = new FilterElement();
        $result = $filter->setWidth('120%');

        $this->assertSame($filter, $result);
        $this->assertSame('120%', $filter->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $filter = new FilterElement();
        $result = $filter->setHeight('120%');

        $this->assertSame($filter, $result);
        $this->assertSame('120%', $filter->getHeight());
    }

    public function testSetAndGetFilterUnits(): void
    {
        $filter = new FilterElement();
        $result = $filter->setFilterUnits('userSpaceOnUse');

        $this->assertSame($filter, $result);
        $this->assertSame('userSpaceOnUse', $filter->getFilterUnits());
    }

    public function testSetAndGetPrimitiveUnits(): void
    {
        $filter = new FilterElement();
        $result = $filter->setPrimitiveUnits('objectBoundingBox');

        $this->assertSame($filter, $result);
        $this->assertSame('objectBoundingBox', $filter->getPrimitiveUnits());
    }

    public function testSetAndGetHref(): void
    {
        $filter = new FilterElement();
        $result = $filter->setHref('#otherFilter');

        $this->assertSame($filter, $result);
        $this->assertSame('#otherFilter', $filter->getHref());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $filter = new FilterElement();

        $this->assertNull($filter->getX());
        $this->assertNull($filter->getY());
        $this->assertNull($filter->getWidth());
        $this->assertNull($filter->getHeight());
        $this->assertNull($filter->getFilterUnits());
        $this->assertNull($filter->getPrimitiveUnits());
        $this->assertNull($filter->getHref());
    }
}
