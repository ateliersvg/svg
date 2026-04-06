<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\ScriptElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Sanitizer\Pass\RemoveScriptElementsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveScriptElementsPass::class)]
final class RemoveScriptElementsPassTest extends TestCase
{
    private RemoveScriptElementsPass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveScriptElementsPass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-script-elements', $this->pass->getName());
    }

    public function testRemovesScriptElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $script = new ScriptElement();
        $svg->appendChild($script);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemovesNestedScriptElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $group = new GroupElement();
        $script = new ScriptElement();
        $group->appendChild($script);
        $svg->appendChild($group);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(0, $group->getChildren());
    }

    public function testKeepsNonScriptElements(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $script = new ScriptElement();
        $svg->appendChild($circle);
        $svg->appendChild($script);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame('circle', $svg->getChildren()[0]->getTagName());
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->sanitize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testSkipsNonContainerChildElements(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $image = new ImageElement();
        $svg->appendChild($image);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame('image', $svg->getChildren()[0]->getTagName());
    }
}
