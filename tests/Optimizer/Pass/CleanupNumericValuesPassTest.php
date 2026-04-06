<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\CleanupNumericValuesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleanupNumericValuesPass::class)]
final class CleanupNumericValuesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new CleanupNumericValuesPass();
        $this->assertSame('cleanup-numeric-values', $pass->getName());
    }

    public function testRemovesTrailingZeros(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.00000');
        $rect->setAttribute('y', '20.50000');
        $rect->setAttribute('width', '100.0');
        $rect->setAttribute('height', '50.000');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass(precision: 3);
        $pass->optimize($document);

        $this->assertSame('10', $rect->getAttribute('x'));
        $this->assertSame('20.5', $rect->getAttribute('y'));
        $this->assertSame('100', $rect->getAttribute('width'));
        $this->assertSame('50', $rect->getAttribute('height'));
    }

    public function testRemovesLeadingZero(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('opacity', '0.5');
        $rect->setAttribute('stroke-opacity', '0.75');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass(removeLeadingZero: true);
        $pass->optimize($document);

        $this->assertSame('.5', $rect->getAttribute('opacity'));
        $this->assertSame('.75', $rect->getAttribute('stroke-opacity'));
    }

    public function testPreservesLeadingZeroWhenDisabled(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('opacity', '0.5');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass(removeLeadingZero: false);
        $pass->optimize($document);

        $this->assertSame('0.5', $rect->getAttribute('opacity'));
    }

    public function testRoundsToSpecifiedPrecision(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '10.123456');
        $rect->setAttribute('y', '20.987654');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass(precision: 2);
        $pass->optimize($document);

        $this->assertSame('10.12', $rect->getAttribute('x'));
        $this->assertSame('20.99', $rect->getAttribute('y'));
    }

    public function testCleansUpViewBox(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.00 0.00 100.00 50.00');

        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $this->assertSame('0 0 100 50', $svg->getAttribute('viewBox'));
    }

    public function testCleansUpPoints(): void
    {
        $svg = new SvgElement();
        $polygon = new PolygonElement();
        $polygon->setAttribute('points', '0.00,0.00 100.00,0.00 50.00,50.00');

        $svg->appendChild($polygon);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $this->assertSame('0 0 100 0 50 50', $polygon->getAttribute('points'));
    }

    public function testCleansUpPathData(): void
    {
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('d', 'M 10.00 20.00 L 30.00 40.00');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $d = $path->getAttribute('d');
        $this->assertStringContainsString('10', $d);
        $this->assertStringContainsString('20', $d);
        $this->assertStringContainsString('30', $d);
        $this->assertStringContainsString('40', $d);
    }

    public function testHandlesNegativeNumbers(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('x', '-10.50000');
        $rect->setAttribute('y', '-0.75');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass(removeLeadingZero: true);
        $pass->optimize($document);

        $this->assertSame('-10.5', $rect->getAttribute('x'));
        $this->assertSame('-.75', $rect->getAttribute('y'));
    }

    public function testPreservesNonNumericAttributes(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $rect->setAttribute('fill', 'red');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $this->assertSame('my-rect', $rect->getAttribute('id'));
        $this->assertSame('red', $rect->getAttribute('fill'));
    }

    public function testHandlesCircleAttributes(): void
    {
        $svg = new SvgElement();
        $circle = new CircleElement();
        $circle->setAttribute('cx', '50.00');
        $circle->setAttribute('cy', '50.00');
        $circle->setAttribute('r', '25.50');

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $this->assertSame('50', $circle->getAttribute('cx'));
        $this->assertSame('50', $circle->getAttribute('cy'));
        $this->assertSame('25.5', $circle->getAttribute('r'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new CleanupNumericValuesPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testViewBoxWithNonNumericPart(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 abc 100');

        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        // Non-numeric parts should be preserved as-is
        $viewBox = $svg->getAttribute('viewBox');
        $this->assertNotNull($viewBox);
        $this->assertStringContainsString('abc', $viewBox);
    }

    public function testCompoundAttributeWithoutAttribute(): void
    {
        $svg = new SvgElement();
        // No viewBox attribute at all
        $document = new Document($svg);

        $pass = new CleanupNumericValuesPass();
        $pass->optimize($document);

        $this->assertNull($svg->getAttribute('viewBox'));
    }
}
