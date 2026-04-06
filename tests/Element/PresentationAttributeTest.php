<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\LineElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractElement::class)]
final class PresentationAttributeTest extends TestCase
{
    public function testSetFill(): void
    {
        $element = new RectElement();
        $result = $element->setFill('red');

        $this->assertSame($element, $result);
        $this->assertSame('red', $element->getAttribute('fill'));
    }

    public function testSetFillWithHexColor(): void
    {
        $element = new RectElement();
        $element->setFill('#ff0000');

        $this->assertSame('#ff0000', $element->getAttribute('fill'));
    }

    public function testSetFillNone(): void
    {
        $element = new RectElement();
        $element->setFill('none');

        $this->assertSame('none', $element->getAttribute('fill'));
    }

    public function testSetStroke(): void
    {
        $element = new RectElement();
        $result = $element->setStroke('black');

        $this->assertSame($element, $result);
        $this->assertSame('black', $element->getAttribute('stroke'));
    }

    public function testSetStrokeWithHexColor(): void
    {
        $element = new RectElement();
        $element->setStroke('#000000');

        $this->assertSame('#000000', $element->getAttribute('stroke'));
    }

    public function testSetStrokeWidthWithString(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeWidth('2px');

        $this->assertSame($element, $result);
        $this->assertSame('2px', $element->getAttribute('stroke-width'));
    }

    public function testSetStrokeWidthWithInt(): void
    {
        $element = new RectElement();
        $element->setStrokeWidth(3);

        $this->assertSame('3', $element->getAttribute('stroke-width'));
    }

    public function testSetStrokeWidthWithFloat(): void
    {
        $element = new RectElement();
        $element->setStrokeWidth(1.5);

        $this->assertSame('1.5', $element->getAttribute('stroke-width'));
    }

    public function testSetDisplay(): void
    {
        $element = new RectElement();
        $result = $element->setDisplay('none');

        $this->assertSame($element, $result);
        $this->assertSame('none', $element->getAttribute('display'));
    }

    public function testSetDisplayInline(): void
    {
        $element = new RectElement();
        $element->setDisplay('inline');

        $this->assertSame('inline', $element->getAttribute('display'));
    }

    public function testSetVisibility(): void
    {
        $element = new RectElement();
        $result = $element->setVisibility('hidden');

        $this->assertSame($element, $result);
        $this->assertSame('hidden', $element->getAttribute('visibility'));
    }

    public function testSetVisibilityVisible(): void
    {
        $element = new RectElement();
        $element->setVisibility('visible');

        $this->assertSame('visible', $element->getAttribute('visibility'));
    }

    public function testChainingMultipleSetters(): void
    {
        $element = new RectElement();
        $result = $element
            ->setFill('blue')
            ->setStroke('red')
            ->setStrokeWidth(2)
            ->setDisplay('inline')
            ->setVisibility('visible');

        $this->assertSame($element, $result);
        $this->assertSame('blue', $element->getAttribute('fill'));
        $this->assertSame('red', $element->getAttribute('stroke'));
        $this->assertSame('2', $element->getAttribute('stroke-width'));
        $this->assertSame('inline', $element->getAttribute('display'));
        $this->assertSame('visible', $element->getAttribute('visibility'));
    }

    public function testSettersOnCircleElement(): void
    {
        $element = new CircleElement();
        $result = $element
            ->setFill('#00ff00')
            ->setStroke('#0000ff')
            ->setStrokeWidth(1.5)
            ->setVisibility('visible');

        $this->assertSame($element, $result);
        $this->assertSame('#00ff00', $element->getAttribute('fill'));
        $this->assertSame('#0000ff', $element->getAttribute('stroke'));
        $this->assertSame('1.5', $element->getAttribute('stroke-width'));
        $this->assertSame('visible', $element->getAttribute('visibility'));
    }

    public function testSettersOnGroupElement(): void
    {
        $element = new GroupElement();
        $result = $element
            ->setFill('none')
            ->setStroke('none')
            ->setDisplay('block')
            ->setVisibility('collapse');

        $this->assertSame($element, $result);
        $this->assertSame('none', $element->getAttribute('fill'));
        $this->assertSame('none', $element->getAttribute('stroke'));
        $this->assertSame('block', $element->getAttribute('display'));
        $this->assertSame('collapse', $element->getAttribute('visibility'));
    }

    public function testSetFillOpacity(): void
    {
        $element = new RectElement();
        $result = $element->setFillOpacity(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getAttribute('fill-opacity'));
    }

    public function testSetFillOpacityFull(): void
    {
        $element = new RectElement();
        $element->setFillOpacity(1.0);

        $this->assertSame('1', $element->getAttribute('fill-opacity'));
    }

    public function testSetStrokeOpacity(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeOpacity(0.3);

        $this->assertSame($element, $result);
        $this->assertSame('0.3', $element->getAttribute('stroke-opacity'));
    }

    public function testSetStrokeOpacityZero(): void
    {
        $element = new RectElement();
        $element->setStrokeOpacity(0.0);

        $this->assertSame('0', $element->getAttribute('stroke-opacity'));
    }

    public function testSetPointerEvents(): void
    {
        $element = new RectElement();
        $result = $element->setPointerEvents('none');

        $this->assertSame($element, $result);
        $this->assertSame('none', $element->getAttribute('pointer-events'));
    }

    public function testSetPointerEventsAll(): void
    {
        $element = new RectElement();
        $element->setPointerEvents('all');

        $this->assertSame('all', $element->getAttribute('pointer-events'));
    }

    public function testSetCursor(): void
    {
        $element = new RectElement();
        $result = $element->setCursor('pointer');

        $this->assertSame($element, $result);
        $this->assertSame('pointer', $element->getAttribute('cursor'));
    }

    public function testSetCursorCrosshair(): void
    {
        $element = new RectElement();
        $element->setCursor('crosshair');

        $this->assertSame('crosshair', $element->getAttribute('cursor'));
    }

    public function testSetClipPath(): void
    {
        $element = new RectElement();
        $result = $element->setClipPath('myClip');

        $this->assertSame($element, $result);
        $this->assertSame('url(#myClip)', $element->getAttribute('clip-path'));
    }

    public function testSetClipPathWrapsWithUrl(): void
    {
        $element = new CircleElement();
        $element->setClipPath('circle-clip');

        $this->assertSame('url(#circle-clip)', $element->getAttribute('clip-path'));
    }

    public function testSetMask(): void
    {
        $element = new RectElement();
        $result = $element->setMask('myMask');

        $this->assertSame($element, $result);
        $this->assertSame('url(#myMask)', $element->getAttribute('mask'));
    }

    public function testSetMaskWrapsWithUrl(): void
    {
        $element = new CircleElement();
        $element->setMask('alpha-mask');

        $this->assertSame('url(#alpha-mask)', $element->getAttribute('mask'));
    }

    public function testSetStrokeLinecap(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeLinecap('round');

        $this->assertSame($element, $result);
        $this->assertSame('round', $element->getAttribute('stroke-linecap'));
    }

    public function testSetStrokeLinecapSquare(): void
    {
        $element = new RectElement();
        $element->setStrokeLinecap('square');

        $this->assertSame('square', $element->getAttribute('stroke-linecap'));
    }

    public function testSetStrokeLinejoin(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeLinejoin('bevel');

        $this->assertSame($element, $result);
        $this->assertSame('bevel', $element->getAttribute('stroke-linejoin'));
    }

    public function testSetStrokeLinejoinRound(): void
    {
        $element = new RectElement();
        $element->setStrokeLinejoin('round');

        $this->assertSame('round', $element->getAttribute('stroke-linejoin'));
    }

    public function testSetStrokeDasharray(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeDasharray('5,10');

        $this->assertSame($element, $result);
        $this->assertSame('5,10', $element->getAttribute('stroke-dasharray'));
    }

    public function testSetStrokeDasharrayComplex(): void
    {
        $element = new RectElement();
        $element->setStrokeDasharray('3 5 2 7');

        $this->assertSame('3 5 2 7', $element->getAttribute('stroke-dasharray'));
    }

    public function testSetStrokeDashoffsetWithString(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeDashoffset('10px');

        $this->assertSame($element, $result);
        $this->assertSame('10px', $element->getAttribute('stroke-dashoffset'));
    }

    public function testSetStrokeDashoffsetWithInt(): void
    {
        $element = new RectElement();
        $element->setStrokeDashoffset(5);

        $this->assertSame('5', $element->getAttribute('stroke-dashoffset'));
    }

    public function testSetStrokeDashoffsetWithFloat(): void
    {
        $element = new RectElement();
        $element->setStrokeDashoffset(2.5);

        $this->assertSame('2.5', $element->getAttribute('stroke-dashoffset'));
    }

    public function testSetStrokeMiterlimitWithString(): void
    {
        $element = new RectElement();
        $result = $element->setStrokeMiterlimit('8');

        $this->assertSame($element, $result);
        $this->assertSame('8', $element->getAttribute('stroke-miterlimit'));
    }

    public function testSetStrokeMiterlimitWithInt(): void
    {
        $element = new RectElement();
        $element->setStrokeMiterlimit(4);

        $this->assertSame('4', $element->getAttribute('stroke-miterlimit'));
    }

    public function testSetStrokeMiterlimitWithFloat(): void
    {
        $element = new RectElement();
        $element->setStrokeMiterlimit(3.5);

        $this->assertSame('3.5', $element->getAttribute('stroke-miterlimit'));
    }

    public function testSetFillRule(): void
    {
        $element = new RectElement();
        $result = $element->setFillRule('evenodd');

        $this->assertSame($element, $result);
        $this->assertSame('evenodd', $element->getAttribute('fill-rule'));
    }

    public function testSetFillRuleNonzero(): void
    {
        $element = new RectElement();
        $element->setFillRule('nonzero');

        $this->assertSame('nonzero', $element->getAttribute('fill-rule'));
    }

    public function testChainingStrokeDetailSetters(): void
    {
        $element = new RectElement();
        $result = $element
            ->setStroke('black')
            ->setStrokeWidth(2)
            ->setStrokeLinecap('round')
            ->setStrokeLinejoin('bevel')
            ->setStrokeDasharray('5,10')
            ->setStrokeDashoffset(3)
            ->setStrokeMiterlimit(8)
            ->setFillRule('evenodd');

        $this->assertSame($element, $result);
        $this->assertSame('black', $element->getAttribute('stroke'));
        $this->assertSame('2', $element->getAttribute('stroke-width'));
        $this->assertSame('round', $element->getAttribute('stroke-linecap'));
        $this->assertSame('bevel', $element->getAttribute('stroke-linejoin'));
        $this->assertSame('5,10', $element->getAttribute('stroke-dasharray'));
        $this->assertSame('3', $element->getAttribute('stroke-dashoffset'));
        $this->assertSame('8', $element->getAttribute('stroke-miterlimit'));
        $this->assertSame('evenodd', $element->getAttribute('fill-rule'));
    }

    public function testChainingNewSetters(): void
    {
        $element = new RectElement();
        $result = $element
            ->setFillOpacity(0.8)
            ->setStrokeOpacity(0.6)
            ->setPointerEvents('none')
            ->setCursor('pointer')
            ->setClipPath('clip1')
            ->setMask('mask1');

        $this->assertSame($element, $result);
        $this->assertSame('0.8', $element->getAttribute('fill-opacity'));
        $this->assertSame('0.6', $element->getAttribute('stroke-opacity'));
        $this->assertSame('none', $element->getAttribute('pointer-events'));
        $this->assertSame('pointer', $element->getAttribute('cursor'));
        $this->assertSame('url(#clip1)', $element->getAttribute('clip-path'));
        $this->assertSame('url(#mask1)', $element->getAttribute('mask'));
    }

    public function testSetMarkerStart(): void
    {
        $element = new RectElement();
        $result = $element->setMarkerStart('arrowhead');

        $this->assertSame($element, $result);
        $this->assertSame('url(#arrowhead)', $element->getAttribute('marker-start'));
    }

    public function testSetMarkerMid(): void
    {
        $element = new RectElement();
        $result = $element->setMarkerMid('dot');

        $this->assertSame($element, $result);
        $this->assertSame('url(#dot)', $element->getAttribute('marker-mid'));
    }

    public function testSetMarkerEnd(): void
    {
        $element = new RectElement();
        $result = $element->setMarkerEnd('arrow');

        $this->assertSame($element, $result);
        $this->assertSame('url(#arrow)', $element->getAttribute('marker-end'));
    }

    public function testFillAlias(): void
    {
        $element = new RectElement();
        $result = $element->fill('red');

        $this->assertSame($element, $result);
        $this->assertSame('red', $element->getAttribute('fill'));
    }

    public function testStrokeAlias(): void
    {
        $element = new RectElement();
        $result = $element->stroke('#000');

        $this->assertSame($element, $result);
        $this->assertSame('#000', $element->getAttribute('stroke'));
    }

    public function testStrokeWidthAlias(): void
    {
        $element = new RectElement();
        $result = $element->strokeWidth(2);

        $this->assertSame($element, $result);
        $this->assertSame('2', $element->getAttribute('stroke-width'));
    }

    public function testOpacityAlias(): void
    {
        $element = new RectElement();
        $result = $element->opacity(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getAttribute('opacity'));
    }

    public function testAliasChainingWithMixedMethods(): void
    {
        $element = new RectElement();
        $result = $element
            ->fill('red')
            ->stroke('#000')
            ->strokeWidth(2)
            ->opacity(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('red', $element->getAttribute('fill'));
        $this->assertSame('#000', $element->getAttribute('stroke'));
        $this->assertSame('2', $element->getAttribute('stroke-width'));
        $this->assertSame('0.5', $element->getAttribute('opacity'));
    }

    public function testAliasReturnTypeIsStatic(): void
    {
        $element = new CircleElement();

        $this->assertInstanceOf(CircleElement::class, $element->fill('blue'));
        $this->assertInstanceOf(CircleElement::class, $element->stroke('red'));
        $this->assertInstanceOf(CircleElement::class, $element->strokeWidth(1));
        $this->assertInstanceOf(CircleElement::class, $element->opacity(1.0));
    }

    public function testMarkerSettersOnLineElement(): void
    {
        $element = new LineElement();
        $result = $element
            ->setMarkerStart('start-arrow')
            ->setMarkerMid('midpoint')
            ->setMarkerEnd('end-arrow');

        $this->assertSame($element, $result);
        $this->assertSame('url(#start-arrow)', $element->getAttribute('marker-start'));
        $this->assertSame('url(#midpoint)', $element->getAttribute('marker-mid'));
        $this->assertSame('url(#end-arrow)', $element->getAttribute('marker-end'));
    }
}
