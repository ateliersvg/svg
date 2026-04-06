<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeTurbulenceElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeTurbulenceElement::class)]
final class FeTurbulenceElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeTurbulenceElement();

        $this->assertSame('feTurbulence', $element->getTagName());
    }

    public function testSetAndGetBaseFrequency(): void
    {
        $element = new FeTurbulenceElement();
        $result = $element->setBaseFrequency('0.05');

        $this->assertSame($element, $result);
        $this->assertSame('0.05', $element->getBaseFrequency());
    }

    public function testSetAndGetNumOctaves(): void
    {
        $element = new FeTurbulenceElement();
        $result = $element->setNumOctaves(2);

        $this->assertSame($element, $result);
        $this->assertSame('2', $element->getNumOctaves());
    }

    public function testSetAndGetType(): void
    {
        $element = new FeTurbulenceElement();
        $result = $element->setType('turbulence');

        $this->assertSame($element, $result);
        $this->assertSame('turbulence', $element->getType());
    }

    public function testSetAndGetSeed(): void
    {
        $element = new FeTurbulenceElement();
        $result = $element->setSeed(123);

        $this->assertSame($element, $result);
        $this->assertSame('123', $element->getSeed());
    }

    public function testTypes(): void
    {
        $element = new FeTurbulenceElement();

        $element->setType('turbulence');
        $this->assertSame('turbulence', $element->getType());

        $element->setType('fractalNoise');
        $this->assertSame('fractalNoise', $element->getType());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeTurbulenceElement();

        $this->assertNull($element->getBaseFrequency());
        $this->assertNull($element->getNumOctaves());
        $this->assertNull($element->getType());
        $this->assertNull($element->getSeed());
    }
}
