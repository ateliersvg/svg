<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeGaussianBlurElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeGaussianBlurElement::class)]
final class FeGaussianBlurElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeGaussianBlurElement();

        $this->assertSame('feGaussianBlur', $element->getTagName());
    }

    public function testSetAndGetStdDeviation(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setStdDeviation('5');

        $this->assertSame($element, $result);
        $this->assertSame('5', $element->getStdDeviation());
    }

    public function testSetStdDeviationWithNumeric(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setStdDeviation(10);

        $this->assertSame('10', $element->getStdDeviation());
    }

    public function testSetStdDeviationWithFloat(): void
    {
        $element = new FeGaussianBlurElement();
        $element->setStdDeviation(5.5);

        $this->assertSame('5.5', $element->getStdDeviation());
    }

    public function testSetAndGetEdgeMode(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setEdgeMode('wrap');

        $this->assertSame($element, $result);
        $this->assertSame('wrap', $element->getEdgeMode());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setIn('SourceGraphic');

        $this->assertSame($element, $result);
        $this->assertSame('SourceGraphic', $element->getIn());
    }

    public function testSetAndGetResult(): void
    {
        $element = new FeGaussianBlurElement();
        $result = $element->setResult('blur');

        $this->assertSame($element, $result);
        $this->assertSame('blur', $element->getResult());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeGaussianBlurElement();

        $this->assertNull($element->getStdDeviation());
        $this->assertNull($element->getEdgeMode());
        $this->assertNull($element->getIn());
        $this->assertNull($element->getResult());
    }
}
