<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\PreserveAspectRatio;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SvgElement::class)]
final class SvgElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $svg = new SvgElement();

        $this->assertSame('svg', $svg->getTagName());
    }

    public function testDefaultXmlns(): void
    {
        $svg = new SvgElement();

        $this->assertSame('http://www.w3.org/2000/svg', $svg->getXmlns());
        $this->assertSame('http://www.w3.org/2000/svg', $svg->getAttribute('xmlns'));
    }

    public function testDefaultVersion(): void
    {
        $svg = new SvgElement();

        $this->assertSame('1.1', $svg->getVersion());
        $this->assertSame('1.1', $svg->getAttribute('version'));
    }

    public function testSetWidthWithString(): void
    {
        $svg = new SvgElement();
        $result = $svg->setWidth('100px');

        $this->assertSame($svg, $result);
        $this->assertSame('100px', $svg->getAttribute('width'));
    }

    public function testSetWidthWithNumber(): void
    {
        $svg = new SvgElement();
        $svg->setWidth(100);

        $this->assertSame('100', $svg->getAttribute('width'));
    }

    public function testSetWidthWithLengthObject(): void
    {
        $svg = new SvgElement();
        $length = Length::parse('55%');
        $svg->setWidth($length);

        $this->assertSame('55%', $svg->getAttribute('width'));
    }

    public function testGetWidth(): void
    {
        $svg = new SvgElement();
        $svg->setWidth('100px');

        $width = $svg->getWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(100.0, $width->getValue());
        $this->assertSame('px', $width->getUnit());
    }

    public function testGetWidthWhenNotSet(): void
    {
        $svg = new SvgElement();

        $this->assertNull($svg->getWidth());
    }

    public function testSetHeightWithString(): void
    {
        $svg = new SvgElement();
        $result = $svg->setHeight('200px');

        $this->assertSame($svg, $result);
        $this->assertSame('200px', $svg->getAttribute('height'));
    }

    public function testSetHeightWithNumber(): void
    {
        $svg = new SvgElement();
        $svg->setHeight(200);

        $this->assertSame('200', $svg->getAttribute('height'));
    }

    public function testSetHeightWithLengthObject(): void
    {
        $svg = new SvgElement();
        $length = Length::parse('75%');
        $svg->setHeight($length);

        $this->assertSame('75%', $svg->getAttribute('height'));
    }

    public function testGetHeight(): void
    {
        $svg = new SvgElement();
        $svg->setHeight('200px');

        $height = $svg->getHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(200.0, $height->getValue());
        $this->assertSame('px', $height->getUnit());
    }

    public function testGetHeightWhenNotSet(): void
    {
        $svg = new SvgElement();

        $this->assertNull($svg->getHeight());
    }

    public function testSetViewboxWithString(): void
    {
        $svg = new SvgElement();
        $result = $svg->setViewbox('0 0 100 200');

        $this->assertSame($svg, $result);
        $this->assertSame('0 0 100 200', $svg->getAttribute('viewBox'));
    }

    public function testSetViewboxWithViewboxObject(): void
    {
        $svg = new SvgElement();
        $viewbox = new Viewbox(10, 20, 100, 200);
        $svg->setViewbox($viewbox);

        $this->assertSame('10 20 100 200', $svg->getAttribute('viewBox'));
    }

    public function testGetViewbox(): void
    {
        $svg = new SvgElement();
        $svg->setViewbox('10 20 100 200');

        $viewbox = $svg->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $viewbox);
        $this->assertSame(10.0, $viewbox->getMinX());
        $this->assertSame(20.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(200.0, $viewbox->getHeight());
    }

    public function testGetViewboxWhenNotSet(): void
    {
        $svg = new SvgElement();

        $this->assertNull($svg->getViewbox());
    }

    public function testSetPreserveAspectRatioWithString(): void
    {
        $svg = new SvgElement();
        $result = $svg->setPreserveAspectRatio('xMinYMin meet');

        $this->assertSame($svg, $result);
        // Note: 'meet' is the default, so it can be omitted
        $this->assertSame('xMinYMin', $svg->getAttribute('preserveAspectRatio'));
    }

    public function testSetPreserveAspectRatioWithObject(): void
    {
        $svg = new SvgElement();
        $par = PreserveAspectRatio::parse('xMaxYMax slice');
        $svg->setPreserveAspectRatio($par);

        $this->assertSame('xMaxYMax slice', $svg->getAttribute('preserveAspectRatio'));
    }

    public function testGetPreserveAspectRatio(): void
    {
        $svg = new SvgElement();
        $svg->setPreserveAspectRatio('xMidYMid slice');

        $par = $svg->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $par);
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('slice', $par->getMeetOrSlice());
    }

    public function testGetPreserveAspectRatioWhenNotSet(): void
    {
        $svg = new SvgElement();
        // Remove the default preserveAspectRatio if set
        $svg->removeAttribute('preserveAspectRatio');

        $this->assertNull($svg->getPreserveAspectRatio());
    }

    public function testSetXmlns(): void
    {
        $svg = new SvgElement();
        $result = $svg->setXmlns('http://www.w3.org/2000/svg');

        $this->assertSame($svg, $result);
        $this->assertSame('http://www.w3.org/2000/svg', $svg->getAttribute('xmlns'));
    }

    public function testSetCustomXmlns(): void
    {
        $svg = new SvgElement();
        $svg->setXmlns('http://example.com/custom');

        $this->assertSame('http://example.com/custom', $svg->getXmlns());
    }

    public function testSetVersion(): void
    {
        $svg = new SvgElement();
        $result = $svg->setVersion('2.0');

        $this->assertSame($svg, $result);
        $this->assertSame('2.0', $svg->getAttribute('version'));
    }

    public function testCanContainChildren(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();

        $svg->appendChild($group);

        $this->assertTrue($svg->hasChildren());
        $this->assertSame(1, $svg->getChildCount());
        $this->assertSame($group, $svg->getChildren()[0]);
    }

    public function testCanContainMultipleChildren(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->appendChild($group);
        $svg->appendChild($path);

        $this->assertSame(2, $svg->getChildCount());
        $this->assertSame($group, $svg->getChildren()[0]);
        $this->assertSame($path, $svg->getChildren()[1]);
    }

    public function testRemoveChild(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();

        $svg->appendChild($group);
        $this->assertSame(1, $svg->getChildCount());

        $svg->removeChild($group);
        $this->assertSame(0, $svg->getChildCount());
        $this->assertFalse($svg->hasChildren());
    }

    public function testClearChildren(): void
    {
        $svg = new SvgElement();
        $group1 = new GroupElement();
        $group2 = new GroupElement();

        $svg->appendChild($group1);
        $svg->appendChild($group2);
        $this->assertSame(2, $svg->getChildCount());

        $svg->clearChildren();
        $this->assertSame(0, $svg->getChildCount());
        $this->assertFalse($svg->hasChildren());
    }

    public function testAttributeSerialization(): void
    {
        $svg = new SvgElement();
        $svg->setWidth('100px');
        $svg->setHeight('200px');
        $svg->setViewbox('0 0 100 200');

        $attributes = $svg->getAttributes();
        $this->assertArrayHasKey('width', $attributes);
        $this->assertArrayHasKey('height', $attributes);
        $this->assertArrayHasKey('viewBox', $attributes);
        $this->assertArrayHasKey('xmlns', $attributes);
        $this->assertArrayHasKey('version', $attributes);
    }
}
