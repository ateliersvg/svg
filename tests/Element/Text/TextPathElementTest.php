<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Text\TextPathElement;
use Atelier\Svg\Value\Length;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextPathElement::class)]
final class TextPathElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $textPath = new TextPathElement();

        $this->assertSame('textPath', $textPath->getTagName());
    }

    public function testSetAndGetHref(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setHref('#myPath');

        $this->assertSame($textPath, $result, 'setHref should return self for chaining');
        $this->assertSame('#myPath', $textPath->getAttribute('href'));
        $this->assertSame('#myPath', $textPath->getHref());
    }

    public function testGetHrefReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getHref());
    }

    public function testSetAndGetStartOffset(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setStartOffset(50);

        $this->assertSame($textPath, $result, 'setStartOffset should return self for chaining');
        $this->assertSame('50', $textPath->getAttribute('startOffset'));

        $startOffset = $textPath->getStartOffset();
        $this->assertInstanceOf(Length::class, $startOffset);
        $this->assertSame(50.0, $startOffset->getValue());
    }

    public function testSetAndGetStartOffsetWithString(): void
    {
        $textPath = new TextPathElement();
        $textPath->setStartOffset('25%');

        $startOffset = $textPath->getStartOffset();
        $this->assertInstanceOf(Length::class, $startOffset);
        $this->assertSame(25.0, $startOffset->getValue());
        $this->assertSame('%', $startOffset->getUnit());
    }

    public function testSetAndGetStartOffsetWithPixels(): void
    {
        $textPath = new TextPathElement();
        $textPath->setStartOffset('100px');

        $startOffset = $textPath->getStartOffset();
        $this->assertInstanceOf(Length::class, $startOffset);
        $this->assertSame(100.0, $startOffset->getValue());
        $this->assertSame('px', $startOffset->getUnit());
    }

    public function testGetStartOffsetReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getStartOffset());
    }

    public function testSetAndGetMethod(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setMethod('align');

        $this->assertSame($textPath, $result, 'setMethod should return self for chaining');
        $this->assertSame('align', $textPath->getAttribute('method'));
        $this->assertSame('align', $textPath->getMethod());
    }

    public function testSetAndGetMethodStretch(): void
    {
        $textPath = new TextPathElement();
        $textPath->setMethod('stretch');

        $this->assertSame('stretch', $textPath->getMethod());
    }

    public function testGetMethodReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getMethod());
    }

    public function testSetAndGetSpacing(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setSpacing('auto');

        $this->assertSame($textPath, $result, 'setSpacing should return self for chaining');
        $this->assertSame('auto', $textPath->getAttribute('spacing'));
        $this->assertSame('auto', $textPath->getSpacing());
    }

    public function testSetAndGetSpacingExact(): void
    {
        $textPath = new TextPathElement();
        $textPath->setSpacing('exact');

        $this->assertSame('exact', $textPath->getSpacing());
    }

    public function testGetSpacingReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getSpacing());
    }

    public function testMethodChaining(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath
            ->setHref('#path1')
            ->setStartOffset('25%')
            ->setMethod('align')
            ->setSpacing('auto');

        $this->assertSame($textPath, $result);
        $this->assertSame('#path1', $textPath->getHref());
        $this->assertSame(25.0, $textPath->getStartOffset()->getValue());
        $this->assertSame('%', $textPath->getStartOffset()->getUnit());
        $this->assertSame('align', $textPath->getMethod());
        $this->assertSame('auto', $textPath->getSpacing());
    }

    public function testCompleteTextPathConfiguration(): void
    {
        $textPath = new TextPathElement();
        $textPath
            ->setHref('#curvePath')
            ->setStartOffset('10%')
            ->setMethod('stretch')
            ->setSpacing('exact');

        $this->assertSame('#curvePath', $textPath->getAttribute('href'));
        $this->assertSame('10%', $textPath->getAttribute('startOffset'));
        $this->assertSame('stretch', $textPath->getAttribute('method'));
        $this->assertSame('exact', $textPath->getAttribute('spacing'));

        $startOffset = $textPath->getStartOffset();
        $this->assertSame(10.0, $startOffset->getValue());
        $this->assertSame('%', $startOffset->getUnit());
    }

    public function testSetAndGetTextContent(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setTextContent('Hello along the path');

        $this->assertSame($textPath, $result, 'setTextContent should return self for chaining');
        $this->assertSame('Hello along the path', $textPath->getAttribute('textContent'));
        $this->assertSame('Hello along the path', $textPath->getTextContent());
    }

    public function testGetTextContentReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getTextContent());
    }

    public function testSetAndGetBaselineShift(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setBaselineShift('super');

        $this->assertSame($textPath, $result, 'setBaselineShift should return self for chaining');
        $this->assertSame('super', $textPath->getAttribute('baseline-shift'));
        $this->assertSame('super', $textPath->getBaselineShift());
    }

    public function testSetAndGetBaselineShiftWithNumeric(): void
    {
        $textPath = new TextPathElement();
        $textPath->setBaselineShift(5);

        $this->assertSame('5', $textPath->getBaselineShift());
    }

    public function testGetBaselineShiftReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getBaselineShift());
    }

    public function testSetAndGetLetterSpacing(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setLetterSpacing('2px');

        $this->assertSame($textPath, $result, 'setLetterSpacing should return self for chaining');
        $this->assertSame('2px', $textPath->getAttribute('letter-spacing'));
        $this->assertSame('2px', $textPath->getLetterSpacing());
    }

    public function testSetAndGetLetterSpacingWithNumeric(): void
    {
        $textPath = new TextPathElement();
        $textPath->setLetterSpacing(3);

        $this->assertSame('3', $textPath->getLetterSpacing());
    }

    public function testGetLetterSpacingReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getLetterSpacing());
    }

    public function testSetAndGetWordSpacing(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setWordSpacing('5px');

        $this->assertSame($textPath, $result, 'setWordSpacing should return self for chaining');
        $this->assertSame('5px', $textPath->getAttribute('word-spacing'));
        $this->assertSame('5px', $textPath->getWordSpacing());
    }

    public function testSetAndGetWordSpacingWithFloat(): void
    {
        $textPath = new TextPathElement();
        $textPath->setWordSpacing(0.5);

        $this->assertSame('0.5', $textPath->getWordSpacing());
    }

    public function testGetWordSpacingReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getWordSpacing());
    }

    public function testSetAndGetTextDecoration(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setTextDecoration('underline');

        $this->assertSame($textPath, $result, 'setTextDecoration should return self for chaining');
        $this->assertSame('underline', $textPath->getAttribute('text-decoration'));
        $this->assertSame('underline', $textPath->getTextDecoration());
    }

    public function testSetAndGetTextDecorationLineThrough(): void
    {
        $textPath = new TextPathElement();
        $textPath->setTextDecoration('line-through');

        $this->assertSame('line-through', $textPath->getTextDecoration());
    }

    public function testGetTextDecorationReturnsNullWhenNotSet(): void
    {
        $textPath = new TextPathElement();

        $this->assertNull($textPath->getTextDecoration());
    }

    public function testSetStartOffsetWithFloat(): void
    {
        $textPath = new TextPathElement();
        $textPath->setStartOffset(33.5);

        $this->assertSame('33.5', $textPath->getAttribute('startOffset'));
        $startOffset = $textPath->getStartOffset();
        $this->assertInstanceOf(Length::class, $startOffset);
        $this->assertSame(33.5, $startOffset->getValue());
    }

    public function testFullChainWithAllAttributes(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath
            ->setHref('#arc')
            ->setStartOffset('50%')
            ->setMethod('stretch')
            ->setSpacing('exact')
            ->setTextContent('Curved text')
            ->setBaselineShift('sub')
            ->setLetterSpacing('0.1em')
            ->setWordSpacing('0.2em')
            ->setTextDecoration('overline');

        $this->assertSame($textPath, $result);
        $this->assertSame('#arc', $textPath->getHref());
        $this->assertSame(50.0, $textPath->getStartOffset()->getValue());
        $this->assertSame('stretch', $textPath->getMethod());
        $this->assertSame('exact', $textPath->getSpacing());
        $this->assertSame('Curved text', $textPath->getTextContent());
        $this->assertSame('sub', $textPath->getBaselineShift());
        $this->assertSame('0.1em', $textPath->getLetterSpacing());
        $this->assertSame('0.2em', $textPath->getWordSpacing());
        $this->assertSame('overline', $textPath->getTextDecoration());
    }

    public function testCanContainChildren(): void
    {
        $textPath = new TextPathElement();

        $this->assertFalse($textPath->hasChildren());
        $this->assertSame(0, $textPath->getChildCount());
    }
}
