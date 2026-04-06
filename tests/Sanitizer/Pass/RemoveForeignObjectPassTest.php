<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\ForeignObjectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Sanitizer\Pass\RemoveForeignObjectPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveForeignObjectPass::class)]
final class RemoveForeignObjectPassTest extends TestCase
{
    private RemoveForeignObjectPass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveForeignObjectPass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-foreign-object', $this->pass->getName());
    }

    public function testRemovesForeignObjectElement(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $foreignObject = new ForeignObjectElement();
        $rect = new RectElement();
        $svg->appendChild($foreignObject);
        $svg->appendChild($rect);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $children = $svg->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame('rect', $children[0]->getTagName());
    }

    public function testRemovesNestedForeignObject(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $group = new GroupElement();
        $foreignObject = new ForeignObjectElement();
        $circle = new CircleElement();
        $group->appendChild($foreignObject);
        $group->appendChild($circle);
        $svg->appendChild($group);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $groupChildren = $group->getChildren();
        $this->assertCount(1, $groupChildren);
        $this->assertSame('circle', $groupChildren[0]->getTagName());
    }

    public function testRemovesMultipleForeignObjects(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $fo1 = new ForeignObjectElement();
        $fo2 = new ForeignObjectElement();
        $rect = new RectElement();
        $svg->appendChild($fo1);
        $svg->appendChild($rect);
        $svg->appendChild($fo2);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $children = $svg->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame('rect', $children[0]->getTagName());
    }

    public function testKeepsNonForeignObjectElements(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $rect = new RectElement();
        $circle = new CircleElement();
        $svg->appendChild($rect);
        $svg->appendChild($circle);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(2, $svg->getChildren());
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->sanitize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testHandlesDocumentWithNoForeignObjects(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $group = new GroupElement();
        $rect = new RectElement();
        $group->appendChild($rect);
        $svg->appendChild($group);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertCount(1, $group->getChildren());
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
