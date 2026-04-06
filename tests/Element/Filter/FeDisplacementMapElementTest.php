<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeDisplacementMapElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeDisplacementMapElement::class)]
final class FeDisplacementMapElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeDisplacementMapElement();

        $this->assertSame('feDisplacementMap', $element->getTagName());
    }

    public function testSetAndGetIn2(): void
    {
        $element = new FeDisplacementMapElement();
        $result = $element->setIn2('displacement');

        $this->assertSame($element, $result);
        $this->assertSame('displacement', $element->getIn2());
    }

    public function testSetAndGetScale(): void
    {
        $element = new FeDisplacementMapElement();
        $result = $element->setScaleAttribute(20);

        $this->assertSame($element, $result);
        $this->assertSame('20', $element->getScaleAttribute());
    }

    public function testSetAndGetXChannelSelector(): void
    {
        $element = new FeDisplacementMapElement();
        $result = $element->setXChannelSelector('R');

        $this->assertSame($element, $result);
        $this->assertSame('R', $element->getXChannelSelector());
    }

    public function testSetAndGetYChannelSelector(): void
    {
        $element = new FeDisplacementMapElement();
        $result = $element->setYChannelSelector('G');

        $this->assertSame($element, $result);
        $this->assertSame('G', $element->getYChannelSelector());
    }

    public function testChannelSelectors(): void
    {
        $element = new FeDisplacementMapElement();

        $channels = ['R', 'G', 'B', 'A'];
        foreach ($channels as $channel) {
            $element->setXChannelSelector($channel);
            $this->assertSame($channel, $element->getXChannelSelector());
        }
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeDisplacementMapElement();

        $this->assertNull($element->getIn2());
        $this->assertNull($element->getScaleAttribute());
        $this->assertNull($element->getXChannelSelector());
        $this->assertNull($element->getYChannelSelector());
    }
}
