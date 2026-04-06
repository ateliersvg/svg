<?php

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\SymbolBuilder;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\SymbolLibrary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymbolBuilder::class)]
#[CoversClass(SymbolLibrary::class)]
final class SymbolBuilderTest extends TestCase
{
    private ?Document $doc = null;

    protected function setUp(): void
    {
        $this->doc = Document::create();
    }

    private function getDoc(): Document
    {
        $doc = $this->doc;
        $this->assertNotNull($doc);

        return $doc;
    }

    public function testCreateSymbol(): void
    {
        $symbol = SymbolBuilder::createSymbol($this->getDoc(), 'icon-test', '0 0 24 24');

        $this->assertEquals('icon-test', $symbol->getId());
        $this->assertEquals('symbol', $symbol->getTagName());
        $this->assertEquals('0 0 24 24', $symbol->getAttribute('viewBox'));
    }

    public function testCreateSymbolWithoutViewBox(): void
    {
        $symbol = SymbolBuilder::createSymbol($this->getDoc(), 'icon-test');

        $this->assertEquals('icon-test', $symbol->getId());
        $this->assertNull($symbol->getAttribute('viewBox'));
    }

    public function testUseSymbol(): void
    {
        SymbolBuilder::createSymbol($this->getDoc(), 'icon');
        $use = SymbolBuilder::useSymbol($this->getDoc(), 'icon', 10, 20);

        $this->assertEquals('use', $use->getTagName());
        $this->assertEquals('#icon', $use->getAttribute('href'));
        $this->assertEquals('10', $use->getAttribute('x'));
        $this->assertEquals('20', $use->getAttribute('y'));
    }

    public function testUseSymbolWithDimensions(): void
    {
        SymbolBuilder::createSymbol($this->getDoc(), 'icon');
        $use = SymbolBuilder::useSymbol($this->getDoc(), 'icon', 0, 0, 50, 50);

        $this->assertEquals('50', $use->getAttribute('width'));
        $this->assertEquals('50', $use->getAttribute('height'));
    }

    public function testSymbolExists(): void
    {
        SymbolBuilder::createSymbol($this->getDoc(), 'icon');

        $this->assertTrue(SymbolBuilder::symbolExists($this->getDoc(), 'icon'));
        $this->assertFalse(SymbolBuilder::symbolExists($this->getDoc(), 'nonexistent'));
    }

    public function testGetSymbol(): void
    {
        SymbolBuilder::createSymbol($this->getDoc(), 'icon');
        $symbol = SymbolBuilder::getSymbol($this->getDoc(), 'icon');

        $this->assertNotNull($symbol);
        $this->assertEquals('icon', $symbol->getId());
    }

    public function testSymbolLibraryAdd(): void
    {
        $library = new SymbolLibrary();
        $circle = new CircleElement();

        $library->add('circle-icon', $circle);

        $this->assertTrue($library->has('circle-icon'));
        $this->assertCount(1, $library->getSymbols());
    }

    public function testSymbolLibraryGet(): void
    {
        $library = new SymbolLibrary();
        $circle = new CircleElement();
        $library->add('circle-icon', $circle);

        $symbol = $library->get('circle-icon');

        $this->assertNotNull($symbol);
        $this->assertEquals('circle-icon', $symbol->getId());
    }

    public function testSymbolLibraryRemove(): void
    {
        $library = new SymbolLibrary();
        $circle = new CircleElement();
        $library->add('circle-icon', $circle);
        $library->remove('circle-icon');

        $this->assertFalse($library->has('circle-icon'));
    }

    public function testSymbolLibraryGetIds(): void
    {
        $library = new SymbolLibrary();
        $library->add('icon1', new CircleElement());
        $library->add('icon2', new CircleElement());

        $ids = $library->getIds();

        $this->assertCount(2, $ids);
        $this->assertContains('icon1', $ids);
        $this->assertContains('icon2', $ids);
    }

    public function testSymbolLibraryClear(): void
    {
        $library = new SymbolLibrary();
        $library->add('icon1', new CircleElement());
        $library->clear();

        $this->assertCount(0, $library->getSymbols());
    }

    public function testSymbolLibraryMerge(): void
    {
        $library1 = new SymbolLibrary();
        $library1->add('icon1', new CircleElement());

        $library2 = new SymbolLibrary();
        $library2->add('icon2', new CircleElement());

        $library1->merge($library2);

        $this->assertCount(2, $library1->getSymbols());
        $this->assertTrue($library1->has('icon1'));
        $this->assertTrue($library1->has('icon2'));
    }

    public function testImportLibrary(): void
    {
        $library = new SymbolLibrary();
        $library->add('icon1', new CircleElement());
        $library->add('icon2', new CircleElement());

        SymbolBuilder::importLibrary($this->getDoc(), $library);

        $this->assertTrue(SymbolBuilder::symbolExists($this->getDoc(), 'icon1'));
        $this->assertTrue(SymbolBuilder::symbolExists($this->getDoc(), 'icon2'));
    }
}
