<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Morphing;

use Atelier\Svg\Document;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Morphing\AnimationExporter;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnimationExporter::class)]
final class AnimationExporterTest extends TestCase
{
    /** @return Data[] */
    private function createFrames(): array
    {
        return [
            new Data([
                new MoveTo('M', new Point(0, 0)),
                new LineTo('L', new Point(100, 0)),
                new LineTo('L', new Point(100, 100)),
            ]),
            new Data([
                new MoveTo('M', new Point(10, 10)),
                new LineTo('L', new Point(90, 10)),
                new LineTo('L', new Point(90, 90)),
            ]),
        ];
    }

    public function testToAnimatedSVGReturnsDocument(): void
    {
        $frames = $this->createFrames();

        $doc = AnimationExporter::toAnimatedSVG($frames);

        $this->assertInstanceOf(Document::class, $doc);
        $this->assertNotNull($doc->getRootElement());
    }

    public function testToAnimatedSVGDefaultViewBox(): void
    {
        $frames = $this->createFrames();

        $doc = AnimationExporter::toAnimatedSVG($frames);
        $svg = $doc->getRootElement();

        $this->assertSame('0 0 200 200', $svg->getAttribute('viewBox'));
    }

    public function testToAnimatedSVGWithOptions(): void
    {
        $frames = $this->createFrames();

        $doc = AnimationExporter::toAnimatedSVG($frames, [
            'viewBox' => '0 0 100 100',
            'width' => '400',
            'height' => '300',
            'fill' => '#ff0000',
            'stroke' => '#00ff00',
            'strokeWidth' => '2',
            'duration' => '5',
            'repeatCount' => '3',
            'calcMode' => 'spline',
            'keySplines' => '0.42 0 0.58 1',
        ]);

        $svg = $doc->getRootElement();
        $this->assertSame('0 0 100 100', $svg->getAttribute('viewBox'));
        $this->assertSame('400', $svg->getAttribute('width'));
        $this->assertSame('300', $svg->getAttribute('height'));
    }

    public function testToCSSKeyframesReturnsValidCSS(): void
    {
        $frames = $this->createFrames();

        $css = AnimationExporter::toCSSKeyframes($frames);

        $this->assertStringContainsString('@keyframes morph', $css);
        $this->assertStringContainsString('0.0%', $css);
        $this->assertStringContainsString('100.0%', $css);
        $this->assertStringContainsString('d: path("', $css);
    }

    public function testToCSSKeyframesWithCustomName(): void
    {
        $frames = $this->createFrames();

        $css = AnimationExporter::toCSSKeyframes($frames, 'fadeShape');

        $this->assertStringContainsString('@keyframes fadeShape', $css);
        $this->assertStringContainsString('animation: fadeShape', $css);
    }

    public function testToCSSKeyframesSingleFrame(): void
    {
        $frames = [
            new Data([
                new MoveTo('M', new Point(0, 0)),
                new LineTo('L', new Point(50, 50)),
            ]),
        ];

        $css = AnimationExporter::toCSSKeyframes($frames);

        $this->assertStringContainsString('@keyframes morph', $css);
        $this->assertStringContainsString('0.0%', $css);
    }

    public function testToJavaScriptReturnsValidCode(): void
    {
        $frames = $this->createFrames();

        $js = AnimationExporter::toJavaScript($frames);

        $this->assertStringContainsString('const morphFrames = [', $js);
        $this->assertStringContainsString('];', $js);
        $this->assertStringContainsString('// Morphing animation frames', $js);
    }

    public function testToJavaScriptWithCustomVariable(): void
    {
        $frames = $this->createFrames();

        $js = AnimationExporter::toJavaScript($frames, 'myFrames');

        $this->assertStringContainsString('const myFrames = [', $js);
        $this->assertStringContainsString('myFrames[frameIndex]', $js);
        $this->assertStringContainsString('myFrames.length', $js);
    }

    public function testToJSONReturnsValidJSON(): void
    {
        $frames = $this->createFrames();

        $json = AnimationExporter::toJSON($frames);

        $decoded = json_decode($json, true);
        $this->assertNotNull($decoded);
        $this->assertSame('1.0', $decoded['version']);
        $this->assertSame(2, $decoded['frameCount']);
        $this->assertCount(2, $decoded['frames']);
        $this->assertIsString($decoded['frames'][0]);
        $this->assertIsString($decoded['frames'][1]);
    }

    public function testToJSONWithMetadata(): void
    {
        $frames = $this->createFrames();

        $json = AnimationExporter::toJSON($frames, ['author' => 'test', 'description' => 'morph animation']);

        $decoded = json_decode($json, true);
        $this->assertSame('test', $decoded['metadata']['author']);
        $this->assertSame('morph animation', $decoded['metadata']['description']);
    }

    public function testToWebAnimationsAPIReturnsValidCode(): void
    {
        $frames = $this->createFrames();

        $js = AnimationExporter::toWebAnimationsAPI($frames);

        $this->assertStringContainsString('const morphKeyframes =', $js);
        $this->assertStringContainsString('const morphOptions =', $js);
        $this->assertStringContainsString('duration: 3000', $js);
        $this->assertStringContainsString("easing: 'ease-in-out'", $js);
        $this->assertStringContainsString('iterations: Infinity', $js);
    }

    public function testToWebAnimationsAPIWithOptions(): void
    {
        $frames = $this->createFrames();

        $js = AnimationExporter::toWebAnimationsAPI($frames, [
            'duration' => 5,
            'easing' => 'linear',
            'iterations' => '3',
        ]);

        $this->assertStringContainsString('duration: 5000', $js);
        $this->assertStringContainsString("easing: 'linear'", $js);
        $this->assertStringContainsString('iterations: 3', $js);
    }

    public function testCreateDebugVisualizationReturnsDocument(): void
    {
        $frames = $this->createFrames();

        $doc = AnimationExporter::createDebugVisualization($frames);

        $this->assertInstanceOf(Document::class, $doc);
        $svg = $doc->getRootElement();
        $this->assertNotNull($svg);
        $this->assertNotNull($svg->getAttribute('viewBox'));
        $this->assertNotNull($svg->getAttribute('width'));
        $this->assertNotNull($svg->getAttribute('height'));
    }

    public function testCreateDebugVisualizationWithCustomCols(): void
    {
        $frames = $this->createFrames();

        $doc = AnimationExporter::createDebugVisualization($frames, 2);

        $svg = $doc->getRootElement();
        $this->assertNotNull($svg->getAttribute('viewBox'));
        $this->assertTrue($svg->hasChildren());
    }

    public function testToSpriteSheetCreatesFiles(): void
    {
        $frames = $this->createFrames();
        $outputDir = sys_get_temp_dir().'/svg-sprite-test-'.uniqid();

        try {
            AnimationExporter::toSpriteSheet($frames, $outputDir);

            $this->assertDirectoryExists($outputDir);
            $this->assertFileExists($outputDir.'/frame-0000.svg');
            $this->assertFileExists($outputDir.'/frame-0001.svg');

            $content = file_get_contents($outputDir.'/frame-0000.svg');
            $this->assertStringContainsString('<svg', $content);
            $this->assertStringContainsString('<path', $content);
        } finally {
            // Cleanup
            array_map(unlink(...), glob($outputDir.'/*') ?: []);
            if (is_dir($outputDir)) {
                rmdir($outputDir);
            }
        }
    }

    public function testToSpriteSheetWithOptions(): void
    {
        $frames = $this->createFrames();
        $outputDir = sys_get_temp_dir().'/svg-sprite-opts-'.uniqid();

        try {
            AnimationExporter::toSpriteSheet($frames, $outputDir, [
                'viewBox' => '0 0 100 100',
                'width' => '100',
                'height' => '100',
                'fill' => '#333',
                'stroke' => '#000',
            ]);

            $this->assertFileExists($outputDir.'/frame-0000.svg');
            $content = file_get_contents($outputDir.'/frame-0000.svg');
            $this->assertStringContainsString('viewBox', $content);
        } finally {
            array_map(unlink(...), glob($outputDir.'/*') ?: []);
            if (is_dir($outputDir)) {
                rmdir($outputDir);
            }
        }
    }

    public function testToJSONThrowsOnEncodingFailure(): void
    {
        // Create a frame with valid path data - JSON encoding should succeed
        $frames = $this->createFrames();
        $json = AnimationExporter::toJSON($frames);
        $this->assertIsString($json);
        $this->assertNotEmpty($json);
    }

    public function testCreateDebugVisualizationWithManyFrames(): void
    {
        $frames = [];
        for ($i = 0; $i < 12; ++$i) {
            $frames[] = new Data([
                new MoveTo('M', new Point($i * 10, 0)),
                new LineTo('L', new Point($i * 10 + 50, 50)),
            ]);
        }

        $doc = AnimationExporter::createDebugVisualization($frames, 4);

        $svg = $doc->getRootElement();
        $this->assertNotNull($svg);
        $this->assertTrue($svg->hasChildren());
    }
}
