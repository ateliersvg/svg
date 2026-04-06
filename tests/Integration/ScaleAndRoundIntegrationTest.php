<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Integration;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\PolygonElement;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;
use Atelier\Svg\Optimizer\Pass\ScaleCoordinatesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScaleCoordinatesPass::class)]
final class ScaleAndRoundIntegrationTest extends TestCase
{
    public function testScaleThenRoundProducesIntegerCoordinates(): void
    {
        $svgContent = <<<SVG
<svg viewBox="0 0 1.5 2.5">
  <path d="M 0.1 0.2 L 0.3 0.4" stroke-width="0.05" stroke-dasharray="0.1 0.2" />
  <polygon points="0,0 0.5,1 1,0" />
</svg>
SVG;

        $loader = new DomLoader();
        $document = $loader->loadFromString($svgContent);

        $optimizer = new Optimizer([
            new ScaleCoordinatesPass(10.0),
            new RoundValuesPass(0),
        ]);

        $optimizer->optimize($document);

        $root = $document->getRootElement();
        $this->assertNotNull($root);
        $this->assertSame('0 0 15 25', $root->getAttribute('viewBox'));

        $path = null;
        $polygon = null;

        foreach ($root->getChildren() as $child) {
            if ($child instanceof PathElement) {
                $path = $child;
            }

            if ($child instanceof PolygonElement) {
                $polygon = $child;
            }
        }

        $this->assertInstanceOf(PathElement::class, $path);
        $this->assertInstanceOf(PolygonElement::class, $polygon);

        $this->assertSame('M 1,2 L 3,4', $path->getPathData());
        $this->assertSame('1', $path->getAttribute('stroke-width'));
        $this->assertSame('1 2', $path->getAttribute('stroke-dasharray'));

        $this->assertSame('0 0 5 10 10 0', $polygon->getAttribute('points'));
    }
}
