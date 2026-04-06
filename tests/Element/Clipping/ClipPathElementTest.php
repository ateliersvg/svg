<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Clipping;

use Atelier\Svg\Element\Clipping\ClipPathElement;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClipPathElement::class)]
final class ClipPathElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $clipPath = new ClipPathElement();
        $this->assertSame('clipPath', $clipPath->getTagName());
    }

    public function testSetAndGetClipPathUnits(): void
    {
        $clipPath = new ClipPathElement();
        $result = $clipPath->setClipPathUnits('userSpaceOnUse');

        $this->assertSame($clipPath, $result);
        $this->assertSame('userSpaceOnUse', $clipPath->getClipPathUnits());
    }

    public function testClipPathUnitsDefaultsToUserSpaceOnUse(): void
    {
        $clipPath = new ClipPathElement();

        // According to SVG spec, clipPathUnits defaults to 'userSpaceOnUse'
        // but we don't set it by default, it's null until set
        $this->assertNull($clipPath->getClipPathUnits());
    }

    public function testCanContainChildren(): void
    {
        $clipPath = new ClipPathElement();
        $rect = new RectElement();

        $clipPath->appendChild($rect);

        $this->assertTrue($clipPath->hasChildren());
        $this->assertSame(1, $clipPath->getChildCount());
        $this->assertSame($rect, $clipPath->getChildren()[0]);
    }

    public function testCanContainMultipleShapes(): void
    {
        $clipPath = new ClipPathElement();
        $rect1 = new RectElement();
        $rect2 = new RectElement();

        $clipPath->appendChild($rect1);
        $clipPath->appendChild($rect2);

        $this->assertSame(2, $clipPath->getChildCount());
    }

    public function testGetterReturnsNullWhenNotSet(): void
    {
        $clipPath = new ClipPathElement();
        $this->assertNull($clipPath->getClipPathUnits());
    }
}
