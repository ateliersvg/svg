<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Sanitizer\Pass\RemoveEventHandlersPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEventHandlersPass::class)]
final class RemoveEventHandlersPassTest extends TestCase
{
    private RemoveEventHandlersPass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveEventHandlersPass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-event-handlers', $this->pass->getName());
    }

    public function testRemovesOnclickAttribute(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setAttribute('onclick', 'alert("xss")');
        $svg->appendChild($circle);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($circle->getAttribute('onclick'));
    }

    public function testRemovesOnloadAttribute(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $svg->setAttribute('onload', 'init()');
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($svg->getAttribute('onload'));
    }

    public function testRemovesMultipleEventHandlers(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('onclick', 'click()');
        $rect->setAttribute('onmouseover', 'hover()');
        $rect->setAttribute('onerror', 'error()');
        $rect->setAttribute('fill', 'red');
        $svg->appendChild($rect);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($rect->getAttribute('onclick'));
        $this->assertNull($rect->getAttribute('onmouseover'));
        $this->assertNull($rect->getAttribute('onerror'));
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testRemovesNestedEventHandlers(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $group = new GroupElement();
        $circle = new CircleElement();
        $circle->setAttribute('onclick', 'alert(1)');
        $group->appendChild($circle);
        $svg->appendChild($group);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($circle->getAttribute('onclick'));
    }

    public function testKeepsNonEventAttributes(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setAttribute('fill', 'blue');
        $circle->setAttribute('stroke', 'black');
        $circle->setAttribute('id', 'my-circle');
        $svg->appendChild($circle);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertSame('blue', $circle->getAttribute('fill'));
        $this->assertSame('black', $circle->getAttribute('stroke'));
        $this->assertSame('my-circle', $circle->getAttribute('id'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->sanitize($document);

        $this->assertNull($document->getRootElement());
    }
}
