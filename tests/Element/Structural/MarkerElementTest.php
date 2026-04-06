<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\Structural\MarkerElement;
use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkerElement::class)]
final class MarkerElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $marker = new MarkerElement();

        $this->assertSame('marker', $marker->getTagName());
    }

    public function testSetAndGetRefX(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setRefX(10);

        $this->assertSame($marker, $result, 'setRefX should return self for chaining');
        $this->assertSame('10', $marker->getRefX());
    }

    public function testSetRefXWithFloat(): void
    {
        $marker = new MarkerElement();
        $marker->setRefX(5.5);

        $this->assertSame('5.5', $marker->getRefX());
    }

    public function testSetRefXWithString(): void
    {
        $marker = new MarkerElement();
        $marker->setRefX('center');

        $this->assertSame('center', $marker->getRefX());
    }

    public function testGetRefXReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getRefX());
    }

    public function testSetAndGetRefY(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setRefY(20);

        $this->assertSame($marker, $result, 'setRefY should return self for chaining');
        $this->assertSame('20', $marker->getRefY());
    }

    public function testSetRefYWithFloat(): void
    {
        $marker = new MarkerElement();
        $marker->setRefY(7.5);

        $this->assertSame('7.5', $marker->getRefY());
    }

    public function testGetRefYReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getRefY());
    }

    public function testSetAndGetMarkerWidth(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setMarkerWidth(12);

        $this->assertSame($marker, $result, 'setMarkerWidth should return self for chaining');

        $width = $marker->getMarkerWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(12.0, $width->getValue());
    }

    public function testSetMarkerWidthWithString(): void
    {
        $marker = new MarkerElement();
        $marker->setMarkerWidth('8px');

        $width = $marker->getMarkerWidth();
        $this->assertInstanceOf(Length::class, $width);
        $this->assertSame(8.0, $width->getValue());
        $this->assertSame('px', $width->getUnit());
    }

    public function testGetMarkerWidthReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getMarkerWidth());
    }

    public function testSetAndGetMarkerHeight(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setMarkerHeight(15);

        $this->assertSame($marker, $result, 'setMarkerHeight should return self for chaining');

        $height = $marker->getMarkerHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(15.0, $height->getValue());
    }

    public function testSetMarkerHeightWithString(): void
    {
        $marker = new MarkerElement();
        $marker->setMarkerHeight('10em');

        $height = $marker->getMarkerHeight();
        $this->assertInstanceOf(Length::class, $height);
        $this->assertSame(10.0, $height->getValue());
        $this->assertSame('em', $height->getUnit());
    }

    public function testGetMarkerHeightReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getMarkerHeight());
    }

    public function testSetAndGetOrient(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setOrient('auto');

        $this->assertSame($marker, $result, 'setOrient should return self for chaining');
        $this->assertSame('auto', $marker->getOrient());
    }

    public function testSetOrientWithAutoStartReverse(): void
    {
        $marker = new MarkerElement();
        $marker->setOrient('auto-start-reverse');

        $this->assertSame('auto-start-reverse', $marker->getOrient());
    }

    public function testSetOrientWithAngle(): void
    {
        $marker = new MarkerElement();
        $marker->setOrient(45);

        $this->assertSame('45', $marker->getOrient());
    }

    public function testGetOrientReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getOrient());
    }

    public function testSetAndGetViewbox(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setViewbox('0 0 10 10');

        $this->assertSame($marker, $result, 'setViewbox should return self for chaining');

        $viewbox = $marker->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $viewbox);
        $this->assertSame('0 0 10 10', $viewbox->toString());
    }

    public function testSetViewboxWithObject(): void
    {
        $marker = new MarkerElement();
        $viewbox = new Viewbox(0, 0, 20, 20);
        $marker->setViewbox($viewbox);

        $result = $marker->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $result);
        $this->assertSame('0 0 20 20', $result->toString());
    }

    public function testGetViewboxReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getViewbox());
    }

    public function testSetAndGetMarkerUnits(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setMarkerUnits('strokeWidth');

        $this->assertSame($marker, $result, 'setMarkerUnits should return self for chaining');
        $this->assertSame('strokeWidth', $marker->getMarkerUnits());
    }

    public function testSetMarkerUnitsUserSpaceOnUse(): void
    {
        $marker = new MarkerElement();
        $marker->setMarkerUnits('userSpaceOnUse');

        $this->assertSame('userSpaceOnUse', $marker->getMarkerUnits());
    }

    public function testGetMarkerUnitsReturnsNullWhenNotSet(): void
    {
        $marker = new MarkerElement();

        $this->assertNull($marker->getMarkerUnits());
    }

    public function testSetSize(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setSize(10, 20);

        $this->assertSame($marker, $result, 'setSize should return self for chaining');
        $this->assertSame(10.0, $marker->getMarkerWidth()->getValue());
        $this->assertSame(20.0, $marker->getMarkerHeight()->getValue());
    }

    public function testSetRefPoint(): void
    {
        $marker = new MarkerElement();
        $result = $marker->setRefPoint(5, 10);

        $this->assertSame($marker, $result, 'setRefPoint should return self for chaining');
        $this->assertSame('5', $marker->getRefX());
        $this->assertSame('10', $marker->getRefY());
    }

    public function testMethodChaining(): void
    {
        $marker = new MarkerElement();
        $result = $marker
            ->setRefX(5)
            ->setRefY(5)
            ->setMarkerWidth(10)
            ->setMarkerHeight(10)
            ->setOrient('auto')
            ->setMarkerUnits('strokeWidth')
            ->setViewbox('0 0 10 10');

        $this->assertSame($marker, $result);
        $this->assertSame('5', $marker->getRefX());
        $this->assertSame('5', $marker->getRefY());
        $this->assertSame(10.0, $marker->getMarkerWidth()->getValue());
        $this->assertSame(10.0, $marker->getMarkerHeight()->getValue());
        $this->assertSame('auto', $marker->getOrient());
        $this->assertSame('strokeWidth', $marker->getMarkerUnits());
        $this->assertSame('0 0 10 10', $marker->getViewbox()->toString());
    }

    public function testContainerBehavior(): void
    {
        $marker = new MarkerElement();
        $path = new \Atelier\Svg\Element\PathElement();

        $marker->appendChild($path);

        $this->assertTrue($marker->hasChildren());
        $this->assertSame(1, $marker->getChildCount());
        $this->assertSame($path, $marker->getChildren()[0]);
    }
}
