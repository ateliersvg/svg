<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Path\Serializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Serializer::class)]
final class SerializerTest extends TestCase
{
    public function testSerializeEmptyCommands(): void
    {
        $result = Serializer::serialize([]);
        $this->assertSame('', $result);
    }

    public function testSerializeMoveToCommand(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [10, 20]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M10 20', $result);
    }

    public function testSerializeLineToCommand(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [0, 0]],
            ['type' => 'L', 'coords' => [30, 40]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M0 0 L30 40', $result);
    }

    public function testSerializeClosePathCommand(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [10, 20]],
            ['type' => 'L', 'coords' => [30, 40]],
            ['type' => 'Z'],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M10 20 L30 40 Z', $result);
    }

    public function testSerializeLowercaseClosePath(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [0, 0]],
            ['type' => 'z'],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M0 0 z', $result);
    }

    public function testSerializeHorizontalLineTo(): void
    {
        $commands = [
            ['type' => 'H', 'coords' => [50]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('H50', $result);
    }

    public function testSerializeVerticalLineTo(): void
    {
        $commands = [
            ['type' => 'V', 'coords' => [80]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('V80', $result);
    }

    public function testSerializeCurveToCommand(): void
    {
        $commands = [
            ['type' => 'C', 'coords' => [10, 20, 30, 40, 50, 60]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('C10 20 30 40 50 60', $result);
    }

    public function testSerializeQuadraticCurveToCommand(): void
    {
        $commands = [
            ['type' => 'Q', 'coords' => [20, 30, 40, 50]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('Q20 30 40 50', $result);
    }

    public function testSerializeArcToCommand(): void
    {
        $commands = [
            ['type' => 'A', 'coords' => [25, 26, 30, 0, 1, 50, 25]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('A25 26 30 0 1 50 25', $result);
    }

    public function testSerializeWithPrecision(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [10.123456789, 20.987654321]],
        ];

        $result = Serializer::serialize($commands, 2);
        $this->assertSame('M10.12 20.99', $result);
    }

    public function testSerializeTrimsTrailingZeros(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [10.0, 20.0]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M10 20', $result);
    }

    public function testSerializeHandlesNegativeZero(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [-0.0, 0.0]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M0 0', $result);
    }

    public function testSerializeWithObjectCommands(): void
    {
        $cmd = new \stdClass();
        $cmd->type = 'M';
        $cmd->coords = [10, 20];

        $result = Serializer::serialize([$cmd]);
        $this->assertSame('M10 20', $result);
    }

    public function testSerializeThrowsOnMissingTypeInArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize([['coords' => [10, 20]]]);
    }

    public function testSerializeThrowsOnMissingTypeInObject(): void
    {
        $cmd = new \stdClass();
        $cmd->coords = [10, 20];

        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize([$cmd]);
    }

    public function testSerializeThrowsOnInvalidCommandType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize([['type' => '99']]);
    }

    public function testSerializeThrowsOnMissingCoordinates(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize([['type' => 'M']]);
    }

    public function testSerializeThrowsOnInvalidFormat(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize(['invalid']);
    }

    public function testSerializeThrowsOnNonNumericCoordinate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Serializer::serialize([['type' => 'M', 'coords' => ['abc', 'def']]]);
    }

    public function testSerializeRelativeCommands(): void
    {
        $commands = [
            ['type' => 'm', 'coords' => [10, 20]],
            ['type' => 'l', 'coords' => [5, 10]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('m10 20 l5 10', $result);
    }

    public function testSerializeComplexPath(): void
    {
        $commands = [
            ['type' => 'M', 'coords' => [10, 20]],
            ['type' => 'L', 'coords' => [30, 40]],
            ['type' => 'C', 'coords' => [50, 60, 70, 80, 90, 100]],
            ['type' => 'Z'],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('M10 20 L30 40 C50 60 70 80 90 100 Z', $result);
    }

    public function testSerializeClosePathIgnoresCoords(): void
    {
        $commands = [
            ['type' => 'Z', 'coords' => [10, 20]],
        ];

        $result = Serializer::serialize($commands);
        $this->assertSame('Z', $result);
    }
}
