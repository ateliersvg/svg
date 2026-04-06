<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TspanElement::class)]
final class TspanElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $tspan = new TspanElement();

        $this->assertSame('tspan', $tspan->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setX(100);

        $this->assertSame($tspan, $result, 'setX should return self for chaining');
        $this->assertSame('100', $tspan->getAttribute('x'));

        $x = $tspan->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(100.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $tspan = new TspanElement();
        $tspan->setX('50px');

        $x = $tspan->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(50.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getX());
    }

    public function testSetAndGetY(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setY(200);

        $this->assertSame($tspan, $result, 'setY should return self for chaining');
        $this->assertSame('200', $tspan->getAttribute('y'));

        $y = $tspan->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(200.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $tspan = new TspanElement();
        $tspan->setY('75%');

        $y = $tspan->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(75.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getY());
    }

    public function testSetAndGetDx(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setDx(10);

        $this->assertSame($tspan, $result, 'setDx should return self for chaining');
        $this->assertSame('10', $tspan->getAttribute('dx'));
        $this->assertSame('10', $tspan->getDx());
    }

    public function testGetDxReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getDx());
    }

    public function testSetAndGetDy(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setDy(15);

        $this->assertSame($tspan, $result, 'setDy should return self for chaining');
        $this->assertSame('15', $tspan->getAttribute('dy'));
        $this->assertSame('15', $tspan->getDy());
    }

    public function testGetDyReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getDy());
    }

    public function testSetAndGetRotate(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setRotate('0 45 90');

        $this->assertSame($tspan, $result, 'setRotate should return self for chaining');
        $this->assertSame('0 45 90', $tspan->getAttribute('rotate'));
        $this->assertSame('0 45 90', $tspan->getRotate());
    }

    public function testGetRotateReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getRotate());
    }

    public function testSetAndGetTextLength(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setTextLength(500);

        $this->assertSame($tspan, $result, 'setTextLength should return self for chaining');
        $this->assertSame('500', $tspan->getAttribute('textLength'));

        $textLength = $tspan->getTextLength();
        $this->assertInstanceOf(Length::class, $textLength);
        $this->assertSame(500.0, $textLength->getValue());
    }

    public function testSetAndGetTextLengthWithString(): void
    {
        $tspan = new TspanElement();
        $tspan->setTextLength('300px');

        $textLength = $tspan->getTextLength();
        $this->assertInstanceOf(Length::class, $textLength);
        $this->assertSame(300.0, $textLength->getValue());
        $this->assertSame('px', $textLength->getUnit());
    }

    public function testGetTextLengthReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getTextLength());
    }

    public function testSetAndGetLengthAdjust(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setLengthAdjust('spacingAndGlyphs');

        $this->assertSame($tspan, $result, 'setLengthAdjust should return self for chaining');
        $this->assertSame('spacingAndGlyphs', $tspan->getAttribute('lengthAdjust'));
        $this->assertSame('spacingAndGlyphs', $tspan->getLengthAdjust());
    }

    public function testGetLengthAdjustReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getLengthAdjust());
    }

    public function testSetPosition(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setPosition(100, 200);

        $this->assertSame($tspan, $result, 'setPosition should return self for chaining');
        $this->assertSame('100', $tspan->getAttribute('x'));
        $this->assertSame('200', $tspan->getAttribute('y'));

        $x = $tspan->getX();
        $y = $tspan->getY();
        $this->assertSame(100.0, $x->getValue());
        $this->assertSame(200.0, $y->getValue());
    }

    public function testSetPositionWithStrings(): void
    {
        $tspan = new TspanElement();
        $tspan->setPosition('50px', '75px');

        $this->assertSame('50px', $tspan->getAttribute('x'));
        $this->assertSame('75px', $tspan->getAttribute('y'));
    }

    public function testMethodChaining(): void
    {
        $tspan = new TspanElement();
        $result = $tspan
            ->setX(100)
            ->setY(200)
            ->setDx(10)
            ->setDy(15)
            ->setRotate('45')
            ->setTextLength(500)
            ->setLengthAdjust('spacing');

        $this->assertSame($tspan, $result);
        $this->assertSame(100.0, $tspan->getX()->getValue());
        $this->assertSame(200.0, $tspan->getY()->getValue());
        $this->assertSame('10', $tspan->getDx());
        $this->assertSame('15', $tspan->getDy());
        $this->assertSame('45', $tspan->getRotate());
        $this->assertSame(500.0, $tspan->getTextLength()->getValue());
        $this->assertSame('spacing', $tspan->getLengthAdjust());
    }

    public function testCompleteTspanConfiguration(): void
    {
        $tspan = new TspanElement();
        $tspan
            ->setPosition('100px', '200px')
            ->setDx('5px')
            ->setDy('10px')
            ->setRotate('0 45 90')
            ->setTextLength('400px')
            ->setLengthAdjust('spacingAndGlyphs');

        $this->assertSame('100px', $tspan->getAttribute('x'));
        $this->assertSame('200px', $tspan->getAttribute('y'));
        $this->assertSame('5px', $tspan->getAttribute('dx'));
        $this->assertSame('10px', $tspan->getAttribute('dy'));
        $this->assertSame('0 45 90', $tspan->getAttribute('rotate'));
        $this->assertSame('400px', $tspan->getAttribute('textLength'));
        $this->assertSame('spacingAndGlyphs', $tspan->getAttribute('lengthAdjust'));

        $x = $tspan->getX();
        $y = $tspan->getY();
        $textLength = $tspan->getTextLength();

        $this->assertSame(100.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
        $this->assertSame(200.0, $y->getValue());
        $this->assertSame('px', $y->getUnit());
        $this->assertSame(400.0, $textLength->getValue());
        $this->assertSame('px', $textLength->getUnit());
    }

    public function testCanContainNestedTspans(): void
    {
        $parent = new TspanElement();
        $child = new TspanElement();

        $parent->appendChild($child);

        $this->assertTrue($parent->hasChildren());
        $this->assertSame(1, $parent->getChildCount());
        $this->assertSame([$child], $parent->getChildren());
        $this->assertSame($parent, $child->getParent());
    }

    public function testCanContainMultipleNestedTspans(): void
    {
        $parent = new TspanElement();
        $child1 = new TspanElement();
        $child2 = new TspanElement();

        $parent->appendChild($child1);
        $parent->appendChild($child2);

        $this->assertSame(2, $parent->getChildCount());
        $this->assertSame([$child1, $child2], $parent->getChildren());
    }

    public function testSetAndGetTextContent(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setTextContent('Hello World');

        $this->assertSame($tspan, $result, 'setTextContent should return self for chaining');
        $this->assertSame('Hello World', $tspan->getAttribute('textContent'));
        $this->assertSame('Hello World', $tspan->getTextContent());
    }

    public function testGetTextContentReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getTextContent());
    }

    public function testSetAndGetBaselineShift(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setBaselineShift('super');

        $this->assertSame($tspan, $result, 'setBaselineShift should return self for chaining');
        $this->assertSame('super', $tspan->getAttribute('baseline-shift'));
        $this->assertSame('super', $tspan->getBaselineShift());
    }

    public function testSetAndGetBaselineShiftWithNumeric(): void
    {
        $tspan = new TspanElement();
        $tspan->setBaselineShift(5);

        $this->assertSame('5', $tspan->getBaselineShift());
    }

    public function testSetAndGetBaselineShiftWithPercentage(): void
    {
        $tspan = new TspanElement();
        $tspan->setBaselineShift('50%');

        $this->assertSame('50%', $tspan->getBaselineShift());
    }

    public function testGetBaselineShiftReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getBaselineShift());
    }

    public function testSetAndGetLetterSpacing(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setLetterSpacing('2px');

        $this->assertSame($tspan, $result, 'setLetterSpacing should return self for chaining');
        $this->assertSame('2px', $tspan->getAttribute('letter-spacing'));
        $this->assertSame('2px', $tspan->getLetterSpacing());
    }

    public function testSetAndGetLetterSpacingWithNumeric(): void
    {
        $tspan = new TspanElement();
        $tspan->setLetterSpacing(3);

        $this->assertSame('3', $tspan->getLetterSpacing());
    }

    public function testGetLetterSpacingReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getLetterSpacing());
    }

    public function testSetAndGetWordSpacing(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setWordSpacing('5px');

        $this->assertSame($tspan, $result, 'setWordSpacing should return self for chaining');
        $this->assertSame('5px', $tspan->getAttribute('word-spacing'));
        $this->assertSame('5px', $tspan->getWordSpacing());
    }

    public function testSetAndGetWordSpacingWithFloat(): void
    {
        $tspan = new TspanElement();
        $tspan->setWordSpacing(0.5);

        $this->assertSame('0.5', $tspan->getWordSpacing());
    }

    public function testGetWordSpacingReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getWordSpacing());
    }

    public function testSetAndGetTextDecoration(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setTextDecoration('underline');

        $this->assertSame($tspan, $result, 'setTextDecoration should return self for chaining');
        $this->assertSame('underline', $tspan->getAttribute('text-decoration'));
        $this->assertSame('underline', $tspan->getTextDecoration());
    }

    public function testSetAndGetTextDecorationNone(): void
    {
        $tspan = new TspanElement();
        $tspan->setTextDecoration('none');

        $this->assertSame('none', $tspan->getTextDecoration());
    }

    public function testGetTextDecorationReturnsNullWhenNotSet(): void
    {
        $tspan = new TspanElement();

        $this->assertNull($tspan->getTextDecoration());
    }

    public function testSetDxWithStringValue(): void
    {
        $tspan = new TspanElement();
        $tspan->setDx('5px');

        $this->assertSame('5px', $tspan->getDx());
    }

    public function testSetDyWithStringValue(): void
    {
        $tspan = new TspanElement();
        $tspan->setDy('10px');

        $this->assertSame('10px', $tspan->getDy());
    }

    public function testSetRotateWithNumericValue(): void
    {
        $tspan = new TspanElement();
        $tspan->setRotate(45);

        $this->assertSame('45', $tspan->getRotate());
    }

    public function testFullChainWithAllAttributes(): void
    {
        $tspan = new TspanElement();
        $result = $tspan
            ->setPosition('10px', '20px')
            ->setDx('5px')
            ->setDy('10px')
            ->setRotate('0 45 90')
            ->setTextLength('300px')
            ->setLengthAdjust('spacingAndGlyphs')
            ->setTextContent('Styled text')
            ->setBaselineShift('super')
            ->setLetterSpacing('0.1em')
            ->setWordSpacing('0.2em')
            ->setTextDecoration('underline');

        $this->assertSame($tspan, $result);
        $this->assertSame(10.0, $tspan->getX()->getValue());
        $this->assertSame(20.0, $tspan->getY()->getValue());
        $this->assertSame('5px', $tspan->getDx());
        $this->assertSame('10px', $tspan->getDy());
        $this->assertSame('0 45 90', $tspan->getRotate());
        $this->assertSame(300.0, $tspan->getTextLength()->getValue());
        $this->assertSame('spacingAndGlyphs', $tspan->getLengthAdjust());
        $this->assertSame('Styled text', $tspan->getTextContent());
        $this->assertSame('super', $tspan->getBaselineShift());
        $this->assertSame('0.1em', $tspan->getLetterSpacing());
        $this->assertSame('0.2em', $tspan->getWordSpacing());
        $this->assertSame('underline', $tspan->getTextDecoration());
    }
}
