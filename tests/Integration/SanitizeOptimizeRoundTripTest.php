<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Svg;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Svg::class)]
final class SanitizeOptimizeRoundTripTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/svg-roundtrip-'.uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testLoadSanitizeOptimizeSaveRoundTrip(): void
    {
        $input = '<svg xmlns="http://www.w3.org/2000/svg" width="200.00000" height="200.00000">'
            .'<script>alert("xss")</script>'
            .'<rect onclick="steal()" fill="red" width="100.00000" height="100.00000"/>'
            .'<circle cx="150" cy="150" r="30" fill="blue"/>'
            .'</svg>';

        $inputPath = $this->tempDir.'/input.svg';
        $outputPath = $this->tempDir.'/output.svg';
        file_put_contents($inputPath, $input);

        Svg::load($inputPath)
            ->sanitize()
            ->optimize()
            ->save($outputPath);

        $this->assertFileExists($outputPath);
        $output = file_get_contents($outputPath);

        // Security: no malicious content
        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringNotContainsString('alert', $output);

        // Optimization: reduced precision
        $this->assertStringNotContainsString('100.00000', $output);

        // Structure preserved
        $this->assertStringContainsString('rect', $output);
        $this->assertStringContainsString('circle', $output);

        // Valid XML
        $dom = new \DOMDocument();
        $this->assertTrue(@$dom->loadXML($output));
    }

    public function testCreateSanitizeExportToDataUri(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="50" height="50" fill="red"/><script>bad()</script></svg>';

        $dataUri = Svg::fromString($svg)
            ->sanitize()
            ->toDataUri();

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $decoded = base64_decode(substr($dataUri, strlen('data:image/svg+xml;base64,')), true);
        $this->assertStringNotContainsString('<script', $decoded);
        $this->assertStringContainsString('<rect', $decoded);
    }

    public function testSanitizeOptimizePrettyPrint(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><g><rect fill="red" width="100" height="100" onclick="xss()"/></g></svg>';

        $output = Svg::fromString($svg)
            ->sanitize()
            ->optimize()
            ->toPrettyString();

        $this->assertStringNotContainsString('onclick', $output);
        $this->assertStringContainsString("\n", $output);
        $this->assertStringContainsString('<rect', $output);
    }

    public function testSanitizePreservesGradientsAndDefs(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">'
            .'<defs><linearGradient id="grad1"><stop offset="0%" stop-color="red"/></linearGradient></defs>'
            .'<rect fill="url(#grad1)" width="100" height="50"/>'
            .'<script>track()</script>'
            .'</svg>';

        $output = Svg::fromString($svg)
            ->sanitize()
            ->toString();

        $this->assertStringContainsString('linearGradient', $output);
        $this->assertStringContainsString('id="grad1"', $output);
        $this->assertStringNotContainsString('<script', $output);
    }

    public function testSanitizePreservesAccessibilityElements(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg">'
            .'<title>Accessible Chart</title>'
            .'<desc>A bar chart showing sales data</desc>'
            .'<rect width="100" height="50" fill="blue"/>'
            .'<script>track()</script>'
            .'</svg>';

        $output = Svg::fromString($svg)
            ->sanitize()
            ->toString();

        $this->assertStringContainsString('<title>Accessible Chart</title>', $output);
        $this->assertStringContainsString('<desc>A bar chart showing sales data</desc>', $output);
        $this->assertStringNotContainsString('<script', $output);
    }
}
