<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\EllipseElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertEllipseToCirclePass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertEllipseToCirclePass::class)]
final class ConvertEllipseToCirclePassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertEllipseToCirclePass();

        $this->assertSame('convert-ellipse-to-circle', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testConvertEqualRadiiEllipseToCircle(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25);
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should have a circle now
        $children = $svg->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        /** @var CircleElement $circle */
        $circle = $children[0];
        $this->assertSame('50', $circle->getAttribute('cx'));
        $this->assertSame('60', $circle->getAttribute('cy'));
        $this->assertSame('25', $circle->getAttribute('r'));
    }

    public function testPreserveEllipseWithDifferentRadii(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(30);
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should still be an ellipse
        $children = $svg->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testConvertWithinTolerance(): void
    {
        $pass = new ConvertEllipseToCirclePass(tolerance: 0.1);
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25.05); // Within 0.1 tolerance
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should be converted to circle
        $children = $svg->getChildren();
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testDoNotConvertOutsideTolerance(): void
    {
        $pass = new ConvertEllipseToCirclePass(tolerance: 0.01);
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25.05); // Outside 0.01 tolerance
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should still be an ellipse
        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testPreserveOtherAttributes(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25);
        $ellipse->setAttribute('fill', 'red');
        $ellipse->setAttribute('stroke', 'blue');
        $ellipse->setAttribute('id', 'my-shape');
        $ellipse->setAttribute('class', 'shape');
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        /** @var CircleElement $circle */
        $circle = $children[0];
        $this->assertSame('red', $circle->getAttribute('fill'));
        $this->assertSame('blue', $circle->getAttribute('stroke'));
        $this->assertSame('my-shape', $circle->getAttribute('id'));
        $this->assertSame('shape', $circle->getAttribute('class'));
    }

    public function testHandleMissingRadii(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60);
        // No rx or ry set
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should still be an ellipse (can't convert without radii)
        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testHandleZeroRadii(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(0)->setRy(0);
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not convert (invalid radii)
        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testHandleNegativeRadii(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(-5)->setRy(-5);
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not convert (invalid radii)
        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }

    public function testConvertMultipleEllipses(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        // Ellipse that should be converted
        $ellipse1 = new EllipseElement();
        $ellipse1->setCx(50)->setCy(60)->setRx(25)->setRy(25);
        $svg->appendChild($ellipse1);

        // Ellipse that should NOT be converted
        $ellipse2 = new EllipseElement();
        $ellipse2->setCx(100)->setCy(100)->setRx(30)->setRy(20);
        $svg->appendChild($ellipse2);

        // Another ellipse that should be converted
        $ellipse3 = new EllipseElement();
        $ellipse3->setCx(150)->setCy(150)->setRx(10)->setRy(10);
        $svg->appendChild($ellipse3);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertCount(3, $children);

        // First should be a circle
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        // Second should still be an ellipse
        $this->assertInstanceOf(EllipseElement::class, $children[1]);

        // Third should be a circle
        $this->assertInstanceOf(CircleElement::class, $children[2]);
    }

    public function testConvertNestedEllipses(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $group = new GroupElement();
        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25);
        $group->appendChild($ellipse);
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $group->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);
    }

    public function testDefaultCenterValues(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setRx(25)->setRy(25);
        // cx and cy not set, should default to 0
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        /** @var CircleElement $circle */
        $circle = $children[0];
        $this->assertSame('0', $circle->getAttribute('cx'));
        $this->assertSame('0', $circle->getAttribute('cy'));
    }

    public function testParseFloatWithScientificNotation(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60);
        $ellipse->setAttribute('rx', '2.5e1'); // 25 in scientific notation
        $ellipse->setAttribute('ry', '2.5e1');
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should be converted to circle
        $children = $svg->getChildren();
        $this->assertInstanceOf(CircleElement::class, $children[0]);

        /** @var CircleElement $circle */
        $circle = $children[0];
        $this->assertSame('25', $circle->getAttribute('r'));
    }

    public function testReplaceElementNotFoundInParent(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();
        $group = new GroupElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60)->setRx(25)->setRy(25);

        // Append to group but then remove before optimize
        $group->appendChild($ellipse);
        $svg->appendChild($group);
        $group->removeChild($ellipse);

        // Re-add to svg directly so it processes but parent is svg, not group
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // The ellipse should still be converted (it is found in its parent svg)
        $this->assertInstanceOf(CircleElement::class, $svg->getChildren()[1]);
    }

    public function testParseFloatReturnsNullForNonNumericValue(): void
    {
        $pass = new ConvertEllipseToCirclePass();
        $svg = new SvgElement();

        $ellipse = new EllipseElement();
        $ellipse->setCx(50)->setCy(60);
        $ellipse->setAttribute('rx', 'abc');
        $ellipse->setAttribute('ry', 'abc');
        $svg->appendChild($ellipse);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not be converted because rx/ry are not valid floats
        $children = $svg->getChildren();
        $this->assertInstanceOf(EllipseElement::class, $children[0]);
    }
}
