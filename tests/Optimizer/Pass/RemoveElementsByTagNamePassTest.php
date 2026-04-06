<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveElementsByTagNamePass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveElementsByTagNamePass::class)]
final class RemoveElementsByTagNamePassTest extends TestCase
{
    public function testRemovesSingleTagName(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc']);

        $svg = new SvgElement();
        $desc = new DescElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(2, $svg->getChildren());
        $this->assertSame($title, $svg->getChildren()[0]);
        $this->assertSame($rect, $svg->getChildren()[1]);
    }

    public function testRemovesMultipleTagNames(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc', 'title']);

        $svg = new SvgElement();
        $desc = new DescElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testRemovesElementsRecursively(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc']);

        $svg = new SvgElement();
        $group = new GroupElement();
        $desc1 = new DescElement();
        $desc2 = new DescElement();
        $rect = new RectElement();

        $group->appendChild($desc2);
        $group->appendChild($rect);

        $svg->appendChild($desc1);
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($group, $svg->getChildren()[0]);
        $this->assertCount(1, $group->getChildren());
        $this->assertSame($rect, $group->getChildren()[0]);
    }

    public function testHandlesEmptyDocument(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc']);

        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testGetNameWithSingleTag(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc']);

        $this->assertSame('remove-desc', $pass->getName());
    }

    public function testGetNameWithMultipleTags(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc', 'title', 'metadata']);

        $this->assertSame('remove-desc-title-metadata', $pass->getName());
    }

    public function testGetNameWithCustomName(): void
    {
        $pass = new RemoveElementsByTagNamePass(['desc', 'title'], 'my-custom-pass');

        $this->assertSame('my-custom-pass', $pass->getName());
    }

    public function testRemoveDescFactory(): void
    {
        $pass = RemoveElementsByTagNamePass::removeDesc();

        $this->assertSame('remove-desc', $pass->getName());

        $svg = new SvgElement();
        $desc = new DescElement();
        $svg->appendChild($desc);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveTitleFactory(): void
    {
        $pass = RemoveElementsByTagNamePass::removeTitle();

        $this->assertSame('remove-title', $pass->getName());

        $svg = new SvgElement();
        $title = new TitleElement();
        $svg->appendChild($title);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveMetadataFactory(): void
    {
        $pass = RemoveElementsByTagNamePass::removeMetadata();

        $this->assertSame('remove-metadata-elements', $pass->getName());
    }

    public function testRemoveAllDescriptiveFactory(): void
    {
        $pass = RemoveElementsByTagNamePass::removeAllDescriptive();

        $this->assertSame('remove-descriptive', $pass->getName());

        $svg = new SvgElement();
        $desc = new DescElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testIsCaseInsensitive(): void
    {
        $pass = new RemoveElementsByTagNamePass(['DESC', 'Title']);

        $svg = new SvgElement();
        $desc = new DescElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }
}
