<?php

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\MarkerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkerBuilder::class)]
final class MarkerBuilderTest extends TestCase
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

    public function testCreateMarker(): void
    {
        $helper = MarkerBuilder::create($this->getDoc(), 'test-marker');
        $marker = $helper->getMarker();

        $this->assertEquals('test-marker', $marker->getId());
        $this->assertEquals('marker', $marker->getTagName());
    }

    public function testSetSize(): void
    {
        $helper = MarkerBuilder::create($this->getDoc(), 'marker')
            ->size(10, 10);

        $marker = $helper->getMarker();
        $this->assertEquals('10', $marker->getAttribute('markerWidth'));
        $this->assertEquals('10', $marker->getAttribute('markerHeight'));
    }

    public function testSetRefPoint(): void
    {
        $helper = MarkerBuilder::create($this->getDoc(), 'marker')
            ->refPoint(5, 5);

        $marker = $helper->getMarker();
        $this->assertEquals('5', $marker->getAttribute('refX'));
        $this->assertEquals('5', $marker->getAttribute('refY'));
    }

    public function testAutoOrient(): void
    {
        $helper = MarkerBuilder::create($this->getDoc(), 'marker')
            ->autoOrient();

        $this->assertEquals('auto', $helper->getMarker()->getAttribute('orient'));
    }

    public function testCreateArrow(): void
    {
        $marker = MarkerBuilder::arrow($this->getDoc(), 'arrow', '#000', 10);

        $this->assertEquals('arrow', $marker->getId());
        $this->assertEquals('auto', $marker->getAttribute('orient'));
        $this->assertCount(1, iterator_to_array($marker->getChildren()));
    }

    public function testCreateCircle(): void
    {
        $marker = MarkerBuilder::circle($this->getDoc(), 'circle', '#ff0000', 5);

        $this->assertEquals('circle', $marker->getId());
        $children = iterator_to_array($marker->getChildren());
        $this->assertCount(1, $children);
        $this->assertEquals('circle', $children[0]->getTagName());
    }

    public function testCreateDot(): void
    {
        $marker = MarkerBuilder::dot($this->getDoc(), 'dot', '#00ff00', 3);

        $this->assertEquals('dot', $marker->getId());
        $this->assertCount(1, iterator_to_array($marker->getChildren()));
    }

    public function testCreateSquare(): void
    {
        $marker = MarkerBuilder::square($this->getDoc(), 'square', '#0000ff', 8);

        $this->assertEquals('square', $marker->getId());
        $children = iterator_to_array($marker->getChildren());
        $this->assertCount(1, $children);
        $this->assertEquals('polygon', $children[0]->getTagName());
    }

    public function testCreateDiamond(): void
    {
        $marker = MarkerBuilder::diamond($this->getDoc(), 'diamond', '#ff00ff', 6);

        $this->assertEquals('diamond', $marker->getId());
        $children = iterator_to_array($marker->getChildren());
        $this->assertCount(1, $children);
        $this->assertEquals('polygon', $children[0]->getTagName());
    }

    public function testMarkerAddedToDefs(): void
    {
        MarkerBuilder::arrow($this->getDoc(), 'arrow');

        $root = $this->getDoc()->getRootElement();
        $this->assertNotNull($root);

        $defs = null;
        foreach ($root->getChildren() as $child) {
            if ('defs' === $child->getTagName()) {
                $defs = $child;
                break;
            }
        }

        $this->assertNotNull($defs);
        $this->assertInstanceOf(\Atelier\Svg\Element\Structural\DefsElement::class, $defs);
        $this->assertCount(1, iterator_to_array($defs->getChildren()));
    }
}
