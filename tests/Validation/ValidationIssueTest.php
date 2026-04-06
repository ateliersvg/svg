<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Validation\ValidationIssue;
use Atelier\Svg\Validation\ValidationSeverity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationIssue::class)]
final class ValidationIssueTest extends TestCase
{
    public function testErrorFactory(): void
    {
        $issue = ValidationIssue::error('test_code', 'Test message');

        $this->assertEquals(ValidationSeverity::ERROR, $issue->severity);
        $this->assertEquals('test_code', $issue->code);
        $this->assertEquals('Test message', $issue->message);
    }

    public function testWarningFactory(): void
    {
        $issue = ValidationIssue::warning('test_code', 'Test message');

        $this->assertEquals(ValidationSeverity::WARNING, $issue->severity);
    }

    public function testInfoFactory(): void
    {
        $issue = ValidationIssue::info('test_code', 'Test message');

        $this->assertEquals(ValidationSeverity::INFO, $issue->severity);
    }

    public function testWithElement(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'test-rect');

        $issue = ValidationIssue::error(
            'test',
            'Test message',
            $element
        );

        $this->assertSame($element, $issue->element);
    }

    public function testWithAttribute(): void
    {
        $issue = ValidationIssue::error(
            'test',
            'Test message',
            null,
            'fill'
        );

        $this->assertEquals('fill', $issue->attribute);
    }

    public function testWithValue(): void
    {
        $issue = ValidationIssue::error(
            'test',
            'Test message',
            null,
            'fill',
            '#invalid'
        );

        $this->assertEquals('#invalid', $issue->value);
    }

    public function testWithMetadata(): void
    {
        $metadata = ['count' => 5, 'type' => 'test'];

        $issue = ValidationIssue::error(
            'test',
            'Test message',
            null,
            null,
            null,
            $metadata
        );

        $this->assertEquals($metadata, $issue->metadata);
    }

    public function testToArray(): void
    {
        $issue = ValidationIssue::error('test_code', 'Test message');
        $array = $issue->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('error', $array['severity']);
        $this->assertEquals('test_code', $array['code']);
        $this->assertEquals('Test message', $array['message']);
    }

    public function testToArrayWithElement(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'my-rect');

        $issue = ValidationIssue::error('test', 'Message', $element);
        $array = $issue->toArray();

        $this->assertArrayHasKey('element', $array);
        $this->assertEquals('rect', $array['element']['tag']);
        $this->assertEquals('my-rect', $array['element']['id']);
    }

    public function testToArrayWithAttribute(): void
    {
        $issue = ValidationIssue::error('test', 'Message', null, 'fill');
        $array = $issue->toArray();

        $this->assertArrayHasKey('attribute', $array);
        $this->assertEquals('fill', $array['attribute']);
    }

    public function testToArrayWithMetadata(): void
    {
        $metadata = ['extra' => 'data'];
        $issue = ValidationIssue::error('test', 'Message', null, null, null, $metadata);
        $array = $issue->toArray();

        $this->assertArrayHasKey('metadata', $array);
        $this->assertEquals($metadata, $array['metadata']);
    }

    public function testFormat(): void
    {
        $issue = ValidationIssue::error('test_code', 'Test message');
        $formatted = $issue->format();

        $this->assertIsString($formatted);
        $this->assertStringContainsString('[ERROR]', $formatted);
        $this->assertStringContainsString('Test message', $formatted);
    }

    public function testFormatWithElement(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'my-rect');

        $issue = ValidationIssue::error('test', 'Message', $element);
        $formatted = $issue->format();

        $this->assertStringContainsString('my-rect', $formatted);
        $this->assertStringContainsString('rect', $formatted);
    }

    public function testFormatWithAttribute(): void
    {
        $issue = ValidationIssue::error('test', 'Message', null, 'fill');
        $formatted = $issue->format();

        $this->assertStringContainsString('fill', $formatted);
    }

    public function testFormatWarning(): void
    {
        $issue = ValidationIssue::warning('test', 'Message');
        $formatted = $issue->format();

        $this->assertStringContainsString('[WARN]', $formatted);
    }

    public function testFormatInfo(): void
    {
        $issue = ValidationIssue::info('test', 'Message');
        $formatted = $issue->format();

        $this->assertStringContainsString('[INFO]', $formatted);
    }

    public function testToArrayWithValue(): void
    {
        $issue = ValidationIssue::error('test', 'Message', null, 'fill', '#ff0000');
        $array = $issue->toArray();

        $this->assertArrayHasKey('value', $array);
        $this->assertSame('#ff0000', $array['value']);
    }
}
