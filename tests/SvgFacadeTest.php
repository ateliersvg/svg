<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests;

use Atelier\Svg\Document;
use Atelier\Svg\Sanitizer\SanitizeProfile;
use Atelier\Svg\Svg;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for the Svg Facade class.
 */
#[CoversClass(Svg::class)]
final class SvgFacadeTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/svg-test-'.uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->tempDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testLoadFromFile(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10" width="50" height="50"/></svg>';
        $filePath = $this->tempDir.'/test.svg';
        file_put_contents($filePath, $svgContent);

        $svg = Svg::load($filePath);

        $this->assertInstanceOf(Svg::class, $svg);
        $this->assertInstanceOf(Document::class, $svg->getDocument());
    }

    public function testFromString(): void
    {
        $svgContent = '<svg width="100" height="100"><circle cx="50" cy="50" r="40"/></svg>';
        $svg = Svg::fromString($svgContent);

        $this->assertInstanceOf(Svg::class, $svg);
        $this->assertInstanceOf(Document::class, $svg->getDocument());
    }

    public function testCreate(): void
    {
        $svg = Svg::create(800, 600);

        $this->assertInstanceOf(Svg::class, $svg);
        $document = $svg->getDocument();
        $this->assertNotNull($document);
        $this->assertNotNull($svg->getBuilder());
    }

    public function testFromDocument(): void
    {
        $document = Document::create(300, 200);
        $svg = Svg::fromDocument($document);

        $this->assertInstanceOf(Svg::class, $svg);
        $this->assertSame($document, $svg->getDocument());
    }

    public function testRectWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->rect(10, 20, 100, 80, ['fill' => '#ff0000']);

        $document = $svg->getDocument();
        $root = $document->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $rect = $children[0];
        $this->assertEquals('rect', $rect->getTagName());
        $this->assertEquals('#ff0000', $rect->getAttribute('fill'));
    }

    public function testCircleWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->circle(100, 100, 50, ['fill' => '#00ff00']);

        $document = $svg->getDocument();
        $root = $document->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $circle = $children[0];
        $this->assertEquals('circle', $circle->getTagName());
        $this->assertEquals('#00ff00', $circle->getAttribute('fill'));
    }

    public function testPathWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->path('M 10 10 L 50 50', ['stroke' => '#0000ff']);

        $document = $svg->getDocument();
        $root = $document->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $path = $children[0];
        $this->assertEquals('path', $path->getTagName());
        $this->assertEquals('#0000ff', $path->getAttribute('stroke'));
    }

    public function testBuilderMethodsThrowExceptionWhenNotCreated(): void
    {
        $svgContent = '<svg width="100" height="100"></svg>';
        $svg = Svg::fromString($svgContent);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Builder methods are only available for SVGs created with Svg::create(). For loaded SVGs, use getDocument() to access the full API.');

        $svg->rect(10, 10, 50, 50);
    }

    public function testQuerySelector(): void
    {
        $svgContent = '<svg><rect class="test" x="10" y="10"/></svg>';
        $svg = Svg::fromString($svgContent);

        $element = $svg->querySelector('.test');
        $this->assertNotNull($element);
        $this->assertEquals('rect', $element->getTagName());
    }

    public function testQuerySelectorAll(): void
    {
        $svgContent = '<svg><rect class="shape"/><circle class="shape"/></svg>';
        $svg = Svg::fromString($svgContent);

        $collection = $svg->querySelectorAll('.shape');
        $this->assertEquals(2, $collection->count());
    }

    public function testOptimize(): void
    {
        $svgContent = '<svg width="100.00000" height="100.00000"><rect x="10.00000" y="10.00000"/></svg>';
        $filePath = $this->tempDir.'/input.svg';
        file_put_contents($filePath, $svgContent);

        $svg = Svg::load($filePath);
        $svg->optimize();

        $output = $svg->toString();
        // Optimization should have cleaned up the excessive precision
        $this->assertStringNotContainsString('100.00000', $output);
    }

    public function testOptimizeAggressive(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $filePath = $this->tempDir.'/input.svg';
        file_put_contents($filePath, $svgContent);

        $svg = Svg::load($filePath);
        $svg->optimizeAggressive();

        $this->assertInstanceOf(Svg::class, $svg);
    }

    public function testOptimizeSafe(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $filePath = $this->tempDir.'/input.svg';
        file_put_contents($filePath, $svgContent);

        $svg = Svg::load($filePath);
        $svg->optimizeSafe();

        $this->assertInstanceOf(Svg::class, $svg);
    }

    public function testOptimizeAccessible(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $filePath = $this->tempDir.'/input.svg';
        file_put_contents($filePath, $svgContent);

        $svg = Svg::load($filePath);
        $svg->optimizeAccessible();

        $this->assertInstanceOf(Svg::class, $svg);
    }

    public function testToString(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $svg = Svg::fromString($svgContent);

        $output = $svg->toString();
        $this->assertIsString($output);
        $this->assertStringContainsString('<svg', $output);
        $this->assertStringContainsString('rect', $output);
    }

    public function testToPrettyString(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $svg = Svg::fromString($svgContent);

        $output = $svg->toPrettyString();
        $this->assertIsString($output);
        $this->assertStringContainsString('<svg', $output);
        // Pretty output should have newlines
        $this->assertStringContainsString("\n", $output);
    }

    public function testSave(): void
    {
        $svg = Svg::create(200, 200);
        $outputPath = $this->tempDir.'/output.svg';

        $svg->save($outputPath);

        $this->assertFileExists($outputPath);
        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('<svg', $content);
    }

    public function testSavePretty(): void
    {
        $svg = Svg::create(200, 200);
        $outputPath = $this->tempDir.'/output-pretty.svg';

        $svg->savePretty($outputPath);

        $this->assertFileExists($outputPath);
        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('<svg', $content);
        $this->assertStringContainsString("\n", $content);
    }

    public function testMagicToString(): void
    {
        $svgContent = '<svg width="100" height="100"><rect x="10" y="10"/></svg>';
        $svg = Svg::fromString($svgContent);

        $output = (string) $svg;
        $this->assertIsString($output);
        $this->assertStringContainsString('<svg', $output);
    }

    public function testFluentChaining(): void
    {
        $outputPath = $this->tempDir.'/chained.svg';

        Svg::create(400, 300)
            ->rect(10, 10, 100, 100, ['fill' => '#ff0000'])
            ->circle(200, 150, 50, ['fill' => '#00ff00'])
            ->optimize()
            ->save($outputPath);

        $this->assertFileExists($outputPath);
        $content = file_get_contents($outputPath);
        $this->assertStringContainsString('rect', $content);
        $this->assertStringContainsString('circle', $content);
    }

    public function testLoadOptimizeSaveWorkflow(): void
    {
        $svgContent = '<svg width="100.00000" height="100.00000"><rect x="10" y="10"/></svg>';
        $inputPath = $this->tempDir.'/input.svg';
        $outputPath = $this->tempDir.'/output.svg';
        file_put_contents($inputPath, $svgContent);

        Svg::load($inputPath)
            ->optimize()
            ->save($outputPath);

        $this->assertFileExists($outputPath);
        $content = file_get_contents($outputPath);
        // Should be optimized
        $this->assertStringNotContainsString('100.00000', $content);
    }

    public function testSanitizeRemovesScripts(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/><script>alert("xss")</script></svg>';
        $svg = Svg::fromString($svgContent);

        $result = $svg->sanitize();

        $this->assertInstanceOf(Svg::class, $result);
        $output = $svg->toString();
        $this->assertStringNotContainsString('<script', $output);
        $this->assertStringContainsString('<circle', $output);
    }

    public function testSanitizeWithStrictProfile(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100"/><foreignObject width="200" height="200"></foreignObject></svg>';
        $svg = Svg::fromString($svgContent);

        $svg->sanitize(SanitizeProfile::STRICT);

        $output = $svg->toString();
        $this->assertStringNotContainsString('foreignObject', $output);
        $this->assertStringContainsString('<rect', $output);
    }

    public function testSanitizeWithPermissiveProfile(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" onclick="doStuff()"/></svg>';
        $svg = Svg::fromString($svgContent);

        $svg->sanitize(SanitizeProfile::PERMISSIVE);

        $output = $svg->toString();
        $this->assertStringContainsString('onclick', $output);
    }

    public function testSanitizeReturnsSelfForChaining(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100"/></svg>';
        $svg = Svg::fromString($svgContent);

        $result = $svg->sanitize();

        $this->assertSame($svg, $result);
    }

    public function testToDataUriBase64(): void
    {
        $svg = Svg::create(100, 100);

        $dataUri = $svg->toDataUri();

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $encoded = substr($dataUri, strlen('data:image/svg+xml;base64,'));
        $decoded = base64_decode($encoded, true);
        $this->assertNotFalse($decoded);
        $this->assertStringContainsString('<svg', $decoded);
    }

    public function testToDataUriUrlEncoded(): void
    {
        $svg = Svg::create(100, 100);

        $dataUri = $svg->toDataUri(false);

        $this->assertStringStartsWith('data:image/svg+xml,', $dataUri);
        $encoded = substr($dataUri, strlen('data:image/svg+xml,'));
        $decoded = rawurldecode($encoded);
        $this->assertStringContainsString('<svg', $decoded);
    }

    public function testToDataUriDoesNotIncludeXmlDeclaration(): void
    {
        $svg = Svg::create(100, 100);

        $dataUri = $svg->toDataUri();

        $encoded = substr($dataUri, strlen('data:image/svg+xml;base64,'));
        $decoded = base64_decode($encoded, true);
        $this->assertStringNotContainsString('<?xml', $decoded);
    }

    public function testEllipseWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->ellipse(100, 100, 60, 40, ['fill' => '#ff0000']);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('ellipse', $children[0]->getTagName());
        $this->assertEquals('#ff0000', $children[0]->getAttribute('fill'));
    }

    public function testLineWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->line(0, 0, 100, 100, ['stroke' => '#000']);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('line', $children[0]->getTagName());
        $this->assertEquals('#000', $children[0]->getAttribute('stroke'));
    }

    public function testPolygonWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->polygon('0,0 100,0 100,100', ['fill' => '#00ff00']);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('polygon', $children[0]->getTagName());
        $this->assertEquals('#00ff00', $children[0]->getAttribute('fill'));
    }

    public function testPolylineWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->polyline('0,0 50,50 100,0', ['stroke' => '#0000ff']);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('polyline', $children[0]->getTagName());
        $this->assertEquals('#0000ff', $children[0]->getAttribute('stroke'));
    }

    public function testTextWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->text(10, 50, 'Hello', ['fill' => '#333']);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('text', $children[0]->getTagName());
        $this->assertEquals('#333', $children[0]->getAttribute('fill'));
    }

    public function testImageWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->image('logo.png', 10, 20, 100, 50);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('image', $children[0]->getTagName());
        $this->assertEquals('logo.png', $children[0]->getAttribute('href'));
    }

    public function testDefsWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->defs();

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('defs', $children[0]->getTagName());
    }

    public function testUseElementWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->useElement('#myRect', 50, 50);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('use', $children[0]->getTagName());
        $this->assertEquals('#myRect', $children[0]->getAttribute('href'));
    }

    public function testLinearGradientWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->linearGradient('grad1', 0, 0, 1, 1);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('linearGradient', $children[0]->getTagName());
        $this->assertEquals('grad1', $children[0]->getAttribute('id'));
    }

    public function testRadialGradientWithCreate(): void
    {
        $svg = Svg::create(400, 300)
            ->radialGradient('grad2', 50, 50, 25);

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(1, $children);
        $this->assertEquals('radialGradient', $children[0]->getTagName());
        $this->assertEquals('grad2', $children[0]->getAttribute('id'));
    }

    public function testFillWithCreate(): void
    {
        $svg = Svg::create(400, 300);
        $result = $svg->fill('#ff0000');

        $this->assertSame($svg, $result);
    }

    public function testStrokeWithCreate(): void
    {
        $svg = Svg::create(400, 300);
        $result = $svg->stroke('#000000');

        $this->assertSame($svg, $result);
    }

    public function testStrokeWidthWithCreate(): void
    {
        $svg = Svg::create(400, 300);
        $result = $svg->strokeWidth(2);

        $this->assertSame($svg, $result);
    }

    public function testOpacityWithCreate(): void
    {
        $svg = Svg::create(400, 300);
        $result = $svg->opacity(0.5);

        $this->assertSame($svg, $result);
    }

    public function testStylingMethodsThrowExceptionWhenNotCreated(): void
    {
        $svg = Svg::fromString('<svg width="100" height="100"></svg>');

        $this->expectException(\RuntimeException::class);
        $svg->fill('#000');
    }

    public function testShapeMethodsChaining(): void
    {
        $svg = Svg::create(800, 600)
            ->ellipse(100, 100, 60, 40)
            ->line(0, 0, 200, 200)
            ->polygon('50,0 100,100 0,100')
            ->polyline('10,10 50,50 90,10')
            ->text(10, 50, 'Hello');

        $root = $svg->getDocument()->getRootElement();
        $children = $root->getChildren();

        $this->assertCount(5, $children);
        $this->assertEquals('ellipse', $children[0]->getTagName());
        $this->assertEquals('line', $children[1]->getTagName());
        $this->assertEquals('polygon', $children[2]->getTagName());
        $this->assertEquals('polyline', $children[3]->getTagName());
        $this->assertEquals('text', $children[4]->getTagName());
    }

    public function testSanitizeOptimizeSaveWorkflow(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="100.00000" height="100.00000"><rect width="50" height="50"/><script>alert("xss")</script></svg>';
        $outputPath = $this->tempDir.'/sanitized.svg';

        Svg::fromString($svgContent)
            ->sanitize()
            ->optimize()
            ->save($outputPath);

        $this->assertFileExists($outputPath);
        $content = file_get_contents($outputPath);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('100.00000', $content);
        $this->assertStringContainsString('rect', $content);
    }
}
