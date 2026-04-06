<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Validation\ValidationProfile;
use Atelier\Svg\Validation\ValidationSeverity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationProfile::class)]
final class ValidationProfileTest extends TestCase
{
    public function testStrictProfileCreation(): void
    {
        $profile = ValidationProfile::strict();

        $this->assertTrue($profile->isEnabled('check_required_attributes'));
        $this->assertTrue($profile->isEnabled('check_attribute_types'));
        $this->assertTrue($profile->isEnabled('check_references'));
        $this->assertFalse($profile->isEnabled('allow_deprecated'));
    }

    public function testLenientProfileCreation(): void
    {
        $profile = ValidationProfile::lenient();

        $this->assertTrue($profile->isEnabled('check_required_attributes'));
        $this->assertFalse($profile->isEnabled('check_attribute_types'));
        $this->assertTrue($profile->isEnabled('allow_deprecated'));
        $this->assertTrue($profile->isEnabled('allow_unknown_elements'));
    }

    public function testAccessibleProfileCreation(): void
    {
        $profile = ValidationProfile::accessible();

        $this->assertTrue($profile->isEnabled('check_accessibility'));
        $this->assertTrue($profile->isEnabled('require_title'));
        $this->assertTrue($profile->isEnabled('check_aria_attributes'));
    }

    public function testCustomProfileCreation(): void
    {
        $profile = ValidationProfile::custom([
            'check_references' => true,
            'custom_option' => 'value',
        ]);

        $this->assertTrue($profile->isEnabled('check_references'));
        $this->assertEquals('value', $profile->get('custom_option'));
    }

    public function testGetOption(): void
    {
        $profile = ValidationProfile::strict();

        $this->assertTrue($profile->get('check_references'));
        $this->assertNull($profile->get('nonexistent'));
        $this->assertEquals('default', $profile->get('nonexistent', 'default'));
    }

    public function testIsEnabled(): void
    {
        $profile = ValidationProfile::strict();

        $this->assertTrue($profile->isEnabled('check_references'));
        $this->assertFalse($profile->isEnabled('nonexistent'));
    }

    public function testGetOptions(): void
    {
        $profile = ValidationProfile::strict();
        $options = $profile->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('check_references', $options);
        $this->assertArrayHasKey('check_duplicate_ids', $options);
    }

    public function testWithOverrides(): void
    {
        $profile = ValidationProfile::strict();
        $modified = $profile->with([
            'allow_deprecated' => true,
            'custom_setting' => 'test',
        ]);

        // Original profile unchanged
        $this->assertFalse($profile->isEnabled('allow_deprecated'));

        // New profile has overrides
        $this->assertTrue($modified->isEnabled('allow_deprecated'));
        $this->assertEquals('test', $modified->get('custom_setting'));

        // New profile still has original options
        $this->assertTrue($modified->isEnabled('check_references'));
    }

    public function testStrictProfileSeverityLevels(): void
    {
        $profile = ValidationProfile::strict();

        $this->assertEquals(
            ValidationSeverity::ERROR,
            $profile->get('severity_missing_required')
        );
        $this->assertEquals(
            ValidationSeverity::ERROR,
            $profile->get('severity_broken_reference')
        );
    }

    public function testLenientProfileSeverityLevels(): void
    {
        $profile = ValidationProfile::lenient();

        $this->assertEquals(
            ValidationSeverity::WARNING,
            $profile->get('severity_missing_required')
        );
        $this->assertEquals(
            ValidationSeverity::ERROR,
            $profile->get('severity_broken_reference')
        );
    }

    public function testAccessibleProfileRequirements(): void
    {
        $profile = ValidationProfile::accessible();

        $this->assertTrue($profile->isEnabled('require_viewbox'));
        $this->assertTrue($profile->isEnabled('require_title'));
        $this->assertTrue($profile->isEnabled('require_alt_text'));
    }
}
