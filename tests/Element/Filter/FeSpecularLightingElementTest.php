<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeSpecularLightingElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeSpecularLightingElement::class)]
final class FeSpecularLightingElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeSpecularLightingElement();

        $this->assertSame('feSpecularLighting', $element->getTagName());
    }

    public function testSetAndGetSurfaceScale(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setSurfaceScale(5);

        $this->assertSame($element, $result);
        $this->assertSame('5', $element->getSurfaceScale());
    }

    public function testSetAndGetSpecularConstant(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setSpecularConstant(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getSpecularConstant());
    }

    public function testSetAndGetSpecularExponent(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setSpecularExponent(20);

        $this->assertSame($element, $result);
        $this->assertSame('20', $element->getSpecularExponent());
    }

    public function testSetAndGetLightingColor(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setLightingColor('white');

        $this->assertSame($element, $result);
        $this->assertSame('white', $element->getLightingColor());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setIn('SourceAlpha');

        $this->assertSame($element, $result);
        $this->assertSame('SourceAlpha', $element->getIn());
    }

    public function testSetAndGetResult(): void
    {
        $element = new FeSpecularLightingElement();
        $result = $element->setResult('specular');

        $this->assertSame($element, $result);
        $this->assertSame('specular', $element->getResult());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeSpecularLightingElement();

        $this->assertNull($element->getSurfaceScale());
        $this->assertNull($element->getSpecularConstant());
        $this->assertNull($element->getSpecularExponent());
        $this->assertNull($element->getLightingColor());
        $this->assertNull($element->getIn());
        $this->assertNull($element->getResult());
    }
}
