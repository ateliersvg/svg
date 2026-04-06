<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveTitlePass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveTitlePass::class)]
final class RemoveTitlePassTest extends TestCase
{
    private RemoveTitlePass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveTitlePass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-title', $this->pass->getName());
    }

    public function testRemovesTitleElement(): void
    {
        $svg = new SvgElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(2, $svg->getChildren());

        $this->pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testRemovesMultipleTitleElements(): void
    {
        $svg = new SvgElement();
        $title1 = new TitleElement();
        $title2 = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($title1);
        $svg->appendChild($rect);
        $svg->appendChild($title2);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $this->pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testHandlesDocumentWithoutTitle(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();

        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
    }

    public function testPreservesOtherElements(): void
    {
        $svg = new SvgElement();
        $title = new TitleElement();
        $desc = new DescElement();
        $rect = new RectElement();

        $svg->appendChild($title);
        $svg->appendChild($desc);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->pass->optimize($document);

        $this->assertCount(2, $svg->getChildren());
        $this->assertSame($desc, $svg->getChildren()[0]);
        $this->assertSame($rect, $svg->getChildren()[1]);
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }
}
