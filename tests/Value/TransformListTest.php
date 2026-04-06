<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Transform\MatrixTransform;
use Atelier\Svg\Value\Transform\RotateTransform;
use Atelier\Svg\Value\Transform\ScaleTransform;
use Atelier\Svg\Value\Transform\SkewXTransform;
use Atelier\Svg\Value\Transform\SkewYTransform;
use Atelier\Svg\Value\Transform\TranslateTransform;
use Atelier\Svg\Value\TransformList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformList::class)]
final class TransformListTest extends TestCase
{
    public function testParseTranslateWithTwoArgs(): void
    {
        $list = TransformList::parse('translate(10, 20)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(TranslateTransform::class, $list->getTransforms()[0]);
        $this->assertSame(1, $list->count());
        $this->assertFalse($list->isEmpty());
    }

    public function testParseTranslateWithOneArg(): void
    {
        $list = TransformList::parse('translate(10)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(TranslateTransform::class, $list->getTransforms()[0]);
    }

    public function testParseScale(): void
    {
        $list = TransformList::parse('scale(2, 3)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(ScaleTransform::class, $list->getTransforms()[0]);
    }

    public function testParseScaleUniform(): void
    {
        $list = TransformList::parse('scale(2)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(ScaleTransform::class, $list->getTransforms()[0]);
    }

    public function testParseRotate(): void
    {
        $list = TransformList::parse('rotate(45)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(RotateTransform::class, $list->getTransforms()[0]);
    }

    public function testParseRotateWithCenter(): void
    {
        $list = TransformList::parse('rotate(45, 50, 50)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(RotateTransform::class, $list->getTransforms()[0]);
    }

    public function testParseSkewX(): void
    {
        $list = TransformList::parse('skewX(30)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(SkewXTransform::class, $list->getTransforms()[0]);
    }

    public function testParseSkewY(): void
    {
        $list = TransformList::parse('skewY(30)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(SkewYTransform::class, $list->getTransforms()[0]);
    }

    public function testParseMatrix(): void
    {
        $list = TransformList::parse('matrix(1, 0, 0, 1, 0, 0)');
        $this->assertCount(1, $list->getTransforms());
        $this->assertInstanceOf(MatrixTransform::class, $list->getTransforms()[0]);
    }

    public function testParseMultipleTransforms(): void
    {
        $list = TransformList::parse('translate(10, 20) rotate(45) scale(2)');
        $this->assertSame(3, $list->count());
        $this->assertInstanceOf(TranslateTransform::class, $list->getTransforms()[0]);
        $this->assertInstanceOf(RotateTransform::class, $list->getTransforms()[1]);
        $this->assertInstanceOf(ScaleTransform::class, $list->getTransforms()[2]);
    }

    public function testParseNullReturnsEmpty(): void
    {
        $list = TransformList::parse(null);
        $this->assertTrue($list->isEmpty());
        $this->assertSame(0, $list->count());
        $this->assertSame([], $list->getTransforms());
    }

    public function testParseEmptyStringReturnsEmpty(): void
    {
        $list = TransformList::parse('');
        $this->assertTrue($list->isEmpty());
    }

    public function testParseWhitespaceOnlyReturnsEmpty(): void
    {
        $list = TransformList::parse('   ');
        $this->assertTrue($list->isEmpty());
    }

    public function testParseThrowsOnInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('not-a-transform');
    }

    public function testParseThrowsOnUnknownFunction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('unknown(1)');
    }

    public function testParseThrowsOnTranslateWithTooManyArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('translate(1, 2, 3)');
    }

    public function testParseThrowsOnScaleWithTooManyArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('scale(1, 2, 3)');
    }

    public function testParseThrowsOnRotateWithTwoArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('rotate(45, 50)');
    }

    public function testParseThrowsOnSkewXWithTooManyArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('skewX(1, 2)');
    }

    public function testParseThrowsOnSkewYWithTooManyArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('skewY(1, 2)');
    }

    public function testParseThrowsOnMatrixWithWrongArgCount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TransformList::parse('matrix(1, 0, 0, 1)');
    }

    public function testParseThrowsOnRotateWithFourArgs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rotate requires 1 or 3 arguments');
        TransformList::parse('rotate(1, 2, 3, 4)');
    }

    public function testFromArray(): void
    {
        $translate = TransformList::parse('translate(5, 10)')->getTransforms()[0];
        $list = TransformList::fromArray([$translate]);
        $this->assertSame(1, $list->count());
        $this->assertFalse($list->isEmpty());
    }

    public function testFromArrayEmpty(): void
    {
        $list = TransformList::fromArray([]);
        $this->assertTrue($list->isEmpty());
        $this->assertSame(0, $list->count());
    }

    public function testToString(): void
    {
        $list = TransformList::parse('translate(10, 20) scale(2)');
        $result = $list->toString();
        $this->assertStringContainsString('translate(', $result);
        $this->assertStringContainsString('scale(', $result);
    }

    public function testToStringEmpty(): void
    {
        $list = TransformList::parse(null);
        $this->assertSame('', $list->toString());
    }

    public function testMagicToString(): void
    {
        $list = TransformList::parse('rotate(45)');
        $this->assertSame($list->toString(), (string) $list);
    }

    public function testRoundTrip(): void
    {
        $original = 'translate(10 20) rotate(45) scale(2 3)';
        $list = TransformList::parse($original);
        $reparsed = TransformList::parse($list->toString());
        $this->assertSame($list->count(), $reparsed->count());
        $this->assertSame($list->toString(), $reparsed->toString());
    }
}
