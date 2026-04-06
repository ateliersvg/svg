<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Structural\SymbolLibrary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymbolLibrary::class)]
final class SymbolLibraryTest extends TestCase
{
    public function testAddSymbolElementSetsId(): void
    {
        $library = new SymbolLibrary();
        $symbol = new SymbolElement();

        $library->add('my-symbol', $symbol);

        $this->assertSame('my-symbol', $symbol->getId());
        $this->assertTrue($library->has('my-symbol'));
        $this->assertSame($symbol, $library->get('my-symbol'));
    }

    public function testAddNonSymbolElementWrapsInSymbol(): void
    {
        $library = new SymbolLibrary();
        $rect = new RectElement();

        $library->add('rect-sym', $rect);

        $stored = $library->get('rect-sym');
        $this->assertInstanceOf(SymbolElement::class, $stored);
        $this->assertSame('rect-sym', $stored->getId());

        $children = $stored->getChildren();
        $this->assertCount(1, $children);
        $this->assertSame($rect, $children[0]);
    }

    public function testRemoveSymbol(): void
    {
        $library = new SymbolLibrary();
        $library->add('sym', new SymbolElement());

        $result = $library->remove('sym');

        $this->assertSame($library, $result);
        $this->assertFalse($library->has('sym'));
    }

    public function testGetIds(): void
    {
        $library = new SymbolLibrary();
        $library->add('a', new SymbolElement());
        $library->add('b', new SymbolElement());

        $ids = $library->getIds();

        $this->assertSame(['a', 'b'], $ids);
    }

    public function testClear(): void
    {
        $library = new SymbolLibrary();
        $library->add('x', new SymbolElement());

        $result = $library->clear();

        $this->assertSame($library, $result);
        $this->assertEmpty($library->getSymbols());
    }

    public function testMerge(): void
    {
        $lib1 = new SymbolLibrary();
        $lib1->add('a', new SymbolElement());

        $lib2 = new SymbolLibrary();
        $lib2->add('b', new SymbolElement());

        $result = $lib1->merge($lib2);

        $this->assertSame($lib1, $result);
        $this->assertTrue($lib1->has('a'));
        $this->assertTrue($lib1->has('b'));
    }

    public function testGetReturnsNullForMissing(): void
    {
        $library = new SymbolLibrary();

        $this->assertNull($library->get('nonexistent'));
    }
}
