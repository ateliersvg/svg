<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\SymbolBuilder;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Structural\SymbolLibrary;
use Atelier\Svg\Element\Structural\UseElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SymbolBuilder::class)]
final class SymbolBuilderTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        $this->document = Document::create(400, 300);
    }

    public function testCreateSymbol(): void
    {
        $symbol = SymbolBuilder::createSymbol($this->document, 'icon-home');

        $this->assertInstanceOf(SymbolElement::class, $symbol);
        $this->assertSame('icon-home', $symbol->getId());
    }

    public function testCreateSymbolWithViewBox(): void
    {
        $symbol = SymbolBuilder::createSymbol($this->document, 'icon', '0 0 24 24');

        $this->assertSame('0 0 24 24', $symbol->getAttribute('viewBox'));
    }

    public function testCreateSymbolWithoutViewBox(): void
    {
        $symbol = SymbolBuilder::createSymbol($this->document, 'icon-no-vb');

        $this->assertNull($symbol->getAttribute('viewBox'));
    }

    public function testCreateSymbolAddsToDefsSection(): void
    {
        SymbolBuilder::createSymbol($this->document, 'icon-test');

        $root = $this->document->getRootElement();
        $hasDefs = false;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                $hasDefs = true;
                break;
            }
        }
        $this->assertTrue($hasDefs);
    }

    public function testUseSymbol(): void
    {
        SymbolBuilder::createSymbol($this->document, 'icon-use');
        $use = SymbolBuilder::useSymbol($this->document, 'icon-use', 10, 20);

        $this->assertInstanceOf(UseElement::class, $use);
        $this->assertSame('#icon-use', $use->getAttribute('href'));
        $this->assertSame('10', $use->getAttribute('x'));
        $this->assertSame('20', $use->getAttribute('y'));
    }

    public function testUseSymbolWithDimensions(): void
    {
        SymbolBuilder::createSymbol($this->document, 'icon-dim');
        $use = SymbolBuilder::useSymbol($this->document, 'icon-dim', 0, 0, 24.0, 24.0);

        $this->assertSame('24', $use->getAttribute('width'));
        $this->assertSame('24', $use->getAttribute('height'));
    }

    public function testUseSymbolWithoutDimensions(): void
    {
        $use = SymbolBuilder::useSymbol($this->document, 'icon-no-dim');

        $this->assertNull($use->getAttribute('width'));
        $this->assertNull($use->getAttribute('height'));
    }

    public function testSymbolExists(): void
    {
        SymbolBuilder::createSymbol($this->document, 'icon-exists');

        $this->assertTrue(SymbolBuilder::symbolExists($this->document, 'icon-exists'));
        $this->assertFalse(SymbolBuilder::symbolExists($this->document, 'icon-nope'));
    }

    public function testGetSymbol(): void
    {
        SymbolBuilder::createSymbol($this->document, 'icon-get');

        $found = SymbolBuilder::getSymbol($this->document, 'icon-get');
        $this->assertInstanceOf(SymbolElement::class, $found);

        $notFound = SymbolBuilder::getSymbol($this->document, 'nonexistent');
        $this->assertNull($notFound);
    }

    public function testImportLibrary(): void
    {
        $library = new SymbolLibrary();
        $symbol1 = new SymbolElement();
        $symbol1->setId('lib-icon-1');
        $symbol2 = new SymbolElement();
        $symbol2->setId('lib-icon-2');

        $library->add('lib-icon-1', $symbol1);
        $library->add('lib-icon-2', $symbol2);

        SymbolBuilder::importLibrary($this->document, $library);

        $root = $this->document->getRootElement();
        $defs = null;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                $defs = $child;
                break;
            }
        }
        $this->assertNotNull($defs);
        $this->assertCount(2, $defs->getChildren());
    }

    public function testImportLibraryWithDuplicateId(): void
    {
        // Register an ID first
        SymbolBuilder::createSymbol($this->document, 'dup-icon');

        // Import library with same ID - should not throw
        $library = new SymbolLibrary();
        $symbol = new SymbolElement();
        $symbol->setId('dup-icon');
        $library->add('dup-icon', $symbol);

        SymbolBuilder::importLibrary($this->document, $library);

        // Should complete without exception
        $this->assertTrue(true);
    }

    public function testCreateSymbolWithDuplicateIdDoesNotThrow(): void
    {
        SymbolBuilder::createSymbol($this->document, 'dup-sym');
        $symbol2 = SymbolBuilder::createSymbol($this->document, 'dup-sym');

        $this->assertInstanceOf(SymbolElement::class, $symbol2);
        $this->assertSame('dup-sym', $symbol2->getId());
    }

    public function testCreateSymbolThrowsWhenNoRootElement(): void
    {
        $doc = new Document();

        $this->expectException(\Atelier\Svg\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Document has no root element');

        SymbolBuilder::createSymbol($doc, 'no-root');
    }

    public function testCreateSymbolReusesExistingDefs(): void
    {
        SymbolBuilder::createSymbol($this->document, 'sym1');
        SymbolBuilder::createSymbol($this->document, 'sym2');

        $root = $this->document->getRootElement();
        $defsCount = 0;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                ++$defsCount;
            }
        }
        $this->assertSame(1, $defsCount);
    }
}
