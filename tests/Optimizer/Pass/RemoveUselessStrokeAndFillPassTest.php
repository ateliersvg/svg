<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveUselessStrokeAndFillPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveUselessStrokeAndFillPass::class)]
final class RemoveUselessStrokeAndFillPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();

        $this->assertSame('remove-useless-stroke-and-fill', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveStrokeAttributesWhenStrokeWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-linecap', 'round');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('stroke-width'));
        $this->assertFalse($path->hasAttribute('stroke-linecap'));
    }

    public function testPreserveStrokeWhenWidthNonZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '2');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('stroke'));
        $this->assertTrue($path->hasAttribute('stroke-width'));
        $this->assertSame('red', $path->getAttribute('stroke'));
        $this->assertSame('2', $path->getAttribute('stroke-width'));
    }

    public function testRemoveStrokeOpacityWhenStrokeWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-opacity', '0.5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-opacity'));
    }

    public function testRemoveStrokeDasharrayWhenStrokeWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-dasharray', '5,5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-dasharray'));
    }

    public function testRemoveFillOnLine(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $line = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $line->setAttribute('fill', 'red');
        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($line->hasAttribute('fill'));
    }

    public function testRemoveFillOpacityOnLine(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $line = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $line->setAttribute('fill', 'red');
        $line->setAttribute('fill-opacity', '0.5');
        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($line->hasAttribute('fill'));
        $this->assertFalse($line->hasAttribute('fill-opacity'));
    }

    public function testRemoveFillOnPolyline(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $polyline = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('polyline');
            }
        };
        $polyline->setAttribute('fill', 'blue');
        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($polyline->hasAttribute('fill'));
    }

    public function testPreserveFillOnRect(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'red');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertSame('red', $path->getAttribute('fill'));
    }

    public function testPreserveFillOnCircle(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'blue');
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path2->hasAttribute('fill'));
        $this->assertSame('blue', $path2->getAttribute('fill'));
    }

    public function testPreserveFillOnPath(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('fill', 'green');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('fill'));
        $this->assertSame('green', $path->getAttribute('fill'));
    }

    public function testHandleNestedElements(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('stroke-width'));
    }

    public function testStrokeWidthWithUnit(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0px');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('stroke-width'));
    }

    public function testStrokeWidthDecimalZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0.0');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke'));
        $this->assertFalse($path->hasAttribute('stroke-width'));
    }

    public function testPreserveStrokeLinejoin(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '2');
        $path->setAttribute('stroke-linejoin', 'round');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('stroke-linejoin'));
        $this->assertSame('round', $path->getAttribute('stroke-linejoin'));
    }

    public function testRemoveStrokeLinejoinWhenWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-linejoin', 'round');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-linejoin'));
    }

    public function testRemoveStrokeMiterlimitWhenWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-miterlimit', '10');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-miterlimit'));
    }

    public function testRemoveStrokeDashoffsetWhenWidthZero(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        $path->setAttribute('stroke-width', '0');
        $path->setAttribute('stroke-dashoffset', '5');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('stroke-dashoffset'));
    }

    public function testRemoveFillOnNoFillElement(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();

        $line = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $line->setAttribute('fill', 'red');

        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($line->hasAttribute('fill'));
    }

    public function testStrokeWidthNullReturnsFalse(): void
    {
        $pass = new RemoveUselessStrokeAndFillPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('stroke', 'red');
        // No stroke-width attribute set
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Stroke should be preserved since there is no stroke-width to check
        $this->assertTrue($path->hasAttribute('stroke'));
    }
}
