<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\AbstractFilterPrimitiveElement;
use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use Atelier\Svg\Element\Filter\FeOffsetElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests AbstractFilterPrimitiveElement via concrete subclasses.
 */
#[CoversClass(AbstractFilterPrimitiveElement::class)]
final class AbstractFilterPrimitiveElementTest extends TestCase
{
    public function testSetAndGetResult(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setResult('blur');

        $this->assertSame($element, $result);
        $this->assertSame('blur', $element->getResult());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setIn('SourceGraphic');

        $this->assertSame($element, $result);
        $this->assertSame('SourceGraphic', $element->getIn());
    }

    public function testSetInWithSourceAlpha(): void
    {
        $element = new FeOffsetElement();
        $element->setIn('SourceAlpha');

        $this->assertSame('SourceAlpha', $element->getIn());
    }

    public function testSetInWithPrimitiveResult(): void
    {
        $element = new FeOffsetElement();
        $element->setIn('blurResult');

        $this->assertSame('blurResult', $element->getIn());
    }

    public function testSetAndGetX(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setX(10);

        $this->assertSame($element, $result);
        $this->assertSame('10', $element->getX());
    }

    public function testSetXWithFloat(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setX(5.5);

        $this->assertSame('5.5', $element->getX());
    }

    public function testSetXWithString(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setX('10%');

        $this->assertSame('10%', $element->getX());
    }

    public function testSetAndGetY(): void
    {
        $element = new FeOffsetElement();
        $result = $element->setY(20);

        $this->assertSame($element, $result);
        $this->assertSame('20', $element->getY());
    }

    public function testSetYWithFloat(): void
    {
        $element = new FeOffsetElement();
        $element->setY(7.5);

        $this->assertSame('7.5', $element->getY());
    }

    public function testSetAndGetWidth(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setWidth(100);

        $this->assertSame($element, $result);
        $this->assertSame('100', $element->getWidth());
    }

    public function testSetWidthWithFloat(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setWidth(50.5);

        $this->assertSame('50.5', $element->getWidth());
    }

    public function testSetWidthWithString(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setWidth('100%');

        $this->assertSame('100%', $element->getWidth());
    }

    public function testSetAndGetHeight(): void
    {
        $element = new FeOffsetElement();
        $result = $element->setHeight(200);

        $this->assertSame($element, $result);
        $this->assertSame('200', $element->getHeight());
    }

    public function testSetHeightWithFloat(): void
    {
        $element = new FeOffsetElement();
        $element->setHeight(75.5);

        $this->assertSame('75.5', $element->getHeight());
    }

    public function testSetHeightWithString(): void
    {
        $element = new FeOffsetElement();
        $element->setHeight('50%');

        $this->assertSame('50%', $element->getHeight());
    }

    public function testGetAttributesWhenNotSet(): void
    {
        $element = new FeGaussianBlurElement();

        $this->assertNull($element->getResult());
        $this->assertNull($element->getIn());
        $this->assertNull($element->getX());
        $this->assertNull($element->getY());
        $this->assertNull($element->getWidth());
        $this->assertNull($element->getHeight());
    }
}
