<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ScaleCoordinatesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScaleCoordinatesPass::class)]
final class ScaleCoordinatesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ScaleCoordinatesPass();

        $this->assertSame('scale-coordinates', $pass->getName());
    }

    public function testGetScaleFactor(): void
    {
        $pass = new ScaleCoordinatesPass(5.0);

        $this->assertSame(5.0, $pass->getScaleFactor());
    }

    public function testConstructorRejectsNonPositiveFactor(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ScaleCoordinatesPass(0.0);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ScaleCoordinatesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testScalesNumericAttributes(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('x', '1.5');
        $svg->setAttribute('y', '2.5');
        $svg->setAttribute('width', '3');
        $svg->setAttribute('height', '4.25');
        $svg->setAttribute('stroke-width', '0.5');
        $svg->setAttribute('stroke-dashoffset', '1.25');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('3', $svg->getAttribute('x'));
        $this->assertSame('5', $svg->getAttribute('y'));
        $this->assertSame('6', $svg->getAttribute('width'));
        $this->assertSame('8.5', $svg->getAttribute('height'));
        $this->assertSame('1', $svg->getAttribute('stroke-width'));
        $this->assertSame('2.5', $svg->getAttribute('stroke-dashoffset'));
    }

    public function testScalesViewBoxAndPoints(): void
    {
        $pass = new ScaleCoordinatesPass(10.0);
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 1.5 2.5');

        $polygon = new PolygonElement();
        $polygon->setAttribute('points', '0,0 1.2,2.3 3.4,4.5');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 0 15 25', $svg->getAttribute('viewBox'));
        $this->assertSame('0 0 12 23 34 45', $polygon->getAttribute('points'));
    }

    public function testScalesPathData(): void
    {
        $pass = new ScaleCoordinatesPass(3.0);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('M 1 1 L 2 2');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('M 3,3 L 6,6', $path->getPathData());
    }

    public function testScalesStrokeDasharray(): void
    {
        $pass = new ScaleCoordinatesPass(4.0);
        $svg = new SvgElement();
        $svg->setAttribute('stroke-dasharray', '1, 2 3');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('4 8 12', $svg->getAttribute('stroke-dasharray'));
    }

    public function testScalesNestedElements(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->setAttribute('x', '1');
        $group->setAttribute('y', '2');
        $path->setPathData('M 1 2 L 3 4');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('2', $svg->getAttribute('x'));
        $this->assertSame('4', $group->getAttribute('y'));
        $this->assertSame('M 2,4 L 6,8', $path->getPathData());
    }

    public function testSkipsNonNumericAttributes(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('width', '50%');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('50%', $svg->getAttribute('width'));
    }

    public function testScalesViewBoxWithNonNumericPartIsSkipped(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 abc 100');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 0 abc 100', $svg->getAttribute('viewBox'));
    }

    public function testScalesViewBoxWithInvalidPartCount(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 100');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0 0 100', $svg->getAttribute('viewBox'));
    }

    public function testScalesEmptyViewBoxIsSkipped(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('', $svg->getAttribute('viewBox'));
    }

    public function testScalesPathDataWithEmptyPathIsSkipped(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('', $path->getPathData());
    }

    public function testScalesPointsSkipsEmptyNumbers(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setAttribute('points', '');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('', $polygon->getAttribute('points'));
    }

    public function testScalesStrokeDasharrayWithNoneIsSkipped(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $svg->setAttribute('stroke-dasharray', 'none');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('none', $svg->getAttribute('stroke-dasharray'));
    }

    public function testScalesPolylinePoints(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $polyline = new \Atelier\Svg\Element\Shape\PolylineElement();
        $polyline->setAttribute('points', '10 20 30 40');

        $svg->appendChild($polyline);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('20 40 60 80', $polyline->getAttribute('points'));
    }

    public function testScalesPathDataWithEmptySegmentsPath(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('M 10 20 L 30 40');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertNotNull($path->getPathData());
        $this->assertNotSame('M 10 20 L 30 40', $path->getPathData());
    }

    public function testScalesPointsWithLeadingTrailingCommas(): void
    {
        $pass = new ScaleCoordinatesPass(2.0);
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        // Leading/trailing commas produce empty strings in preg_split
        $polygon->setAttribute('points', ',10,,20,');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('20 40', $polygon->getAttribute('points'));
    }

    #[WithoutErrorHandler]
    public function testScalePathDataCatchesParseError(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new \RuntimeException($errstr, $errno);
        });

        try {
            $pass = new ScaleCoordinatesPass(2.0);
            $svg = new SvgElement();
            $path = new PathElement();
            $path->setPathData('M 0 0 C 1');

            $svg->appendChild($path);
            $document = new Document($svg);

            $pass->optimize($document);

            $this->assertSame('M 0 0 C 1', $path->getPathData());
        } finally {
            restore_error_handler();
        }
    }
}
