<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextElement::class)]
final class TextElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $text = new TextElement();

        $this->assertSame('text', $text->getTagName());
    }

    public function testSetAndGetX(): void
    {
        $text = new TextElement();
        $result = $text->setX(100);

        $this->assertSame($text, $result, 'setX should return self for chaining');
        $this->assertSame('100', $text->getAttribute('x'));

        $x = $text->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(100.0, $x->getValue());
    }

    public function testSetAndGetXWithString(): void
    {
        $text = new TextElement();
        $text->setX('50px');

        $x = $text->getX();
        $this->assertInstanceOf(Length::class, $x);
        $this->assertSame(50.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
    }

    public function testGetXReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getX());
    }

    public function testSetAndGetY(): void
    {
        $text = new TextElement();
        $result = $text->setY(200);

        $this->assertSame($text, $result, 'setY should return self for chaining');
        $this->assertSame('200', $text->getAttribute('y'));

        $y = $text->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(200.0, $y->getValue());
    }

    public function testSetAndGetYWithString(): void
    {
        $text = new TextElement();
        $text->setY('75%');

        $y = $text->getY();
        $this->assertInstanceOf(Length::class, $y);
        $this->assertSame(75.0, $y->getValue());
        $this->assertSame('%', $y->getUnit());
    }

    public function testGetYReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getY());
    }

    public function testSetAndGetDx(): void
    {
        $text = new TextElement();
        $result = $text->setDx(10);

        $this->assertSame($text, $result, 'setDx should return self for chaining');
        $this->assertSame('10', $text->getAttribute('dx'));
        $this->assertSame('10', $text->getDx());
    }

    public function testGetDxReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getDx());
    }

    public function testSetAndGetDy(): void
    {
        $text = new TextElement();
        $result = $text->setDy(15);

        $this->assertSame($text, $result, 'setDy should return self for chaining');
        $this->assertSame('15', $text->getAttribute('dy'));
        $this->assertSame('15', $text->getDy());
    }

    public function testGetDyReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getDy());
    }

    public function testSetAndGetRotate(): void
    {
        $text = new TextElement();
        $result = $text->setRotate('0 45 90');

        $this->assertSame($text, $result, 'setRotate should return self for chaining');
        $this->assertSame('0 45 90', $text->getAttribute('rotate'));
        $this->assertSame('0 45 90', $text->getRotate());
    }

    public function testGetRotateReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getRotate());
    }

    public function testSetAndGetTextLength(): void
    {
        $text = new TextElement();
        $result = $text->setTextLength(500);

        $this->assertSame($text, $result, 'setTextLength should return self for chaining');
        $this->assertSame('500', $text->getAttribute('textLength'));

        $textLength = $text->getTextLength();
        $this->assertInstanceOf(Length::class, $textLength);
        $this->assertSame(500.0, $textLength->getValue());
    }

    public function testSetAndGetTextLengthWithString(): void
    {
        $text = new TextElement();
        $text->setTextLength('300px');

        $textLength = $text->getTextLength();
        $this->assertInstanceOf(Length::class, $textLength);
        $this->assertSame(300.0, $textLength->getValue());
        $this->assertSame('px', $textLength->getUnit());
    }

    public function testGetTextLengthReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getTextLength());
    }

    public function testSetAndGetLengthAdjust(): void
    {
        $text = new TextElement();
        $result = $text->setLengthAdjust('spacingAndGlyphs');

        $this->assertSame($text, $result, 'setLengthAdjust should return self for chaining');
        $this->assertSame('spacingAndGlyphs', $text->getAttribute('lengthAdjust'));
        $this->assertSame('spacingAndGlyphs', $text->getLengthAdjust());
    }

    public function testGetLengthAdjustReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getLengthAdjust());
    }

    public function testSetPosition(): void
    {
        $text = new TextElement();
        $result = $text->setPosition(100, 200);

        $this->assertSame($text, $result, 'setPosition should return self for chaining');
        $this->assertSame('100', $text->getAttribute('x'));
        $this->assertSame('200', $text->getAttribute('y'));

        $x = $text->getX();
        $y = $text->getY();
        $this->assertSame(100.0, $x->getValue());
        $this->assertSame(200.0, $y->getValue());
    }

    public function testSetPositionWithStrings(): void
    {
        $text = new TextElement();
        $text->setPosition('50px', '75px');

        $this->assertSame('50px', $text->getAttribute('x'));
        $this->assertSame('75px', $text->getAttribute('y'));
    }

    public function testMethodChaining(): void
    {
        $text = new TextElement();
        $result = $text
            ->setX(100)
            ->setY(200)
            ->setDx(10)
            ->setDy(15)
            ->setRotate('45')
            ->setTextLength(500)
            ->setLengthAdjust('spacing');

        $this->assertSame($text, $result);
        $this->assertSame(100.0, $text->getX()->getValue());
        $this->assertSame(200.0, $text->getY()->getValue());
        $this->assertSame('10', $text->getDx());
        $this->assertSame('15', $text->getDy());
        $this->assertSame('45', $text->getRotate());
        $this->assertSame(500.0, $text->getTextLength()->getValue());
        $this->assertSame('spacing', $text->getLengthAdjust());
    }

    public function testCompleteTextConfiguration(): void
    {
        $text = new TextElement();
        $text
            ->setPosition('100px', '200px')
            ->setDx('5px')
            ->setDy('10px')
            ->setRotate('0 45 90')
            ->setTextLength('400px')
            ->setLengthAdjust('spacingAndGlyphs');

        $this->assertSame('100px', $text->getAttribute('x'));
        $this->assertSame('200px', $text->getAttribute('y'));
        $this->assertSame('5px', $text->getAttribute('dx'));
        $this->assertSame('10px', $text->getAttribute('dy'));
        $this->assertSame('0 45 90', $text->getAttribute('rotate'));
        $this->assertSame('400px', $text->getAttribute('textLength'));
        $this->assertSame('spacingAndGlyphs', $text->getAttribute('lengthAdjust'));

        $x = $text->getX();
        $y = $text->getY();
        $textLength = $text->getTextLength();

        $this->assertSame(100.0, $x->getValue());
        $this->assertSame('px', $x->getUnit());
        $this->assertSame(200.0, $y->getValue());
        $this->assertSame('px', $y->getUnit());
        $this->assertSame(400.0, $textLength->getValue());
        $this->assertSame('px', $textLength->getUnit());
    }

    public function testCanContainChildren(): void
    {
        $text = new TextElement();
        $tspan = new TspanElement();

        $text->appendChild($tspan);

        $this->assertTrue($text->hasChildren());
        $this->assertSame(1, $text->getChildCount());
        $this->assertSame([$tspan], $text->getChildren());
        $this->assertSame($text, $tspan->getParent());
    }

    public function testCanContainMultipleTspans(): void
    {
        $text = new TextElement();
        $tspan1 = new TspanElement();
        $tspan2 = new TspanElement();

        $text->appendChild($tspan1);
        $text->appendChild($tspan2);

        $this->assertSame(2, $text->getChildCount());
        $this->assertSame([$tspan1, $tspan2], $text->getChildren());
    }

    public function testSetAndGetTextContent(): void
    {
        $text = new TextElement();
        $result = $text->setTextContent('Hello World');

        $this->assertSame($text, $result, 'setTextContent should return self for chaining');
        $this->assertSame('Hello World', $text->getTextContent());
        $this->assertSame('Hello World', $text->getAttribute('textContent'));
    }

    public function testGetTextContentReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getTextContent());
    }

    public function testSetTextContentWithEmptyString(): void
    {
        $text = new TextElement();
        $text->setTextContent('');

        $this->assertSame('', $text->getTextContent());
    }

    public function testSetTextContentWithSpecialCharacters(): void
    {
        $text = new TextElement();
        $text->setTextContent('<Hello> & "World"');

        $this->assertSame('<Hello> & "World"', $text->getTextContent());
    }

    public function testSetAndGetBaselineShift(): void
    {
        $text = new TextElement();
        $result = $text->setBaselineShift('super');

        $this->assertSame($text, $result, 'setBaselineShift should return self for chaining');
        $this->assertSame('super', $text->getBaselineShift());
        $this->assertSame('super', $text->getAttribute('baseline-shift'));
    }

    public function testSetBaselineShiftWithNumericValue(): void
    {
        $text = new TextElement();
        $text->setBaselineShift(5);

        $this->assertSame('5', $text->getBaselineShift());
    }

    public function testGetBaselineShiftReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getBaselineShift());
    }

    public function testSetAndGetLetterSpacing(): void
    {
        $text = new TextElement();
        $result = $text->setLetterSpacing('2px');

        $this->assertSame($text, $result, 'setLetterSpacing should return self for chaining');
        $this->assertSame('2px', $text->getLetterSpacing());
        $this->assertSame('2px', $text->getAttribute('letter-spacing'));
    }

    public function testSetLetterSpacingWithNumericValue(): void
    {
        $text = new TextElement();
        $text->setLetterSpacing(2);

        $this->assertSame('2', $text->getLetterSpacing());
    }

    public function testGetLetterSpacingReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getLetterSpacing());
    }

    public function testSetAndGetWordSpacing(): void
    {
        $text = new TextElement();
        $result = $text->setWordSpacing('5px');

        $this->assertSame($text, $result, 'setWordSpacing should return self for chaining');
        $this->assertSame('5px', $text->getWordSpacing());
        $this->assertSame('5px', $text->getAttribute('word-spacing'));
    }

    public function testSetWordSpacingWithNumericValue(): void
    {
        $text = new TextElement();
        $text->setWordSpacing(5);

        $this->assertSame('5', $text->getWordSpacing());
    }

    public function testGetWordSpacingReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getWordSpacing());
    }

    public function testSetAndGetTextDecoration(): void
    {
        $text = new TextElement();
        $result = $text->setTextDecoration('underline');

        $this->assertSame($text, $result, 'setTextDecoration should return self for chaining');
        $this->assertSame('underline', $text->getTextDecoration());
        $this->assertSame('underline', $text->getAttribute('text-decoration'));
    }

    public function testSetTextDecorationWithMultipleValues(): void
    {
        $text = new TextElement();
        $text->setTextDecoration('underline overline');

        $this->assertSame('underline overline', $text->getTextDecoration());
    }

    public function testGetTextDecorationReturnsNullWhenNotSet(): void
    {
        $text = new TextElement();

        $this->assertNull($text->getTextDecoration());
    }
}
