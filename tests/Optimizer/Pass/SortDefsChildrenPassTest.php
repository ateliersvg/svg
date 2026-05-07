<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\SortDefsChildrenPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SortDefsChildrenPass::class)]
final class SortDefsChildrenPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new SortDefsChildrenPass();
        $this->assertSame('sort-defs-children', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new SortDefsChildrenPass();
        $document = new Document();
        $pass->optimize($document);
        $this->assertNull($document->getRootElement());
    }

    public function testSortsByTagName(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $defs = new DefsElement();

        $symbol = new SymbolElement();
        $symbol->setAttribute('id', 'sym');
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad');

        // Add in reverse order: symbol before gradient
        $defs->appendChild($symbol);
        $defs->appendChild($gradient);
        $svg->appendChild($defs);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $defs->getChildren();
        // linearGradient < symbol alphabetically
        $this->assertSame('linearGradient', $children[0]->getTagName());
        $this->assertSame('symbol', $children[1]->getTagName());
    }

    public function testSortsByIdWithinSameTag(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $defs = new DefsElement();

        $grad2 = new LinearGradientElement();
        $grad2->setAttribute('id', 'z-gradient');
        $grad1 = new LinearGradientElement();
        $grad1->setAttribute('id', 'a-gradient');

        $defs->appendChild($grad2);
        $defs->appendChild($grad1);
        $svg->appendChild($defs);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $defs->getChildren();
        $this->assertSame('a-gradient', $children[0]->getAttribute('id'));
        $this->assertSame('z-gradient', $children[1]->getAttribute('id'));
    }

    public function testSkipsSingleChild(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $defs->appendChild($gradient);
        $svg->appendChild($defs);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $defs->getChildren());
    }

    public function testSkipsAlreadySortedDefs(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $defs = new DefsElement();

        $grad = new LinearGradientElement();
        $grad->setAttribute('id', 'a');
        $symbol = new SymbolElement();
        $symbol->setAttribute('id', 'b');

        // Already sorted: linearGradient < symbol
        $defs->appendChild($grad);
        $defs->appendChild($symbol);
        $svg->appendChild($defs);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $defs->getChildren();
        $this->assertSame('linearGradient', $children[0]->getTagName());
        $this->assertSame('symbol', $children[1]->getTagName());
    }

    public function testHandlesNestedDefs(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $defs = new DefsElement();

        $symbol = new SymbolElement();
        $gradient = new LinearGradientElement();
        $defs->appendChild($symbol);
        $defs->appendChild($gradient);

        $group->appendChild($defs);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $defs->getChildren();
        $this->assertSame('linearGradient', $children[0]->getTagName());
        $this->assertSame('symbol', $children[1]->getTagName());
    }

    public function testNoDefsElementIsNoop(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);
        $this->assertCount(1, $svg->getChildren());
    }

    public function testElementsWithoutIdSortedByTag(): void
    {
        $pass = new SortDefsChildrenPass();
        $svg = new SvgElement();
        $defs = new DefsElement();

        $radial = new RadialGradientElement();
        $linear = new LinearGradientElement();

        $defs->appendChild($radial);
        $defs->appendChild($linear);
        $svg->appendChild($defs);
        $document = new Document($svg);

        $pass->optimize($document);

        $children = $defs->getChildren();
        // linearGradient < radialGradient
        $this->assertSame('linearGradient', $children[0]->getTagName());
        $this->assertSame('radialGradient', $children[1]->getTagName());
    }
}
