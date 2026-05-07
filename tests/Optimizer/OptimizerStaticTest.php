<?php

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Optimizer\Optimizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Optimizer::class)]
final class OptimizerStaticTest extends TestCase
{
    public function testOptimizeWithDefaultPreset(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::forDocument($doc, 'default');

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testOptimizeWithAggressivePreset(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::forDocument($doc, 'aggressive');

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testOptimizeWithSafePreset(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::forDocument($doc, 'safe');

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testOptimizeWithAccessiblePreset(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::forDocument($doc, 'web');

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testRemoveMetadata(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::removeMetadata($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testCleanupDefs(): void
    {
        $doc = Document::create();

        $result = Optimizer::cleanupDefs($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testRoundValues(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.123456');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::roundValues($doc, 2);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testOptimizeColors(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('fill', '#ffffff');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::optimizeColors($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testInlineStyles(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: red');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::inlineStyles($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testExtractStyles(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('fill', 'red');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::extractStyles($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testSimplifyPaths(): void
    {
        $doc = Document::create();

        $result = Optimizer::simplifyPaths($doc, 0.5);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testRemoveHidden(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('display', 'none');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::removeHidden($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testMergePaths(): void
    {
        $doc = Document::create();

        $result = Optimizer::mergePaths($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testCollapseGroups(): void
    {
        $doc = Document::create();

        $result = Optimizer::collapseGroups($doc);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testCleanupIds(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('id', 'test-id');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::cleanupIds($doc, false);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testCleanupIdsWithMinify(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $rect->setAttribute('id', 'very-long-id-name');
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::cleanupIds($doc, true);

        $this->assertInstanceOf(Document::class, $result);
    }

    public function testRemoveDefaults(): void
    {
        $doc = Document::create();
        $rect = new RectElement();
        $doc->getRoot()->appendChild($rect);

        $result = Optimizer::removeDefaults($doc);

        $this->assertInstanceOf(Document::class, $result);
    }
}
