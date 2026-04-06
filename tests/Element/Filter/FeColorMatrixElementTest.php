<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeColorMatrixElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeColorMatrixElement::class)]
final class FeColorMatrixElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeColorMatrixElement();

        $this->assertSame('feColorMatrix', $element->getTagName());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeColorMatrixElement();
        $result = $element->setType('saturate');

        $this->assertSame($element, $result);
        $this->assertSame('saturate', $element->getType());
    }

    public function testSetAndGetValues(): void
    {
        $element = new FeColorMatrixElement();
        $result = $element->setValues('0.5');

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getValues());
    }

    public function testSetTypeMatrix(): void
    {
        $element = new FeColorMatrixElement();
        $element->setType('matrix');
        $element->setValues('1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0');

        $this->assertSame('matrix', $element->getType());
        $this->assertSame('1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 1 0', $element->getValues());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeColorMatrixElement();

        $this->assertNull($element->getType());
        $this->assertNull($element->getValues());
    }
}
