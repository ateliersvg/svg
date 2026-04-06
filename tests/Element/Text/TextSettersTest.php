<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TextPathElement;
use Atelier\Svg\Element\Text\TspanElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextElement::class)]
#[CoversClass(TspanElement::class)]
#[CoversClass(TextPathElement::class)]
final class TextSettersTest extends TestCase
{
    public function testTextElementSetFontSize(): void
    {
        $text = new TextElement();
        $result = $text->setFontSize('16px');

        $this->assertSame($text, $result);
        $this->assertSame('16px', $text->getAttribute('font-size'));
    }

    public function testTextElementSetFontSizeWithNumber(): void
    {
        $text = new TextElement();
        $text->setFontSize(14);

        $this->assertSame('14', $text->getAttribute('font-size'));
    }

    public function testTextElementSetFontSizeWithFloat(): void
    {
        $text = new TextElement();
        $text->setFontSize(1.5);

        $this->assertSame('1.5', $text->getAttribute('font-size'));
    }

    public function testTextElementSetFontFamily(): void
    {
        $text = new TextElement();
        $result = $text->setFontFamily('Arial, sans-serif');

        $this->assertSame($text, $result);
        $this->assertSame('Arial, sans-serif', $text->getAttribute('font-family'));
    }

    public function testTextElementSetFontWeight(): void
    {
        $text = new TextElement();
        $result = $text->setFontWeight('bold');

        $this->assertSame($text, $result);
        $this->assertSame('bold', $text->getAttribute('font-weight'));
    }

    public function testTextElementSetFontWeightWithInt(): void
    {
        $text = new TextElement();
        $text->setFontWeight(700);

        $this->assertSame('700', $text->getAttribute('font-weight'));
    }

    public function testTextElementSetFontStyle(): void
    {
        $text = new TextElement();
        $result = $text->setFontStyle('italic');

        $this->assertSame($text, $result);
        $this->assertSame('italic', $text->getAttribute('font-style'));
    }

    public function testTextElementSetTextAnchor(): void
    {
        $text = new TextElement();
        $result = $text->setTextAnchor('middle');

        $this->assertSame($text, $result);
        $this->assertSame('middle', $text->getAttribute('text-anchor'));
    }

    public function testTextElementSetDominantBaseline(): void
    {
        $text = new TextElement();
        $result = $text->setDominantBaseline('central');

        $this->assertSame($text, $result);
        $this->assertSame('central', $text->getAttribute('dominant-baseline'));
    }

    public function testTspanElementSetFontSize(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setFontSize('12px');

        $this->assertSame($tspan, $result);
        $this->assertSame('12px', $tspan->getAttribute('font-size'));
    }

    public function testTspanElementSetFontFamily(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setFontFamily('Helvetica');

        $this->assertSame($tspan, $result);
        $this->assertSame('Helvetica', $tspan->getAttribute('font-family'));
    }

    public function testTspanElementSetFontWeight(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setFontWeight(400);

        $this->assertSame($tspan, $result);
        $this->assertSame('400', $tspan->getAttribute('font-weight'));
    }

    public function testTspanElementSetFontStyle(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setFontStyle('oblique');

        $this->assertSame($tspan, $result);
        $this->assertSame('oblique', $tspan->getAttribute('font-style'));
    }

    public function testTspanElementSetTextAnchor(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setTextAnchor('end');

        $this->assertSame($tspan, $result);
        $this->assertSame('end', $tspan->getAttribute('text-anchor'));
    }

    public function testTspanElementSetDominantBaseline(): void
    {
        $tspan = new TspanElement();
        $result = $tspan->setDominantBaseline('hanging');

        $this->assertSame($tspan, $result);
        $this->assertSame('hanging', $tspan->getAttribute('dominant-baseline'));
    }

    public function testTextPathElementSetFontSize(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setFontSize('20px');

        $this->assertSame($textPath, $result);
        $this->assertSame('20px', $textPath->getAttribute('font-size'));
    }

    public function testTextPathElementSetFontFamily(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setFontFamily('monospace');

        $this->assertSame($textPath, $result);
        $this->assertSame('monospace', $textPath->getAttribute('font-family'));
    }

    public function testTextPathElementSetFontWeight(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setFontWeight('bold');

        $this->assertSame($textPath, $result);
        $this->assertSame('bold', $textPath->getAttribute('font-weight'));
    }

    public function testTextPathElementSetFontStyle(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setFontStyle('italic');

        $this->assertSame($textPath, $result);
        $this->assertSame('italic', $textPath->getAttribute('font-style'));
    }

    public function testTextPathElementSetTextAnchor(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setTextAnchor('start');

        $this->assertSame($textPath, $result);
        $this->assertSame('start', $textPath->getAttribute('text-anchor'));
    }

    public function testTextPathElementSetDominantBaseline(): void
    {
        $textPath = new TextPathElement();
        $result = $textPath->setDominantBaseline('middle');

        $this->assertSame($textPath, $result);
        $this->assertSame('middle', $textPath->getAttribute('dominant-baseline'));
    }

    public function testChainingMultipleTextSetters(): void
    {
        $text = new TextElement();
        $result = $text
            ->setFontSize('16px')
            ->setFontFamily('Arial')
            ->setFontWeight('bold')
            ->setFontStyle('italic')
            ->setTextAnchor('middle')
            ->setDominantBaseline('central');

        $this->assertSame($text, $result);
        $this->assertSame('16px', $text->getAttribute('font-size'));
        $this->assertSame('Arial', $text->getAttribute('font-family'));
        $this->assertSame('bold', $text->getAttribute('font-weight'));
        $this->assertSame('italic', $text->getAttribute('font-style'));
        $this->assertSame('middle', $text->getAttribute('text-anchor'));
        $this->assertSame('central', $text->getAttribute('dominant-baseline'));
    }

    public function testChainingMultipleTextSettersOnTspan(): void
    {
        $tspan = new TspanElement();
        $result = $tspan
            ->setFontSize('12px')
            ->setFontFamily('Helvetica')
            ->setFontWeight(700)
            ->setFontStyle('normal')
            ->setTextAnchor('end')
            ->setDominantBaseline('auto');

        $this->assertSame($tspan, $result);
        $this->assertSame('12px', $tspan->getAttribute('font-size'));
        $this->assertSame('Helvetica', $tspan->getAttribute('font-family'));
        $this->assertSame('700', $tspan->getAttribute('font-weight'));
        $this->assertSame('normal', $tspan->getAttribute('font-style'));
        $this->assertSame('end', $tspan->getAttribute('text-anchor'));
        $this->assertSame('auto', $tspan->getAttribute('dominant-baseline'));
    }

    public function testReturnTypeIsStatic(): void
    {
        $text = new TextElement();

        $this->assertInstanceOf(TextElement::class, $text->setFontSize('16px'));
        $this->assertInstanceOf(TextElement::class, $text->setFontFamily('Arial'));
        $this->assertInstanceOf(TextElement::class, $text->setFontWeight('bold'));
        $this->assertInstanceOf(TextElement::class, $text->setFontStyle('italic'));
        $this->assertInstanceOf(TextElement::class, $text->setTextAnchor('middle'));
        $this->assertInstanceOf(TextElement::class, $text->setDominantBaseline('central'));

        $tspan = new TspanElement();

        $this->assertInstanceOf(TspanElement::class, $tspan->setFontSize('16px'));
        $this->assertInstanceOf(TspanElement::class, $tspan->setFontFamily('Arial'));
        $this->assertInstanceOf(TspanElement::class, $tspan->setFontWeight('bold'));
        $this->assertInstanceOf(TspanElement::class, $tspan->setFontStyle('italic'));
        $this->assertInstanceOf(TspanElement::class, $tspan->setTextAnchor('middle'));
        $this->assertInstanceOf(TspanElement::class, $tspan->setDominantBaseline('central'));

        $textPath = new TextPathElement();

        $this->assertInstanceOf(TextPathElement::class, $textPath->setFontSize('16px'));
        $this->assertInstanceOf(TextPathElement::class, $textPath->setFontFamily('Arial'));
        $this->assertInstanceOf(TextPathElement::class, $textPath->setFontWeight('bold'));
        $this->assertInstanceOf(TextPathElement::class, $textPath->setFontStyle('italic'));
        $this->assertInstanceOf(TextPathElement::class, $textPath->setTextAnchor('middle'));
        $this->assertInstanceOf(TextPathElement::class, $textPath->setDominantBaseline('central'));
    }
}
