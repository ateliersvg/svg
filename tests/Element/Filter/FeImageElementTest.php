<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeImageElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeImageElement::class)]
final class FeImageElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeImageElement();

        $this->assertSame('feImage', $element->getTagName());
    }

    public function testSetAndGetHref(): void
    {
        $element = new FeImageElement();
        $result = $element->setHref('image.png');

        $this->assertSame($element, $result);
        $this->assertSame('image.png', $element->getHref());
    }

    public function testSetHrefToElement(): void
    {
        $element = new FeImageElement();
        $element->setHref('#myGradient');

        $this->assertSame('#myGradient', $element->getHref());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeImageElement();

        $this->assertNull($element->getHref());
    }
}
