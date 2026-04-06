<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Gradient;

use Atelier\Svg\Element\Gradient\StopElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StopElement::class)]
final class StopElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $stop = new StopElement();

        $this->assertSame('stop', $stop->getTagName());
    }

    public function testSetAndGetOffset(): void
    {
        $stop = new StopElement();
        $result = $stop->setOffset(0);

        $this->assertSame($stop, $result, 'setOffset should return self for chaining');
        $this->assertSame('0', $stop->getAttribute('offset'));
        $this->assertSame('0', $stop->getOffset());
    }

    public function testSetAndGetOffsetWithFloat(): void
    {
        $stop = new StopElement();
        $stop->setOffset(0.5);

        $this->assertSame('0.5', $stop->getAttribute('offset'));
        $this->assertSame('0.5', $stop->getOffset());
    }

    public function testSetAndGetOffsetWithPercentage(): void
    {
        $stop = new StopElement();
        $stop->setOffset('50%');

        $this->assertSame('50%', $stop->getAttribute('offset'));
        $this->assertSame('50%', $stop->getOffset());
    }

    public function testSetAndGetOffsetWithOne(): void
    {
        $stop = new StopElement();
        $stop->setOffset(1);

        $this->assertSame('1', $stop->getAttribute('offset'));
        $this->assertSame('1', $stop->getOffset());
    }

    public function testGetOffsetReturnsNullWhenNotSet(): void
    {
        $stop = new StopElement();

        $this->assertNull($stop->getOffset());
    }

    public function testSetAndGetStopColor(): void
    {
        $stop = new StopElement();
        $result = $stop->setStopColor('red');

        $this->assertSame($stop, $result, 'setStopColor should return self for chaining');
        $this->assertSame('red', $stop->getAttribute('stop-color'));
        $this->assertSame('red', $stop->getStopColor());
    }

    public function testSetAndGetStopColorWithHex(): void
    {
        $stop = new StopElement();
        $stop->setStopColor('#ff0000');

        $this->assertSame('#ff0000', $stop->getAttribute('stop-color'));
        $this->assertSame('#ff0000', $stop->getStopColor());
    }

    public function testSetAndGetStopColorWithRgb(): void
    {
        $stop = new StopElement();
        $stop->setStopColor('rgb(255, 0, 0)');

        $this->assertSame('rgb(255, 0, 0)', $stop->getAttribute('stop-color'));
        $this->assertSame('rgb(255, 0, 0)', $stop->getStopColor());
    }

    public function testGetStopColorReturnsNullWhenNotSet(): void
    {
        $stop = new StopElement();

        $this->assertNull($stop->getStopColor());
    }

    public function testSetAndGetStopOpacity(): void
    {
        $stop = new StopElement();
        $result = $stop->setStopOpacity(1);

        $this->assertSame($stop, $result, 'setStopOpacity should return self for chaining');
        $this->assertSame('1', $stop->getAttribute('stop-opacity'));
        $this->assertSame('1', $stop->getStopOpacity());
    }

    public function testSetAndGetStopOpacityWithFloat(): void
    {
        $stop = new StopElement();
        $stop->setStopOpacity(0.5);

        $this->assertSame('0.5', $stop->getAttribute('stop-opacity'));
        $this->assertSame('0.5', $stop->getStopOpacity());
    }

    public function testSetAndGetStopOpacityWithZero(): void
    {
        $stop = new StopElement();
        $stop->setStopOpacity(0);

        $this->assertSame('0', $stop->getAttribute('stop-opacity'));
        $this->assertSame('0', $stop->getStopOpacity());
    }

    public function testGetStopOpacityReturnsNullWhenNotSet(): void
    {
        $stop = new StopElement();

        $this->assertNull($stop->getStopOpacity());
    }

    public function testMethodChaining(): void
    {
        $stop = new StopElement();
        $result = $stop
            ->setOffset(0.5)
            ->setStopColor('#ff0000')
            ->setStopOpacity(0.8);

        $this->assertSame($stop, $result);
        $this->assertSame('0.5', $stop->getOffset());
        $this->assertSame('#ff0000', $stop->getStopColor());
        $this->assertSame('0.8', $stop->getStopOpacity());
    }

    public function testCompleteStopConfiguration(): void
    {
        $stop = new StopElement();
        $stop
            ->setOffset('25%')
            ->setStopColor('blue')
            ->setStopOpacity(0.75);

        $this->assertSame('25%', $stop->getAttribute('offset'));
        $this->assertSame('blue', $stop->getAttribute('stop-color'));
        $this->assertSame('0.75', $stop->getAttribute('stop-opacity'));

        $this->assertSame('25%', $stop->getOffset());
        $this->assertSame('blue', $stop->getStopColor());
        $this->assertSame('0.75', $stop->getStopOpacity());
    }

    public function testMultipleStopsConfiguration(): void
    {
        $stop1 = new StopElement();
        $stop1->setOffset(0)->setStopColor('white')->setStopOpacity(1);

        $stop2 = new StopElement();
        $stop2->setOffset('50%')->setStopColor('gray')->setStopOpacity(0.5);

        $stop3 = new StopElement();
        $stop3->setOffset(1)->setStopColor('black')->setStopOpacity(0);

        $this->assertSame('0', $stop1->getOffset());
        $this->assertSame('white', $stop1->getStopColor());
        $this->assertSame('1', $stop1->getStopOpacity());

        $this->assertSame('50%', $stop2->getOffset());
        $this->assertSame('gray', $stop2->getStopColor());
        $this->assertSame('0.5', $stop2->getStopOpacity());

        $this->assertSame('1', $stop3->getOffset());
        $this->assertSame('black', $stop3->getStopColor());
        $this->assertSame('0', $stop3->getStopOpacity());
    }

    public function testStopAttributeNamesWithHyphen(): void
    {
        $stop = new StopElement();
        $stop->setStopColor('red');
        $stop->setStopOpacity(0.5);

        // Verify the actual attribute names use hyphens, not camelCase
        $this->assertTrue($stop->hasAttribute('stop-color'));
        $this->assertTrue($stop->hasAttribute('stop-opacity'));
        $this->assertFalse($stop->hasAttribute('stopColor'));
        $this->assertFalse($stop->hasAttribute('stopOpacity'));
    }
}
