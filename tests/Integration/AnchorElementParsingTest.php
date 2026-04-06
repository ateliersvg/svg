<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Element\Hyperlinking\AnchorElement;
use Atelier\Svg\Loader\DomLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnchorElement::class)]
final class AnchorElementParsingTest extends TestCase
{
    private DomLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new DomLoader();
    }

    public function testParsesAnchorElement(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="https://example.com"><rect width="100" height="100"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        $root = $document->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertInstanceOf(AnchorElement::class, $children[0]);
    }

    public function testParsedAnchorHasHrefAttribute(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="https://example.com"><rect width="100" height="100"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        $anchor = $document->getRootElement()->getChildren()[0];

        $this->assertSame('https://example.com', $anchor->getAttribute('href'));
    }

    public function testParsedAnchorContainsChildren(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="#"><circle cx="50" cy="50" r="40"/><rect width="10" height="10"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        $anchor = $document->getRootElement()->getChildren()[0];

        $this->assertInstanceOf(AnchorElement::class, $anchor);
        $this->assertCount(2, $anchor->getChildren());
    }

    public function testParsedAnchorWithTargetAttribute(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><a href="https://example.com" target="_blank"><rect width="100" height="100"/></a></svg>';

        $document = $this->loader->loadFromString($svg);
        $anchor = $document->getRootElement()->getChildren()[0];

        $this->assertSame('_blank', $anchor->getAttribute('target'));
    }

    public function testNestedAnchorInGroup(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><g><a href="#link"><circle cx="10" cy="10" r="5"/></a></g></svg>';

        $document = $this->loader->loadFromString($svg);
        $group = $document->getRootElement()->getChildren()[0];
        $anchor = $group->getChildren()[0];

        $this->assertInstanceOf(AnchorElement::class, $anchor);
        $this->assertCount(1, $anchor->getChildren());
    }
}
