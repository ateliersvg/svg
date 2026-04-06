<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeBlendElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeBlendElement::class)]
final class FeBlendElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeBlendElement();

        $this->assertSame('feBlend', $element->getTagName());
    }

    public function testSetAndGetMode(): void
    {
        $element = new FeBlendElement();
        $result = $element->setMode('multiply');

        $this->assertSame($element, $result);
        $this->assertSame('multiply', $element->getMode());
    }

    public function testSetAndGetIn2(): void
    {
        $element = new FeBlendElement();
        $result = $element->setIn2('BackgroundImage');

        $this->assertSame($element, $result);
        $this->assertSame('BackgroundImage', $element->getIn2());
    }

    public function testBlendModes(): void
    {
        $element = new FeBlendElement();

        $modes = ['normal', 'multiply', 'screen', 'darken', 'lighten'];
        foreach ($modes as $mode) {
            $element->setMode($mode);
            $this->assertSame($mode, $element->getMode());
        }
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeBlendElement();

        $this->assertNull($element->getMode());
        $this->assertNull($element->getIn2());
    }
}
