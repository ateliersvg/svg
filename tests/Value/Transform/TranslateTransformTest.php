<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value\Transform;

use Atelier\Svg\Value\Length;
use Atelier\Svg\Value\Transform\TranslateTransform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the TranslateTransform class.
 */
#[CoversClass(TranslateTransform::class)]
final class TranslateTransformTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $tx = Length::parse('10px');
        $ty = Length::parse('20px');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertSame($tx, $transform->getTx());
        $this->assertSame($ty, $transform->getTy());
    }

    public function testToStringBothValues(): void
    {
        $tx = Length::parse('10px');
        $ty = Length::parse('20px');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(10px,20px)', $transform->toString());
    }

    public function testToStringTyZero(): void
    {
        $tx = Length::parse('10px');
        $ty = Length::parse('0');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(10px)', $transform->toString());
    }

    public function testToStringBothZero(): void
    {
        $tx = Length::parse('0');
        $ty = Length::parse('0');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(0)', $transform->toString());
    }

    public function testToStringNegativeValues(): void
    {
        $tx = Length::parse('-10px');
        $ty = Length::parse('-20px');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(-10px,-20px)', $transform->toString());
    }

    public function testToStringMixedValues(): void
    {
        $tx = Length::parse('10px');
        $ty = Length::parse('-20px');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(10px,-20px)', $transform->toString());
    }

    public function testToStringDifferentUnits(): void
    {
        $tx = Length::parse('2em');
        $ty = Length::parse('3%');
        $transform = new TranslateTransform($tx, $ty);

        $this->assertEquals('translate(2em,3%)', $transform->toString());
    }

    public function testToStringTyZeroWithUnit(): void
    {
        $tx = Length::parse('10px');
        $ty = Length::parse('0px');
        $transform = new TranslateTransform($tx, $ty);

        // ty has a unit (px), so it should NOT be omitted
        $this->assertEquals('translate(10px,0px)', $transform->toString());
    }
}
