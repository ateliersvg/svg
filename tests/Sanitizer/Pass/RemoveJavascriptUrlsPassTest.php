<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Sanitizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Hyperlinking\AnchorElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Sanitizer\Pass\RemoveJavascriptUrlsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveJavascriptUrlsPass::class)]
final class RemoveJavascriptUrlsPassTest extends TestCase
{
    private RemoveJavascriptUrlsPass $pass;

    protected function setUp(): void
    {
        $this->pass = new RemoveJavascriptUrlsPass();
    }

    public function testGetName(): void
    {
        $this->assertSame('remove-javascript-urls', $this->pass->getName());
    }

    public function testRemovesJavascriptHref(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', 'javascript:alert(1)');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($anchor->getAttribute('href'));
    }

    public function testRemovesJavascriptHrefWithWhitespace(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', '  javascript:alert(1)');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($anchor->getAttribute('href'));
    }

    public function testRemovesJavascriptHrefCaseInsensitive(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', 'JavaScript:alert(1)');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($anchor->getAttribute('href'));
    }

    public function testRemovesDataTextHtmlHref(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', 'data:text/html,<script>alert(1)</script>');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertNull($anchor->getAttribute('href'));
    }

    public function testKeepsSafeHref(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', 'https://example.com');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertSame('https://example.com', $anchor->getAttribute('href'));
    }

    public function testKeepsFragmentHref(): void
    {
        $document = new Document();
        $svg = new SvgElement();
        $anchor = new AnchorElement();
        $anchor->setAttribute('href', '#section');
        $svg->appendChild($anchor);
        $document->setRootElement($svg);

        $this->pass->sanitize($document);

        $this->assertSame('#section', $anchor->getAttribute('href'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();

        $this->pass->sanitize($document);

        $this->assertNull($document->getRootElement());
    }
}
