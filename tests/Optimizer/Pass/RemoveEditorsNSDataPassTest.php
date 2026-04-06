<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveEditorsNSDataPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEditorsNSDataPass::class)]
final class RemoveEditorsNSDataPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveEditorsNSDataPass();

        $this->assertSame('remove-editors-ns-data', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveSketchAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:sketch', 'http://www.bohemiancoding.com/sketch/ns');

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('sketch:type', 'MSShapeGroup');
        $rect->setAttribute('fill', 'red'); // Should be preserved
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Sketch attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:sketch'));
        $this->assertFalse($rect->hasAttribute('sketch:type'));

        // Normal attributes should be preserved
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testRemoveInkscapeAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:inkscape', 'http://www.inkscape.org/namespaces/inkscape');
        $svg->setAttribute('xmlns:sodipodi', 'http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd');

        $rect = new RectElement();
        $rect->setAttribute('inkscape:label', 'Layer 1');
        $rect->setAttribute('sodipodi:nodetypes', 'cccc');
        $rect->setAttribute('fill', 'blue');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Inkscape/Sodipodi attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:inkscape'));
        $this->assertFalse($svg->hasAttribute('xmlns:sodipodi'));
        $this->assertFalse($rect->hasAttribute('inkscape:label'));
        $this->assertFalse($rect->hasAttribute('sodipodi:nodetypes'));

        // Normal attributes should be preserved
        $this->assertSame('blue', $rect->getAttribute('fill'));
    }

    public function testRemoveIllustratorAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:i', 'http://ns.adobe.com/AdobeIllustrator/10.0/');
        $svg->setAttribute('xmlns:x', 'http://ns.adobe.com/Extensibility/1.0/');

        $rect = new RectElement();
        $rect->setAttribute('i:extraneous', 'Self');
        $rect->setAttribute('fill', 'green');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Illustrator attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:i'));
        $this->assertFalse($svg->hasAttribute('xmlns:x'));
        $this->assertFalse($rect->hasAttribute('i:extraneous'));

        // Normal attributes should be preserved
        $this->assertSame('green', $rect->getAttribute('fill'));
    }

    public function testRemoveDataAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('data-name', 'Rectangle 1');
        $rect->setAttribute('data-tags', 'important');
        $rect->setAttribute('fill', 'yellow');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // data- attributes should be removed
        $this->assertFalse($rect->hasAttribute('data-name'));
        $this->assertFalse($rect->hasAttribute('data-tags'));

        // Normal attributes should be preserved
        $this->assertSame('yellow', $rect->getAttribute('fill'));
    }

    public function testPreserveStandardAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('id', 'my-rect');
        $rect->setAttribute('class', 'shape');
        $rect->setAttribute('fill', 'purple');
        $rect->setAttribute('stroke', 'black');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Standard namespaces should be preserved
        $this->assertTrue($svg->hasAttribute('xmlns'));
        $this->assertTrue($svg->hasAttribute('xmlns:xlink'));

        // All standard attributes should be preserved
        $this->assertSame('my-rect', $rect->getAttribute('id'));
        $this->assertSame('shape', $rect->getAttribute('class'));
        $this->assertSame('purple', $rect->getAttribute('fill'));
        $this->assertSame('black', $rect->getAttribute('stroke'));
    }

    public function testMultipleEditorAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:sketch', 'http://www.bohemiancoding.com/sketch/ns');
        $svg->setAttribute('xmlns:inkscape', 'http://www.inkscape.org/namespaces/inkscape');
        $svg->setAttribute('xmlns:i', 'http://ns.adobe.com/AdobeIllustrator/10.0/');

        $rect = new RectElement();
        $rect->setAttribute('sketch:type', 'MSShapeGroup');
        $rect->setAttribute('inkscape:label', 'Layer 1');
        $rect->setAttribute('i:extraneous', 'Self');
        $rect->setAttribute('data-name', 'My Shape');
        $rect->setAttribute('fill', 'orange');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // All editor attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:sketch'));
        $this->assertFalse($svg->hasAttribute('xmlns:inkscape'));
        $this->assertFalse($svg->hasAttribute('xmlns:i'));
        $this->assertFalse($rect->hasAttribute('sketch:type'));
        $this->assertFalse($rect->hasAttribute('inkscape:label'));
        $this->assertFalse($rect->hasAttribute('i:extraneous'));
        $this->assertFalse($rect->hasAttribute('data-name'));

        // Normal attributes should be preserved
        $this->assertSame('orange', $rect->getAttribute('fill'));
    }

    public function testNestedElements(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:sketch', 'http://www.bohemiancoding.com/sketch/ns');

        $group = new GroupElement();
        $group->setAttribute('sketch:type', 'MSLayerGroup');

        $rect = new RectElement();
        $rect->setAttribute('sketch:type', 'MSShapeGroup');
        $rect->setAttribute('fill', 'cyan');
        $group->appendChild($rect);

        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        // Editor attributes should be removed from all levels
        $this->assertFalse($svg->hasAttribute('xmlns:sketch'));
        $this->assertFalse($group->hasAttribute('sketch:type'));
        $this->assertFalse($rect->hasAttribute('sketch:type'));

        // Normal attributes should be preserved
        $this->assertSame('cyan', $rect->getAttribute('fill'));
    }

    public function testNoEditorAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('fill', 'magenta');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Nothing should change
        $this->assertSame('magenta', $rect->getAttribute('fill'));
        $this->assertCount(1, $svg->getChildren());
    }

    public function testCorelDrawAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:coreldraw', 'http://www.corel.com/coreldraw/ns');

        $rect = new RectElement();
        $rect->setAttribute('coreldraw:export', 'yes');
        $rect->setAttribute('corel-id', '12345');
        $rect->setAttribute('fill', 'teal');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // CorelDRAW attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:coreldraw'));
        $this->assertFalse($rect->hasAttribute('coreldraw:export'));
        $this->assertFalse($rect->hasAttribute('corel-id'));

        // Normal attributes should be preserved
        $this->assertSame('teal', $rect->getAttribute('fill'));
    }

    public function testMicrosoftVisioAttributes(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:v', 'urn:schemas-microsoft-com:vml');
        $svg->setAttribute('xmlns:msvisio', 'urn:schemas-microsoft-com:office:visio');

        $rect = new RectElement();
        $rect->setAttribute('v:shapes', 'Shape_1');
        $rect->setAttribute('msvisio:PageID', '0');
        $rect->setAttribute('fill', 'navy');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Visio attributes should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:v'));
        $this->assertFalse($svg->hasAttribute('xmlns:msvisio'));
        $this->assertFalse($rect->hasAttribute('v:shapes'));
        $this->assertFalse($rect->hasAttribute('msvisio:PageID'));

        // Normal attributes should be preserved
        $this->assertSame('navy', $rect->getAttribute('fill'));
    }

    public function testRemoveMetadataElement(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();

        $metadata = new class extends \Atelier\Svg\Element\AbstractElement {
            public function __construct()
            {
                parent::__construct('metadata');
            }
        };
        $svg->appendChild($metadata);

        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // metadata element should be removed
        $children = $svg->getChildren();
        foreach ($children as $child) {
            $this->assertNotSame('metadata', $child->getTagName());
        }
    }

    public function testRemoveNamespacedElement(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();

        $inkElement = new class extends \Atelier\Svg\Element\AbstractElement {
            public function __construct()
            {
                parent::__construct('inkscape:perspective');
            }
        };
        $svg->appendChild($inkElement);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(0, $children);
    }

    public function testRemoveElementWithEditorNamespacePrefix(): void
    {
        $pass = new RemoveEditorsNSDataPass();
        $svg = new SvgElement();

        $serifEl = new class extends \Atelier\Svg\Element\AbstractElement {
            public function __construct()
            {
                parent::__construct('serif:customdata');
            }
        };
        $svg->appendChild($serifEl);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(0, $children);
    }
}
