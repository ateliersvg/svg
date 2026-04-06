<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeFuncAElement;
use Atelier\Svg\Element\Filter\FeFuncBElement;
use Atelier\Svg\Element\Filter\FeFuncGElement;
use Atelier\Svg\Element\Filter\FeFuncRElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeFuncAElement::class)]
#[CoversClass(FeFuncBElement::class)]
#[CoversClass(FeFuncGElement::class)]
#[CoversClass(FeFuncRElement::class)]
final class FeFuncElementsTest extends TestCase
{
    /**
     * @return iterable<string, array{object, string}>
     */
    public static function elementProvider(): iterable
    {
        yield 'feFuncA' => [new FeFuncAElement(), 'feFuncA'];
        yield 'feFuncB' => [new FeFuncBElement(), 'feFuncB'];
        yield 'feFuncG' => [new FeFuncGElement(), 'feFuncG'];
        yield 'feFuncR' => [new FeFuncRElement(), 'feFuncR'];
    }

    #[DataProvider('elementProvider')]
    public function testGetTagName(object $element, string $expectedTag): void
    {
        $this->assertSame($expectedTag, $element->getTagName());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetType(object $element, string $expectedTag): void
    {
        $result = $element->setType('gamma');

        $this->assertSame($element, $result);
        $this->assertSame('gamma', $element->getType());
    }

    #[DataProvider('elementProvider')]
    public function testAllTransferFunctionTypes(object $element, string $expectedTag): void
    {
        $types = ['identity', 'table', 'discrete', 'linear', 'gamma'];
        foreach ($types as $type) {
            $element->setType($type);
            $this->assertSame($type, $element->getType());
        }
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetTableValues(object $element, string $expectedTag): void
    {
        $result = $element->setTableValues('0 0.5 1');

        $this->assertSame($element, $result);
        $this->assertSame('0 0.5 1', $element->getTableValues());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetSlope(object $element, string $expectedTag): void
    {
        $result = $element->setSlope(1.5);

        $this->assertSame($element, $result);
        $this->assertSame('1.5', $element->getSlope());
    }

    #[DataProvider('elementProvider')]
    public function testSetSlopeWithInt(object $element, string $expectedTag): void
    {
        $element->setSlope(2);

        $this->assertSame('2', $element->getSlope());
    }

    #[DataProvider('elementProvider')]
    public function testSetSlopeWithString(object $element, string $expectedTag): void
    {
        $element->setSlope('0.75');

        $this->assertSame('0.75', $element->getSlope());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetIntercept(object $element, string $expectedTag): void
    {
        $result = $element->setIntercept(0.2);

        $this->assertSame($element, $result);
        $this->assertSame('0.2', $element->getIntercept());
    }

    #[DataProvider('elementProvider')]
    public function testSetInterceptWithInt(object $element, string $expectedTag): void
    {
        $element->setIntercept(1);

        $this->assertSame('1', $element->getIntercept());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetAmplitude(object $element, string $expectedTag): void
    {
        $result = $element->setAmplitude(1.5);

        $this->assertSame($element, $result);
        $this->assertSame('1.5', $element->getAmplitude());
    }

    #[DataProvider('elementProvider')]
    public function testSetAmplitudeWithInt(object $element, string $expectedTag): void
    {
        $element->setAmplitude(3);

        $this->assertSame('3', $element->getAmplitude());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetExponent(object $element, string $expectedTag): void
    {
        $result = $element->setExponent(2.5);

        $this->assertSame($element, $result);
        $this->assertSame('2.5', $element->getExponent());
    }

    #[DataProvider('elementProvider')]
    public function testSetExponentWithInt(object $element, string $expectedTag): void
    {
        $element->setExponent(3);

        $this->assertSame('3', $element->getExponent());
    }

    #[DataProvider('elementProvider')]
    public function testSetAndGetOffset(object $element, string $expectedTag): void
    {
        $result = $element->setOffset(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getOffset());
    }

    #[DataProvider('elementProvider')]
    public function testSetOffsetWithInt(object $element, string $expectedTag): void
    {
        $element->setOffset(0);

        $this->assertSame('0', $element->getOffset());
    }

    #[DataProvider('elementProvider')]
    public function testGetAttributesWhenNotSet(object $element, string $expectedTag): void
    {
        $this->assertNull($element->getType());
        $this->assertNull($element->getTableValues());
        $this->assertNull($element->getSlope());
        $this->assertNull($element->getIntercept());
        $this->assertNull($element->getAmplitude());
        $this->assertNull($element->getExponent());
        $this->assertNull($element->getOffset());
    }
}
