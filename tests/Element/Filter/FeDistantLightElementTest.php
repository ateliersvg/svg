<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeDistantLightElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeDistantLightElement::class)]
final class FeDistantLightElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $light = new FeDistantLightElement();
        $this->assertSame('feDistantLight', $light->getTagName());
    }

    public function testSetAndGetAzimuth(): void
    {
        $light = new FeDistantLightElement();
        $result = $light->setAzimuth('45');

        $this->assertSame($light, $result);
        $this->assertSame('45', $light->getAzimuth());
    }

    public function testSetAndGetElevation(): void
    {
        $light = new FeDistantLightElement();
        $result = $light->setElevation('60');

        $this->assertSame($light, $result);
        $this->assertSame('60', $light->getElevation());
    }

    public function testSetDirection(): void
    {
        $light = new FeDistantLightElement();
        $result = $light->setDirection(45, 60);

        $this->assertSame($light, $result);
        $this->assertSame('45', $light->getAzimuth());
        $this->assertSame('60', $light->getElevation());
    }

    public function testGettersReturnNullWhenNotSet(): void
    {
        $light = new FeDistantLightElement();

        $this->assertNull($light->getAzimuth());
        $this->assertNull($light->getElevation());
    }

    public function testWorksWithNumericValues(): void
    {
        $light = new FeDistantLightElement();
        $light->setAzimuth(45);
        $light->setElevation(60);

        $this->assertSame('45', $light->getAzimuth());
        $this->assertSame('60', $light->getElevation());
    }

    public function testWorksWithFloatValues(): void
    {
        $light = new FeDistantLightElement();
        $light->setAzimuth(45.5);
        $light->setElevation(60.25);

        $this->assertSame('45.5', $light->getAzimuth());
        $this->assertSame('60.25', $light->getElevation());
    }

    public function testWorksWithStringValues(): void
    {
        $light = new FeDistantLightElement();
        $light->setAzimuth('90');
        $light->setElevation('30');

        $this->assertSame('90', $light->getAzimuth());
        $this->assertSame('30', $light->getElevation());
    }
}
