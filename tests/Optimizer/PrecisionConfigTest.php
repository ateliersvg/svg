<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Optimizer\PrecisionConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrecisionConfig::class)]
final class PrecisionConfigTest extends TestCase
{
    public function testConstantsAreDefined(): void
    {
        // Verify all constants are defined and have reasonable values
        $this->assertIsInt(PrecisionConfig::COORDINATE_DEFAULT);
        $this->assertIsInt(PrecisionConfig::COORDINATE_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::COORDINATE_SAFE);

        $this->assertIsInt(PrecisionConfig::DIMENSION_DEFAULT);
        $this->assertIsInt(PrecisionConfig::DIMENSION_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::DIMENSION_SAFE);

        $this->assertIsInt(PrecisionConfig::TRANSFORM_DEFAULT);
        $this->assertIsInt(PrecisionConfig::TRANSFORM_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::TRANSFORM_SAFE);

        $this->assertIsInt(PrecisionConfig::PATH_DEFAULT);
        $this->assertIsInt(PrecisionConfig::PATH_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::PATH_SAFE);

        $this->assertIsInt(PrecisionConfig::OPACITY_DEFAULT);
        $this->assertIsInt(PrecisionConfig::OPACITY_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::OPACITY_SAFE);

        $this->assertIsInt(PrecisionConfig::CLEANUP_DEFAULT);
        $this->assertIsInt(PrecisionConfig::CLEANUP_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::CLEANUP_SAFE);

        $this->assertIsInt(PrecisionConfig::ANGLE_DEFAULT);
        $this->assertIsInt(PrecisionConfig::ANGLE_AGGRESSIVE);
        $this->assertIsInt(PrecisionConfig::ANGLE_SAFE);

        $this->assertIsInt(PrecisionConfig::MIN_PRECISION);
        $this->assertIsInt(PrecisionConfig::MAX_PRECISION);
    }

    public function testPrecisionValuesAreWithinBounds(): void
    {
        // All precision constants should be within MIN and MAX
        $constants = [
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::COORDINATE_AGGRESSIVE,
            PrecisionConfig::COORDINATE_SAFE,
            PrecisionConfig::DIMENSION_DEFAULT,
            PrecisionConfig::DIMENSION_AGGRESSIVE,
            PrecisionConfig::DIMENSION_SAFE,
            PrecisionConfig::TRANSFORM_DEFAULT,
            PrecisionConfig::TRANSFORM_AGGRESSIVE,
            PrecisionConfig::TRANSFORM_SAFE,
            PrecisionConfig::PATH_DEFAULT,
            PrecisionConfig::PATH_AGGRESSIVE,
            PrecisionConfig::PATH_SAFE,
            PrecisionConfig::OPACITY_DEFAULT,
            PrecisionConfig::OPACITY_AGGRESSIVE,
            PrecisionConfig::OPACITY_SAFE,
            PrecisionConfig::CLEANUP_DEFAULT,
            PrecisionConfig::CLEANUP_AGGRESSIVE,
            PrecisionConfig::CLEANUP_SAFE,
            PrecisionConfig::ANGLE_DEFAULT,
            PrecisionConfig::ANGLE_AGGRESSIVE,
            PrecisionConfig::ANGLE_SAFE,
        ];

        foreach ($constants as $value) {
            $this->assertGreaterThanOrEqual(PrecisionConfig::MIN_PRECISION, $value);
            $this->assertLessThanOrEqual(PrecisionConfig::MAX_PRECISION, $value);
        }
    }

    public function testPrecisionHierarchy(): void
    {
        // For each category, safe >= default >= aggressive
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::COORDINATE_SAFE
        );
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_AGGRESSIVE,
            PrecisionConfig::COORDINATE_DEFAULT
        );

        $this->assertGreaterThanOrEqual(
            PrecisionConfig::TRANSFORM_DEFAULT,
            PrecisionConfig::TRANSFORM_SAFE
        );
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::TRANSFORM_AGGRESSIVE,
            PrecisionConfig::TRANSFORM_DEFAULT
        );

        $this->assertGreaterThanOrEqual(
            PrecisionConfig::PATH_DEFAULT,
            PrecisionConfig::PATH_SAFE
        );
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::PATH_AGGRESSIVE,
            PrecisionConfig::PATH_DEFAULT
        );
    }

    public function testTransformPrecisionIsHigherThanCoordinate(): void
    {
        // Transforms need higher precision due to multiplication
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::TRANSFORM_DEFAULT
        );
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_AGGRESSIVE,
            PrecisionConfig::TRANSFORM_AGGRESSIVE
        );
    }

    public function testValidateWithinBounds(): void
    {
        $this->assertSame(3, PrecisionConfig::validate(3));
        $this->assertSame(0, PrecisionConfig::validate(0));
        $this->assertSame(6, PrecisionConfig::validate(6));
    }

    public function testValidateBelowMinimum(): void
    {
        $this->assertSame(0, PrecisionConfig::validate(-5));
        $this->assertSame(0, PrecisionConfig::validate(-1));
    }

    public function testValidateAboveMaximum(): void
    {
        $this->assertSame(6, PrecisionConfig::validate(10));
        $this->assertSame(6, PrecisionConfig::validate(100));
    }

    public function testForPresetDefault(): void
    {
        $config = PrecisionConfig::forPreset('default');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('coordinate', $config);
        $this->assertArrayHasKey('dimension', $config);
        $this->assertArrayHasKey('transform', $config);
        $this->assertArrayHasKey('path', $config);
        $this->assertArrayHasKey('opacity', $config);
        $this->assertArrayHasKey('cleanup', $config);
        $this->assertArrayHasKey('angle', $config);

        $this->assertSame(PrecisionConfig::COORDINATE_DEFAULT, $config['coordinate']);
        $this->assertSame(PrecisionConfig::DIMENSION_DEFAULT, $config['dimension']);
        $this->assertSame(PrecisionConfig::TRANSFORM_DEFAULT, $config['transform']);
        $this->assertSame(PrecisionConfig::PATH_DEFAULT, $config['path']);
        $this->assertSame(PrecisionConfig::OPACITY_DEFAULT, $config['opacity']);
        $this->assertSame(PrecisionConfig::CLEANUP_DEFAULT, $config['cleanup']);
        $this->assertSame(PrecisionConfig::ANGLE_DEFAULT, $config['angle']);
    }

    public function testForPresetAggressive(): void
    {
        $config = PrecisionConfig::forPreset('aggressive');

        $this->assertSame(PrecisionConfig::COORDINATE_AGGRESSIVE, $config['coordinate']);
        $this->assertSame(PrecisionConfig::DIMENSION_AGGRESSIVE, $config['dimension']);
        $this->assertSame(PrecisionConfig::TRANSFORM_AGGRESSIVE, $config['transform']);
        $this->assertSame(PrecisionConfig::PATH_AGGRESSIVE, $config['path']);
        $this->assertSame(PrecisionConfig::OPACITY_AGGRESSIVE, $config['opacity']);
        $this->assertSame(PrecisionConfig::CLEANUP_AGGRESSIVE, $config['cleanup']);
        $this->assertSame(PrecisionConfig::ANGLE_AGGRESSIVE, $config['angle']);
    }

    public function testForPresetSafe(): void
    {
        $config = PrecisionConfig::forPreset('safe');

        $this->assertSame(PrecisionConfig::COORDINATE_SAFE, $config['coordinate']);
        $this->assertSame(PrecisionConfig::DIMENSION_SAFE, $config['dimension']);
        $this->assertSame(PrecisionConfig::TRANSFORM_SAFE, $config['transform']);
        $this->assertSame(PrecisionConfig::PATH_SAFE, $config['path']);
        $this->assertSame(PrecisionConfig::OPACITY_SAFE, $config['opacity']);
        $this->assertSame(PrecisionConfig::CLEANUP_SAFE, $config['cleanup']);
        $this->assertSame(PrecisionConfig::ANGLE_SAFE, $config['angle']);
    }

    public function testForPresetWeb(): void
    {
        $config = PrecisionConfig::forPreset('web');

        // Web preset uses same numeric precision as aggressive
        $aggressiveConfig = PrecisionConfig::forPreset('aggressive');
        $this->assertSame($aggressiveConfig, $config);
    }

    public function testForPresetWithUnknownPresetThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown preset 'unknown'");

        PrecisionConfig::forPreset('unknown');
    }

    public function testDefaultPresetIsBalanced(): void
    {
        // Default should be balanced - not too aggressive, not too conservative
        $this->assertSame(2, PrecisionConfig::COORDINATE_DEFAULT);
        $this->assertSame(3, PrecisionConfig::TRANSFORM_DEFAULT);
        $this->assertSame(3, PrecisionConfig::PATH_DEFAULT);
    }

    public function testAggressivePresetIsLessPrecise(): void
    {
        // Aggressive should use less precision than default
        $this->assertLessThan(
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::COORDINATE_AGGRESSIVE
        );
    }

    public function testSafePresetIsMorePrecise(): void
    {
        // Safe should use more precision than default
        $this->assertGreaterThan(
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::COORDINATE_SAFE
        );
    }

    public function testMinMaxBounds(): void
    {
        $this->assertSame(0, PrecisionConfig::MIN_PRECISION);
        $this->assertSame(6, PrecisionConfig::MAX_PRECISION);
    }

    public function testCleanupPrecisionIsHigherThanRounding(): void
    {
        // Cleanup pass should have slightly higher precision as safety margin
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_DEFAULT,
            PrecisionConfig::CLEANUP_DEFAULT
        );
        $this->assertGreaterThanOrEqual(
            PrecisionConfig::COORDINATE_AGGRESSIVE,
            PrecisionConfig::CLEANUP_AGGRESSIVE
        );
    }
}
