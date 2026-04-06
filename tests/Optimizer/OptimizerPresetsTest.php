<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Optimizer\OptimizerPresets;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptimizerPresets::class)]
final class OptimizerPresetsTest extends TestCase
{
    public function testDefaultPresetReturnsArray(): void
    {
        $passes = OptimizerPresets::default();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testAggressivePresetReturnsArray(): void
    {
        $passes = OptimizerPresets::aggressive();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testSafePresetReturnsArray(): void
    {
        $passes = OptimizerPresets::safe();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testAccessiblePresetReturnsArray(): void
    {
        $passes = OptimizerPresets::accessible();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testAggressiveHasMorePassesThanSafe(): void
    {
        $aggressive = OptimizerPresets::aggressive();
        $safe = OptimizerPresets::safe();

        // Aggressive should have more or equal passes
        $this->assertGreaterThanOrEqual(count($safe), count($aggressive));
    }

    public function testGetDefaultPreset(): void
    {
        $passes = OptimizerPresets::get('default');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetAggressivePreset(): void
    {
        $passes = OptimizerPresets::get('aggressive');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetSafePreset(): void
    {
        $passes = OptimizerPresets::get('safe');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetAccessiblePreset(): void
    {
        $passes = OptimizerPresets::get('accessible');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetWithUnknownPresetThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown preset 'unknown'");

        OptimizerPresets::get('unknown');
    }

    public function testGetIsCaseInsensitive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Should be case-sensitive
        OptimizerPresets::get('DEFAULT');
    }

    public function testAllPresetsReturnDifferentInstances(): void
    {
        $default1 = OptimizerPresets::default();
        $default2 = OptimizerPresets::default();

        // Should return new instances each time
        $this->assertNotSame($default1, $default2);
        $this->assertNotSame($default1[0], $default2[0]);
    }
}
