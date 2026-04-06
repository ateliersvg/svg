<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Element\Attributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase
{
    public function testConstants(): void
    {
        $this->assertSame('id', Attributes::ID);
        $this->assertSame('class', Attributes::CLASS_NAME);
        $this->assertSame('fill', Attributes::FILL);
        $this->assertSame('stroke', Attributes::STROKE);
        $this->assertSame('transform', Attributes::TRANSFORM);
        $this->assertSame('viewBox', Attributes::VIEWBOX);
        $this->assertSame('href', Attributes::HREF);
    }

    public function testIsPresentationAttribute(): void
    {
        $this->assertTrue(Attributes::isPresentationAttribute('fill'));
        $this->assertTrue(Attributes::isPresentationAttribute('stroke'));
        $this->assertTrue(Attributes::isPresentationAttribute('opacity'));
        $this->assertTrue(Attributes::isPresentationAttribute('font-size'));

        $this->assertFalse(Attributes::isPresentationAttribute('id'));
        $this->assertFalse(Attributes::isPresentationAttribute('viewBox'));
        $this->assertFalse(Attributes::isPresentationAttribute('transform'));
    }

    public function testNormalize(): void
    {
        $this->assertSame('fill', Attributes::normalize('fill'));
        $this->assertSame('fill-opacity', Attributes::normalize('fillOpacity'));
        $this->assertSame('stroke-width', Attributes::normalize('strokeWidth'));
        $this->assertSame('view-box', Attributes::normalize('viewBox'));
    }

    public function testIsGeometricAttribute(): void
    {
        $this->assertTrue(Attributes::isGeometricAttribute('x'));
        $this->assertTrue(Attributes::isGeometricAttribute('y'));
        $this->assertTrue(Attributes::isGeometricAttribute('width'));
        $this->assertTrue(Attributes::isGeometricAttribute('height'));
        $this->assertTrue(Attributes::isGeometricAttribute('cx'));
        $this->assertTrue(Attributes::isGeometricAttribute('cy'));
        $this->assertTrue(Attributes::isGeometricAttribute('r'));

        $this->assertFalse(Attributes::isGeometricAttribute('fill'));
        $this->assertFalse(Attributes::isGeometricAttribute('id'));
        $this->assertFalse(Attributes::isGeometricAttribute('transform'));
    }
}
