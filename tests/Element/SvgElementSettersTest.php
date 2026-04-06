<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SvgElement::class)]
final class SvgElementSettersTest extends TestCase
{
    public function testSetWidthReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setWidth(100);

        $this->assertSame($svg, $result);
    }

    public function testSetHeightReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setHeight(200);

        $this->assertSame($svg, $result);
    }

    public function testSetViewboxReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setViewbox('0 0 100 100');

        $this->assertSame($svg, $result);
    }

    public function testSetPreserveAspectRatioReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($svg, $result);
    }

    public function testSetXmlnsReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setXmlns('http://www.w3.org/2000/svg');

        $this->assertSame($svg, $result);
    }

    public function testSetVersionReturnsSameInstance(): void
    {
        $svg = new SvgElement();
        $result = $svg->setVersion('1.1');

        $this->assertSame($svg, $result);
    }

    public function testFluentChainingAllSetters(): void
    {
        $svg = new SvgElement();

        $result = $svg
            ->setWidth(800)
            ->setHeight(600)
            ->setViewbox('0 0 800 600')
            ->setPreserveAspectRatio('xMidYMid meet')
            ->setXmlns('http://www.w3.org/2000/svg')
            ->setVersion('2.0');

        $this->assertSame($svg, $result);
        $this->assertSame('800', $svg->getAttribute('width'));
        $this->assertSame('600', $svg->getAttribute('height'));
        $this->assertSame('0 0 800 600', $svg->getAttribute('viewBox'));
        $this->assertSame('xMidYMid', $svg->getAttribute('preserveAspectRatio'));
        $this->assertSame('http://www.w3.org/2000/svg', $svg->getAttribute('xmlns'));
        $this->assertSame('2.0', $svg->getAttribute('version'));
    }
}
