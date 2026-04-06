<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeSpotLightElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeSpotLightElement::class)]
final class FeSpotLightElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $light = new FeSpotLightElement();
        $this->assertSame('feSpotLight', $light->getTagName());
    }

    public function testSetAndGetPosition(): void
    {
        $light = new FeSpotLightElement();
        $light->setX('100');
        $light->setY('100');
        $light->setZ('50');

        $this->assertSame('100', $light->getX());
        $this->assertSame('100', $light->getY());
        $this->assertSame('50', $light->getZ());
    }

    public function testSetAndGetPointsAt(): void
    {
        $light = new FeSpotLightElement();
        $light->setPointsAtX('0');
        $light->setPointsAtY('0');
        $light->setPointsAtZ('0');

        $this->assertSame('0', $light->getPointsAtX());
        $this->assertSame('0', $light->getPointsAtY());
        $this->assertSame('0', $light->getPointsAtZ());
    }

    public function testSetAndGetSpecularExponent(): void
    {
        $light = new FeSpotLightElement();
        $result = $light->setSpecularExponent('1');

        $this->assertSame($light, $result);
        $this->assertSame('1', $light->getSpecularExponent());
    }

    public function testSetAndGetLimitingConeAngle(): void
    {
        $light = new FeSpotLightElement();
        $result = $light->setLimitingConeAngle('45');

        $this->assertSame($light, $result);
        $this->assertSame('45', $light->getLimitingConeAngle());
    }

    public function testSetPositionHelper(): void
    {
        $light = new FeSpotLightElement();
        $result = $light->setPosition(100, 100, 50);

        $this->assertSame($light, $result);
        $this->assertSame('100', $light->getX());
        $this->assertSame('100', $light->getY());
        $this->assertSame('50', $light->getZ());
    }

    public function testSetPointsAtHelper(): void
    {
        $light = new FeSpotLightElement();
        $result = $light->setPointsAt(0, 0, 0);

        $this->assertSame($light, $result);
        $this->assertSame('0', $light->getPointsAtX());
        $this->assertSame('0', $light->getPointsAtY());
        $this->assertSame('0', $light->getPointsAtZ());
    }

    public function testGettersReturnNullWhenNotSet(): void
    {
        $light = new FeSpotLightElement();

        $this->assertNull($light->getX());
        $this->assertNull($light->getY());
        $this->assertNull($light->getZ());
        $this->assertNull($light->getPointsAtX());
        $this->assertNull($light->getPointsAtY());
        $this->assertNull($light->getPointsAtZ());
        $this->assertNull($light->getSpecularExponent());
        $this->assertNull($light->getLimitingConeAngle());
    }

    public function testCompleteSpotLightConfiguration(): void
    {
        $light = new FeSpotLightElement();
        $light->setPosition(100, 100, 50);
        $light->setPointsAt(0, 0, 0);
        $light->setSpecularExponent('2');
        $light->setLimitingConeAngle('30');

        $this->assertSame('100', $light->getX());
        $this->assertSame('100', $light->getY());
        $this->assertSame('50', $light->getZ());
        $this->assertSame('0', $light->getPointsAtX());
        $this->assertSame('0', $light->getPointsAtY());
        $this->assertSame('0', $light->getPointsAtZ());
        $this->assertSame('2', $light->getSpecularExponent());
        $this->assertSame('30', $light->getLimitingConeAngle());
    }
}
