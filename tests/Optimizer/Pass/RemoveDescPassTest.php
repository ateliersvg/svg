<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveDescPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveDescPass::class)]
final class RemoveDescPassTest extends TestCase
{
    private RemoveDescPass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveDescPass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-desc', $this->pass->getName());
    }

    public function testRemovesDescElement(): void
    {
        $svg = new SvgElement();
        $desc = new DescElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(2, $svg->getChildren());

        $this->pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testRemovesMultipleDescElements(): void
    {
        $svg = new SvgElement();
        $desc1 = new DescElement();
        $desc2 = new DescElement();
        $rect = new RectElement();

        $svg->appendChild($desc1);
        $svg->appendChild($rect);
        $svg->appendChild($desc2);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $this->pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($rect, $svg->getChildren()[0]);
    }

    public function testHandlesDocumentWithoutDesc(): void
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
        $desc = new DescElement();
        $title = new TitleElement();
        $rect = new RectElement();

        $svg->appendChild($desc);
        $svg->appendChild($title);
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->pass->optimize($document);

        $this->assertCount(2, $svg->getChildren());
        $this->assertSame($title, $svg->getChildren()[0]);
        $this->assertSame($rect, $svg->getChildren()[1]);
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }
}
