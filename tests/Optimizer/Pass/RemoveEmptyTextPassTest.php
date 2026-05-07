<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyTextPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEmptyTextPass::class)]
final class RemoveEmptyTextPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveEmptyTextPass();
        $this->assertSame('remove-empty-text', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveEmptyTextPass();
        $document = new Document();
        $pass->optimize($document);
        $this->assertNull($document->getRootElement());
    }

    public function testRemovesEmptyTextElement(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $svg->appendChild($text);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());
        $pass->optimize($document);
        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemovesTextWithWhitespaceOnlyContent(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $text->setTextContent('   ');
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepsTextWithContent(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = TextElement::create(10, 20, 'Hello');
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemovesEmptyTspan(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $tspan = new TspanElement();
        $text->appendChild($tspan);
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        // Both tspan and text are empty, both should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepsTextWithNonEmptyTspan(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $tspan = new TspanElement();
        $tspan->setTextContent('content');
        $text->appendChild($tspan);
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepsEmptyTextWithId(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $text->setAttribute('id', 'my-text');
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepsEmptyTextWithClass(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $text = new TextElement();
        $text->setAttribute('class', 'placeholder');
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $svg->getChildren());
    }

    public function testDoesNotRemoveNonTextElements(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $svg->appendChild($group);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(2, $svg->getChildren());
    }

    public function testRemovesNestedEmptyText(): void
    {
        $pass = new RemoveEmptyTextPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $text = new TextElement();
        $group->appendChild($text);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(0, $group->getChildren());
    }

    public function testCustomPreservingAttributes(): void
    {
        $pass = new RemoveEmptyTextPass(preservingAttributes: ['data-keep']);
        $svg = new SvgElement();

        $text1 = new TextElement();
        $text1->setAttribute('data-keep', 'yes');
        $svg->appendChild($text1);

        $text2 = new TextElement();
        $text2->setAttribute('id', 'should-go');
        $svg->appendChild($text2);

        $document = new Document($svg);
        $pass->optimize($document);

        // text1 kept (has data-keep), text2 removed (id not in custom list)
        $this->assertCount(1, $svg->getChildren());
    }
}
