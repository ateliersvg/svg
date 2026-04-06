<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Sanitizer;

use Atelier\Svg\Document;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\ScriptElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\ForeignObjectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Sanitizer\Pass\RemoveScriptElementsPass;
use Atelier\Svg\Sanitizer\Sanitizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sanitizer::class)]
final class SanitizerTest extends TestCase
{
    public function testDefaultPreset(): void
    {
        $sanitizer = Sanitizer::default();

        $this->assertCount(3, $sanitizer->getPasses());
    }

    public function testStrictPreset(): void
    {
        $sanitizer = Sanitizer::strict();

        $this->assertCount(4, $sanitizer->getPasses());
    }

    public function testPermissivePreset(): void
    {
        $sanitizer = Sanitizer::permissive();

        $this->assertCount(2, $sanitizer->getPasses());
    }

    public function testSanitizeRemovesScriptElements(): void
    {
        $document = $this->createDocumentWithScript();

        Sanitizer::default()->sanitize($document);

        $output = (new CompactXmlDumper())->dump($document);
        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringContainsString('<circle', $output);
    }

    public function testSanitizeRemovesEventHandlers(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setAttribute('onclick', 'alert("xss")');
        $circle->setAttribute('fill', 'red');
        $svg->appendChild($circle);
        $document->setRootElement($svg);

        Sanitizer::default()->sanitize($document);

        $this->assertNull($circle->getAttribute('onclick'));
        $this->assertSame('red', $circle->getAttribute('fill'));
    }

    public function testStrictRemovesForeignObject(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $foreignObject = new ForeignObjectElement();
        $rect = new RectElement();
        $svg->appendChild($foreignObject);
        $svg->appendChild($rect);
        $document->setRootElement($svg);

        Sanitizer::strict()->sanitize($document);

        $output = (new CompactXmlDumper())->dump($document);
        $this->assertStringNotContainsString('foreignObject', $output);
        $this->assertStringContainsString('<rect', $output);
    }

    public function testPermissiveKeepsEventHandlers(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setAttribute('onclick', 'doSomething()');
        $svg->appendChild($circle);
        $document->setRootElement($svg);

        Sanitizer::permissive()->sanitize($document);

        $this->assertSame('doSomething()', $circle->getAttribute('onclick'));
    }

    public function testCustomPasses(): void
    {
        $sanitizer = new Sanitizer([
            new RemoveScriptElementsPass(),
        ]);

        $this->assertCount(1, $sanitizer->getPasses());
    }

    public function testSanitizeWithEmptyDocument(): void
    {
        $document = new Document();

        Sanitizer::default()->sanitize($document);

        $this->assertNull($document->getRootElement());
    }

    private function createDocumentWithScript(): Document
    {
        $document = new Document();
        $svg = new SvgElement();

        $circle = new CircleElement();
        $circle->setRadius(50);

        $script = new ScriptElement();
        $script->setAttribute('textContent', 'alert("xss")');

        $svg->appendChild($circle);
        $svg->appendChild($script);
        $document->setRootElement($svg);

        return $document;
    }
}
