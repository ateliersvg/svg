<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Validation\ValidationSeverity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationSeverity::class)]
final class ValidationSeverityTest extends TestCase
{
    public function testErrorCaseValue(): void
    {
        $this->assertSame('error', ValidationSeverity::ERROR->value);
    }

    public function testWarningCaseValue(): void
    {
        $this->assertSame('warning', ValidationSeverity::WARNING->value);
    }

    public function testInfoCaseValue(): void
    {
        $this->assertSame('info', ValidationSeverity::INFO->value);
    }

    public function testCanBeCreatedFromString(): void
    {
        $error = ValidationSeverity::from('error');
        $warning = ValidationSeverity::from('warning');
        $info = ValidationSeverity::from('info');

        $this->assertSame(ValidationSeverity::ERROR, $error);
        $this->assertSame(ValidationSeverity::WARNING, $warning);
        $this->assertSame(ValidationSeverity::INFO, $info);
    }

    public function testTryFromReturnsNullForInvalidValue(): void
    {
        $this->assertNull(ValidationSeverity::tryFrom('invalid'));
        $this->assertNull(ValidationSeverity::tryFrom('critical'));
        $this->assertNull(ValidationSeverity::tryFrom(''));
    }

    public function testAllCasesAreCovered(): void
    {
        $cases = ValidationSeverity::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ValidationSeverity::ERROR, $cases);
        $this->assertContains(ValidationSeverity::WARNING, $cases);
        $this->assertContains(ValidationSeverity::INFO, $cases);
    }

    public function testCasesOrder(): void
    {
        $cases = ValidationSeverity::cases();

        // Verify the order is ERROR, WARNING, INFO (severity descending)
        $this->assertSame(ValidationSeverity::ERROR, $cases[0]);
        $this->assertSame(ValidationSeverity::WARNING, $cases[1]);
        $this->assertSame(ValidationSeverity::INFO, $cases[2]);
    }

    public function testEnumCasesAreUnique(): void
    {
        $values = array_map(fn ($case) => $case->value, ValidationSeverity::cases());

        $this->assertCount(3, $values);
        $this->assertCount(3, array_unique($values));
    }
}
