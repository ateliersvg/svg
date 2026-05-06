<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\CleanupAttributesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleanupAttributesPass::class)]
final class CleanupAttributesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new CleanupAttributesPass();

        $this->assertSame('cleanup-attributes', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new CleanupAttributesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testTrimLeadingWhitespace(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('id', '  myId');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('myId', $svg->getAttribute('id'));
    }

    public function testTrimTrailingWhitespace(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('id', 'myId  ');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('myId', $svg->getAttribute('id'));
    }

    public function testTrimBothEnds(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('id', '  myId  ');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('myId', $svg->getAttribute('id'));
    }

    public function testCleanupClassAttributeDuplicateSpaces(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('class', 'foo   bar    baz');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('foo bar baz', $svg->getAttribute('class'));
    }

    public function testCleanupClassAttributeLeadingTrailing(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('class', '  foo bar  ');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('foo bar', $svg->getAttribute('class'));
    }

    public function testCleanupClassAttributeTabsNewlines(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('class', "foo\t\nbar\r\nbaz");

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('foo bar baz', $svg->getAttribute('class'));
    }

    public function testCleanupPointsAttribute(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $polygon = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('polygon');
            }
        };
        $polygon->setAttribute('points', '10,20  30,40   50,60');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('10 20 30 40 50 60', $polygon->getAttribute('points'));
    }

    public function testCleanupPointsWithCommas(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $polygon = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('polygon');
            }
        };
        $polygon->setAttribute('points', '10,20,30,40,50,60');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        // Commas should be normalized to spaces
        $this->assertSame('10 20 30 40 50 60', $polygon->getAttribute('points'));
    }

    public function testCleanupPointsTrailingZeros(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $polygon = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('polygon');
            }
        };
        $polygon->setAttribute('points', '10.000 20.500 30.100');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('10 20.5 30.1', $polygon->getAttribute('points'));
    }

    public function testCleanupPathDataWhitespace(): void
    {
        $pass = new CleanupAttributesPass();
        $path = new PathElement();
        $path->setAttribute('d', 'M  10  20  L  30  40');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('M 10 20 L 30 40', $path->getAttribute('d'));
    }

    public function testCleanupPathDataTrailingZeros(): void
    {
        $pass = new CleanupAttributesPass();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10.000 20.500 L 30.100 40.0');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('M 10 20.5 L 30.1 40', $path->getAttribute('d'));
    }

    public function testCleanupPathDataCommandSpacing(): void
    {
        $pass = new CleanupAttributesPass();
        $path = new PathElement();
        $path->setAttribute('d', 'M10 20L30 40C50 60 70 80 90 100');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Commands should NOT have spaces injected -- path data is kept compact
        $this->assertSame('M10 20L30 40C50 60 70 80 90 100', $path->getAttribute('d'));
    }

    public function testCleanupTransformAttribute(): void
    {
        $pass = new CleanupAttributesPass();
        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10.000,  20.500)  rotate(45.0)');

        $svg = new SvgElement();
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('translate(10, 20.5) rotate(45)', $group->getAttribute('transform'));
    }

    public function testCleanupTransformMatrix(): void
    {
        $pass = new CleanupAttributesPass();
        $group = new GroupElement();
        $group->setAttribute('transform', 'matrix(1.000 0.000 0.000 1.000 0.000 0.000)');

        $svg = new SvgElement();
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Trailing zeros should be removed from matrix values
        $this->assertSame('matrix(1 0 0 1 0 0)', $group->getAttribute('transform'));
    }

    public function testCleanupViewBoxAttribute(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.000  10.500   100.000  200.250');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 10.5 100 200.25', $svg->getAttribute('viewBox'));
    }

    public function testCleanupViewBoxWithCommas(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0,10,100,200');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 10 100 200', $svg->getAttribute('viewBox'));
    }

    public function testRemoveTrailingZerosFromDecimals(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setAttribute('d', 'M 1.500 2.000 L 3.100 4.050');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // 2.000 -> 2, 4.050 -> 4.05
        $this->assertSame('M 1.5 2 L 3.1 4.05', $path->getAttribute('d'));
    }

    public function testDoNotRemoveZeroFromIntegers(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setAttribute('d', 'M 0 10 L 20 30');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Integer zeros should be preserved
        $this->assertSame('M 0 10 L 20 30', $path->getAttribute('d'));
    }

    public function testProcessNestedElements(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.000  10.000  100.000  200.000');

        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10.000, 20.000)');

        $path = new PathElement();
        $path->setAttribute('d', 'M 10.000 20.000');

        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // All levels should be cleaned
        $this->assertSame('0 10 100 200', $svg->getAttribute('viewBox'));
        $this->assertSame('translate(10, 20)', $group->getAttribute('transform'));
        $this->assertSame('M 10 20', $path->getAttribute('d'));
    }

    public function testPreserveNonSpecialAttributes(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('data-value', '  some  value  ');

        $document = new Document($svg);

        $pass->optimize($document);

        // Non-special attributes should only be trimmed
        $this->assertSame('some  value', $svg->getAttribute('data-value'));
    }

    public function testMultipleAttributeTypes(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.000 0.000 100.000 100.000');
        $svg->setAttribute('class', '  foo   bar  ');
        $svg->setAttribute('id', '  svg1  ');

        $path = new PathElement();
        $path->setAttribute('d', 'M10.000 20.000L30.000 40.000');
        $path->setAttribute('transform', 'translate(5.000, 10.000)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 0 100 100', $svg->getAttribute('viewBox'));
        $this->assertSame('foo bar', $svg->getAttribute('class'));
        $this->assertSame('svg1', $svg->getAttribute('id'));
        $this->assertSame('M10 20L30 40', $path->getAttribute('d'));
        $this->assertSame('translate(5, 10)', $path->getAttribute('transform'));
    }

    public function testRealWorldSvg(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.000  0.000  500.000  300.000');
        $svg->setAttribute('class', '  icon   svg-icon  ');

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $gradient->setAttribute('id', '  grad1  ');

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $group = new GroupElement();
        $group->setAttribute('transform', 'translate(10.000,  20.000)   scale(1.500)');

        $path = new PathElement();
        $path->setAttribute('d', 'M10.000 20.000L30.100 40.200C50.000 60.000 70.500 80.000 90.000 100.000Z');
        $path->setAttribute('class', '  primary   stroke  ');

        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 0 500 300', $svg->getAttribute('viewBox'));
        $this->assertSame('icon svg-icon', $svg->getAttribute('class'));
        $this->assertSame('grad1', $gradient->getAttribute('id'));
        $this->assertSame('translate(10, 20) scale(1.5)', $group->getAttribute('transform'));
        $this->assertSame('M10 20L30.1 40.2C50 60 70.5 80 90 100Z', $path->getAttribute('d'));
        $this->assertSame('primary stroke', $path->getAttribute('class'));
    }

    public function testComplexPathData(): void
    {
        $pass = new CleanupAttributesPass();
        $path = new PathElement();
        $path->setAttribute('d', 'M100.000,200.000 C150.000,250.000,200.000,250.000,250.000,200.000 L300.000,150.000 Z');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('M100 200 C150 250 200 250 250 200 L300 150 Z', $path->getAttribute('d'));
    }

    public function testLowercasePathCommands(): void
    {
        $pass = new CleanupAttributesPass();
        $path = new PathElement();
        $path->setAttribute('d', 'm10 20l30 40c50 60 70 80 90 100z');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Lowercase commands should be preserved as-is (no space injection)
        $this->assertSame('m10 20l30 40c50 60 70 80 90 100z', $path->getAttribute('d'));
    }

    public function testPreserveZeroInDecimal(): void
    {
        $pass = new CleanupAttributesPass();
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setAttribute('d', 'M 0.5 0.25 L 1.75 2.125');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Decimal values should be preserved correctly
        $this->assertSame('M 0.5 0.25 L 1.75 2.125', $path->getAttribute('d'));
    }
}
