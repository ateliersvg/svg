<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\DefsElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefsElement::class)]
final class DefsElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $defs = new DefsElement();

        $this->assertSame('defs', $defs->getTagName());
    }

    public function testConstructWithInitialAttributes(): void
    {
        $defs = new DefsElement(['id' => 'my-defs', 'class' => 'definitions']);

        $this->assertSame('defs', $defs->getTagName());
        $this->assertSame('my-defs', $defs->getAttribute('id'));
        $this->assertSame('definitions', $defs->getAttribute('class'));
    }

    public function testGetTagName(): void
    {
        $defs = new DefsElement();

        $this->assertSame('defs', $defs->getTagName());
    }

    public function testCanContainChildren(): void
    {
        $defs = new DefsElement();
        $circle = new CircleElement();

        $defs->appendChild($circle);

        $this->assertTrue($defs->hasChildren());
        $this->assertSame(1, $defs->getChildCount());
        $this->assertSame($circle, $defs->getChildren()[0]);
    }

    public function testCanContainMultipleChildren(): void
    {
        $defs = new DefsElement();
        $circle = new CircleElement();
        $path = new PathElement();

        $defs->appendChild($circle);
        $defs->appendChild($path);

        $this->assertSame(2, $defs->getChildCount());
        $this->assertSame($circle, $defs->getChildren()[0]);
        $this->assertSame($path, $defs->getChildren()[1]);
    }

    public function testRemoveChild(): void
    {
        $defs = new DefsElement();
        $circle = new CircleElement();

        $defs->appendChild($circle);
        $this->assertSame(1, $defs->getChildCount());

        $defs->removeChild($circle);
        $this->assertSame(0, $defs->getChildCount());
        $this->assertFalse($defs->hasChildren());
    }

    public function testClearChildren(): void
    {
        $defs = new DefsElement();
        $circle = new CircleElement();
        $path = new PathElement();

        $defs->appendChild($circle);
        $defs->appendChild($path);
        $this->assertSame(2, $defs->getChildCount());

        $defs->clearChildren();
        $this->assertSame(0, $defs->getChildCount());
        $this->assertFalse($defs->hasChildren());
    }
}
