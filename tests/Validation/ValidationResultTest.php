<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Validation\ValidationIssue;
use Atelier\Svg\Validation\ValidationResult;
use Atelier\Svg\Validation\ValidationSeverity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationResult::class)]
final class ValidationResultTest extends TestCase
{
    public function testEmptyResult(): void
    {
        $result = new ValidationResult();

        $this->assertTrue($result->isValid());
        $this->assertFalse($result->hasIssues());
        $this->assertEquals(0, $result->count());
    }

    public function testAddIssue(): void
    {
        $result = new ValidationResult();
        $issue = ValidationIssue::error('test_code', 'Test message');

        $result->addIssue($issue);

        $this->assertEquals(1, $result->count());
        $this->assertTrue($result->hasIssues());
    }

    public function testIsValidWithErrors(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('test', 'Error'));

        $this->assertFalse($result->isValid());
    }

    public function testIsValidWithWarnings(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::warning('test', 'Warning'));

        $this->assertTrue($result->isValid()); // Warnings don't affect validity
    }

    public function testGetErrors(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('e1', 'Error 1'));
        $result->addIssue(ValidationIssue::warning('w1', 'Warning 1'));
        $result->addIssue(ValidationIssue::error('e2', 'Error 2'));

        $errors = $result->getErrors();

        $this->assertCount(2, $errors);
        $errorArray = array_values($errors);
        $this->assertEquals('e1', $errorArray[0]->code);
        $this->assertEquals('e2', $errorArray[1]->code);
    }

    public function testGetWarnings(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('e1', 'Error 1'));
        $result->addIssue(ValidationIssue::warning('w1', 'Warning 1'));
        $result->addIssue(ValidationIssue::warning('w2', 'Warning 2'));

        $warnings = $result->getWarnings();

        $this->assertCount(2, $warnings);
        $warningArray = array_values($warnings);
        $this->assertEquals('w1', $warningArray[0]->code);
    }

    public function testGetInfo(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::info('i1', 'Info 1'));
        $result->addIssue(ValidationIssue::error('e1', 'Error 1'));

        $info = $result->getInfo();

        $this->assertCount(1, $info);
        $this->assertEquals('i1', $info[0]->code);
    }

    public function testGetCounts(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('e1', 'Error 1'));
        $result->addIssue(ValidationIssue::error('e2', 'Error 2'));
        $result->addIssue(ValidationIssue::warning('w1', 'Warning 1'));
        $result->addIssue(ValidationIssue::info('i1', 'Info 1'));

        $counts = $result->getCounts();

        $this->assertEquals(2, $counts['errors']);
        $this->assertEquals(1, $counts['warnings']);
        $this->assertEquals(1, $counts['info']);
    }

    public function testMerge(): void
    {
        $result1 = new ValidationResult();
        $result1->addIssue(ValidationIssue::error('e1', 'Error 1'));

        $result2 = new ValidationResult();
        $result2->addIssue(ValidationIssue::warning('w1', 'Warning 1'));

        $result1->merge($result2);

        $this->assertEquals(2, $result1->count());
        $this->assertCount(1, $result1->getErrors());
        $this->assertCount(1, $result1->getWarnings());
    }

    public function testToArray(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('test', 'Test error'));

        $array = $result->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('valid', $array);
        $this->assertArrayHasKey('counts', $array);
        $this->assertArrayHasKey('issues', $array);
        $this->assertFalse($array['valid']);
    }

    public function testFormat(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('test', 'Test error'));

        $formatted = $result->format();

        $this->assertIsString($formatted);
        $this->assertStringContainsString('error', strtolower($formatted));
    }

    public function testFormatEmpty(): void
    {
        $result = new ValidationResult();
        $formatted = $result->format();

        $this->assertStringContainsString('no issues', strtolower($formatted));
    }

    public function testConstructorWithIssues(): void
    {
        $issues = [
            ValidationIssue::error('e1', 'Error 1'),
            ValidationIssue::warning('w1', 'Warning 1'),
        ];

        $result = new ValidationResult($issues);

        $this->assertEquals(2, $result->count());
    }

    public function testGetIssuesBySeverity(): void
    {
        $result = new ValidationResult();
        $result->addIssue(ValidationIssue::error('e1', 'Error'));
        $result->addIssue(ValidationIssue::warning('w1', 'Warning'));
        $result->addIssue(ValidationIssue::info('i1', 'Info'));

        $errors = $result->getIssuesBySeverity(ValidationSeverity::ERROR);
        $warnings = $result->getIssuesBySeverity(ValidationSeverity::WARNING);
        $info = $result->getIssuesBySeverity(ValidationSeverity::INFO);

        $this->assertCount(1, $errors);
        $this->assertCount(1, $warnings);
        $this->assertCount(1, $info);
    }
}
