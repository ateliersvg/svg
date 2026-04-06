<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FePointLightElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FePointLightElement::class)]
final class FePointLightElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $light = new FePointLightElement();
        $this->assertSame('fePointLight', $light->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $light = new FePointLightElement();
        $result = $light->setX('100');

        $this->assertSame($light, $result);
        $this->assertSame('100', $light->getX());
    }

    public function testSetAndGetY(): void
    {
        $light = new FePointLightElement();
        $result = $light->setY('100');

        $this->assertSame($light, $result);
        $this->assertSame('100', $light->getY());
    }

    public function testSetAndGetZ(): void
    {
        $light = new FePointLightElement();
        $result = $light->setZ('50');

        $this->assertSame($light, $result);
        $this->assertSame('50', $light->getZ());
    }

    public function testSetPosition(): void
    {
        $light = new FePointLightElement();
        $result = $light->setPosition(100, 100, 50);

        $this->assertSame($light, $result);
        $this->assertSame('100', $light->getX());
        $this->assertSame('100', $light->getY());
        $this->assertSame('50', $light->getZ());
    }

    public function testGettersReturnNullWhenNotSet(): void
    {
        $light = new FePointLightElement();

        $this->assertNull($light->getX());
        $this->assertNull($light->getY());
        $this->assertNull($light->getZ());
    }

    public function testWorksWithNumericValues(): void
    {
        $light = new FePointLightElement();
        $light->setX(5);
        $light->setY(10);
        $light->setZ(15);

        $this->assertSame('5', $light->getX());
        $this->assertSame('10', $light->getY());
        $this->assertSame('15', $light->getZ());
    }

    public function testWorksWithFloatValues(): void
    {
        $light = new FePointLightElement();
        $light->setX(5.5);
        $light->setY(10.25);
        $light->setZ(15.75);

        $this->assertSame('5.5', $light->getX());
        $this->assertSame('10.25', $light->getY());
        $this->assertSame('15.75', $light->getZ());
    }
}
