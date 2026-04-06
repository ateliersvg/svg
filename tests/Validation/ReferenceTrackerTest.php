<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Validation\ReferenceTracker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceTracker::class)]
final class ReferenceTrackerTest extends TestCase
{
    public function testTracksElementsById(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $circle = new CircleElement();
        $circle->setAttribute('id', 'myCircle');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);

        $this->assertContains('myCircle', $tracker->getAllIds());
        $this->assertSame($circle, $tracker->getElementById('myCircle'));
    }

    public function testDetectsBrokenReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#missingGradient)');
        $root->appendChild($rect);

        $tracker = new ReferenceTracker($doc);
        $broken = $tracker->findBrokenReferences();

        $this->assertCount(1, $broken);
        $this->assertEquals('missingGradient', $broken[0]->referencedId);
        $this->assertSame($rect, $broken[0]->referencingElement);
    }

    public function testDetectsMultipleBrokenReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#missing1)');
        $rect->setAttribute('stroke', 'url(#missing2)');
        $rect->setAttribute('filter', 'url(#missing3)');
        $root->appendChild($rect);

        $tracker = new ReferenceTracker($doc);
        $broken = $tracker->findBrokenReferences();

        $this->assertCount(3, $broken);
    }

    public function testTracksValidReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad1');

        $stop = new StopElement();
        $stop->setAttribute('offset', '0%');
        $gradient->appendChild($stop);

        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $circle = new CircleElement();
        $circle->setAttribute('fill', 'url(#grad1)');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);
        $broken = $tracker->findBrokenReferences();

        $this->assertEmpty($broken);
        $this->assertTrue($tracker->isReferenced('grad1'));
    }

    public function testGetReferencesTo(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'myGrad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $circle1 = new CircleElement();
        $circle1->setAttribute('fill', 'url(#myGrad)');
        $root->appendChild($circle1);

        $circle2 = new CircleElement();
        $circle2->setAttribute('stroke', 'url(#myGrad)');
        $root->appendChild($circle2);

        $tracker = new ReferenceTracker($doc);
        $refs = $tracker->getReferencesTo('myGrad');

        $this->assertCount(2, $refs);
    }

    public function testDetectsDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'duplicate');
        $root->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'duplicate');
        $root->appendChild($rect2);

        $tracker = new ReferenceTracker($doc);
        $duplicates = $tracker->getDuplicateIds();

        $this->assertCount(1, $duplicates);
        $this->assertEquals(2, $duplicates['duplicate']);
    }

    public function testDetectsCircularReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();

        $grad1 = new LinearGradientElement();
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('href', '#grad2');

        $grad2 = new LinearGradientElement();
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('href', '#grad1');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $root->appendChild($defs);

        $tracker = new ReferenceTracker($doc);
        $circular = $tracker->findCircularReferences();

        $this->assertNotEmpty($circular);
    }

    public function testGetDependencies(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'myGrad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $circle = new CircleElement();
        $circle->setAttribute('id', 'myCircle');
        $circle->setAttribute('fill', 'url(#myGrad)');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);
        $deps = $tracker->getDependencies('myGrad');

        $this->assertContains('myCircle', $deps);
    }

    public function testGetDependsOn(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'myGrad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $circle = new CircleElement();
        $circle->setAttribute('id', 'myCircle');
        $circle->setAttribute('fill', 'url(#myGrad)');
        $circle->setAttribute('stroke', 'url(#anotherGrad)');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);
        $dependsOn = $tracker->getDependsOn('myCircle');

        $this->assertContains('myGrad', $dependsOn);
        $this->assertContains('anotherGrad', $dependsOn);
    }

    public function testGetUnreferencedIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();

        $grad1 = new LinearGradientElement();
        $grad1->setAttribute('id', 'usedGrad');
        $defs->appendChild($grad1);

        $grad2 = new LinearGradientElement();
        $grad2->setAttribute('id', 'unusedGrad');
        $defs->appendChild($grad2);

        $root->appendChild($defs);

        $circle = new CircleElement();
        $circle->setAttribute('fill', 'url(#usedGrad)');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);
        $unreferenced = $tracker->getUnreferencedIds();

        $this->assertContains('unusedGrad', $unreferenced);
        $this->assertNotContains('usedGrad', $unreferenced);
    }

    public function testHandlesHrefReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $symbol = new SymbolElement();
        $symbol->setAttribute('id', 'mySymbol');
        $defs->appendChild($symbol);
        $root->appendChild($defs);

        $use = new UseElement();
        $use->setAttribute('href', '#mySymbol');
        $root->appendChild($use);

        $tracker = new ReferenceTracker($doc);

        $this->assertTrue($tracker->isReferenced('mySymbol'));
        $this->assertEmpty($tracker->findBrokenReferences());
    }

    public function testHandlesXlinkHrefReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $symbol = new SymbolElement();
        $symbol->setAttribute('id', 'mySymbol');
        $defs->appendChild($symbol);
        $root->appendChild($defs);

        $use = new UseElement();
        $use->setAttribute('xlink:href', '#mySymbol');
        $root->appendChild($use);

        $tracker = new ReferenceTracker($doc);

        $this->assertTrue($tracker->isReferenced('mySymbol'));
    }

    public function testEmptyDocumentHandling(): void
    {
        $doc = new Document();

        $tracker = new ReferenceTracker($doc);

        $this->assertEmpty($tracker->getAllIds());
        $this->assertEmpty($tracker->findBrokenReferences());
        $this->assertEmpty($tracker->findCircularReferences());
    }

    public function testGetDependsOnReturnsEmptyForNonExistentId(): void
    {
        $doc = Document::create();

        $tracker = new ReferenceTracker($doc);
        $dependsOn = $tracker->getDependsOn('nonexistent');

        $this->assertSame([], $dependsOn);
    }

    public function testGetDependencyGraphReturnsGraph(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad1');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $circle = new CircleElement();
        $circle->setAttribute('id', 'circle1');
        $circle->setAttribute('fill', 'url(#grad1)');
        $root->appendChild($circle);

        $tracker = new ReferenceTracker($doc);
        $graph = $tracker->getDependencyGraph();

        $this->assertIsArray($graph);
        $this->assertArrayHasKey('grad1', $graph);
        $this->assertContains('circle1', $graph['grad1']);
    }
}
