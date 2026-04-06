<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeDiffuseLightingElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeDiffuseLightingElement::class)]
final class FeDiffuseLightingElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeDiffuseLightingElement();

        $this->assertSame('feDiffuseLighting', $element->getTagName());
    }

    public function testSetAndGetSurfaceScale(): void
    {
        $element = new FeDiffuseLightingElement();
        $result = $element->setSurfaceScale(5);

        $this->assertSame($element, $result);
        $this->assertSame('5', $element->getSurfaceScale());
    }

    public function testSetAndGetDiffuseConstant(): void
    {
        $element = new FeDiffuseLightingElement();
        $result = $element->setDiffuseConstant(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getDiffuseConstant());
    }

    public function testSetAndGetLightingColor(): void
    {
        $element = new FeDiffuseLightingElement();
        $result = $element->setLightingColor('white');

        $this->assertSame($element, $result);
        $this->assertSame('white', $element->getLightingColor());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeDiffuseLightingElement();
        $result = $element->setIn('SourceAlpha');

        $this->assertSame($element, $result);
        $this->assertSame('SourceAlpha', $element->getIn());
    }

    public function testSetAndGetResult(): void
    {
        $element = new FeDiffuseLightingElement();
        $result = $element->setResult('light');

        $this->assertSame($element, $result);
        $this->assertSame('light', $element->getResult());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeDiffuseLightingElement();

        $this->assertNull($element->getSurfaceScale());
        $this->assertNull($element->getDiffuseConstant());
        $this->assertNull($element->getLightingColor());
        $this->assertNull($element->getIn());
        $this->assertNull($element->getResult());
    }
}
