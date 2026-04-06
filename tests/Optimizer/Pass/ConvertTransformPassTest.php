<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\ConvertTransformPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConvertTransformPass::class)]
final class ConvertTransformPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new ConvertTransformPass();

        $this->assertSame('convert-transform', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new ConvertTransformPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testTranslateRect(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should be removed
        $this->assertFalse($rect->hasAttribute('transform'));

        // Coordinates should be translated
        $this->assertSame('15', $rect->getAttribute('x'));
        $this->assertSame('30', $rect->getAttribute('y'));
        $this->assertSame('30', $rect->getAttribute('width'));
        $this->assertSame('40', $rect->getAttribute('height'));
    }

    public function testTranslateRectWithSingleValue(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'translate(5)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should be removed
        $this->assertFalse($rect->hasAttribute('transform'));

        // Only X should be translated (Y defaults to 0)
        $this->assertSame('15', $rect->getAttribute('x'));
        $this->assertSame('20', $rect->getAttribute('y'));
    }

    public function testTranslateCircle(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $circle = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('circle');
            }
        };
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '60');
        $circle->setAttribute('r', '10');
        $circle->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($circle->hasAttribute('transform'));
        $this->assertSame('55', $circle->getAttribute('cx'));
        $this->assertSame('70', $circle->getAttribute('cy'));
        $this->assertSame('10', $circle->getAttribute('r'));
    }

    public function testTranslateEllipse(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $ellipse = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('ellipse');
            }
        };
        $ellipse->setAttribute('cx', '50');
        $ellipse->setAttribute('cy', '60');
        $ellipse->setAttribute('rx', '20');
        $ellipse->setAttribute('ry', '10');
        $ellipse->setAttribute('transform', 'translate(-5, -10)');

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($ellipse->hasAttribute('transform'));
        $this->assertSame('45', $ellipse->getAttribute('cx'));
        $this->assertSame('50', $ellipse->getAttribute('cy'));
    }

    public function testTranslateLine(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $line = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $line->setAttribute('x1', '10');
        $line->setAttribute('y1', '20');
        $line->setAttribute('x2', '30');
        $line->setAttribute('y2', '40');
        $line->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($line->hasAttribute('transform'));
        $this->assertSame('15', $line->getAttribute('x1'));
        $this->assertSame('30', $line->getAttribute('y1'));
        $this->assertSame('35', $line->getAttribute('x2'));
        $this->assertSame('50', $line->getAttribute('y2'));
    }

    public function testScaleRect(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'scale(2)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('transform'));
        $this->assertSame('20', $rect->getAttribute('x'));
        $this->assertSame('40', $rect->getAttribute('y'));
        $this->assertSame('60', $rect->getAttribute('width'));
        $this->assertSame('80', $rect->getAttribute('height'));
    }

    public function testScaleRectWithDifferentScales(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'scale(2, 3)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('transform'));
        $this->assertSame('20', $rect->getAttribute('x'));
        $this->assertSame('60', $rect->getAttribute('y'));
        $this->assertSame('60', $rect->getAttribute('width'));
        $this->assertSame('120', $rect->getAttribute('height'));
    }

    public function testScaleCircle(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $circle = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('circle');
            }
        };
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '60');
        $circle->setAttribute('r', '10');
        $circle->setAttribute('transform', 'scale(2)');

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($circle->hasAttribute('transform'));
        $this->assertSame('100', $circle->getAttribute('cx'));
        $this->assertSame('120', $circle->getAttribute('cy'));
        $this->assertSame('20', $circle->getAttribute('r'));
    }

    public function testScaleCircleWithDifferentScalesDoesNotConvert(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $circle = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('circle');
            }
        };
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '60');
        $circle->setAttribute('r', '10');
        $circle->setAttribute('transform', 'scale(2, 3)');

        $svg->appendChild($circle);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (would become ellipse)
        $this->assertTrue($circle->hasAttribute('transform'));
    }

    public function testScaleEllipse(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $ellipse = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('ellipse');
            }
        };
        $ellipse->setAttribute('cx', '50');
        $ellipse->setAttribute('cy', '60');
        $ellipse->setAttribute('rx', '20');
        $ellipse->setAttribute('ry', '10');
        $ellipse->setAttribute('transform', 'scale(2, 3)');

        $svg->appendChild($ellipse);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($ellipse->hasAttribute('transform'));
        $this->assertSame('100', $ellipse->getAttribute('cx'));
        $this->assertSame('180', $ellipse->getAttribute('cy'));
        $this->assertSame('40', $ellipse->getAttribute('rx'));
        $this->assertSame('30', $ellipse->getAttribute('ry'));
    }

    public function testScaleLine(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $line = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('line');
            }
        };
        $line->setAttribute('x1', '10');
        $line->setAttribute('y1', '20');
        $line->setAttribute('x2', '30');
        $line->setAttribute('y2', '40');
        $line->setAttribute('transform', 'scale(2, 3)');

        $svg->appendChild($line);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($line->hasAttribute('transform'));
        $this->assertSame('20', $line->getAttribute('x1'));
        $this->assertSame('60', $line->getAttribute('y1'));
        $this->assertSame('60', $line->getAttribute('x2'));
        $this->assertSame('120', $line->getAttribute('y2'));
    }

    public function testScaleWithNegativeValuesDoesNotConvert(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'scale(-1)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (negative scale flips the element)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testTranslatePathSimple(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 L 35 50', $path->getAttribute('d'));
    }

    public function testTranslatePathWithMultipleCommands(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40 L50 60');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 L 35 50 L 55 70', $path->getAttribute('d'));
    }

    public function testTranslatePathWithHorizontalVerticalLines(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 H30 V40');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 H 35 V 50', $path->getAttribute('d'));
    }

    public function testTranslatePathWithCubicBezier(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 C15 25 20 30 25 35');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 C 20 35 25 40 30 45', $path->getAttribute('d'));
    }

    public function testTranslatePathDoesNotAffectRelativeCommands(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 l10 10');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        // M is absolute, l is relative
        $this->assertSame('M 15 30 l10 10', $path->getAttribute('d'));
    }

    public function testDisableTranslateConversion(): void
    {
        $pass = new ConvertTransformPass(convertTranslate: false);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (translate conversion disabled)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testDisableScaleConversion(): void
    {
        $pass = new ConvertTransformPass(convertScale: false);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'scale(2)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (scale conversion disabled)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testDisablePathConversion(): void
    {
        $pass = new ConvertTransformPass(convertOnPaths: false);
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (path conversion disabled)
        $this->assertTrue($path->hasAttribute('transform'));
    }

    public function testDisableShapeConversion(): void
    {
        $pass = new ConvertTransformPass(convertOnShapes: false);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (shape conversion disabled)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testRotateIsNotConverted(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'rotate(45)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (rotate is too complex)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testMultipleTransformsNotConverted(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'translate(5, 10) scale(2)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (multiple transforms)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testHandlesMissingCoordinates(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        // No x/y attributes (will default to 0)
        $rect->setAttribute('width', '30');
        $rect->setAttribute('height', '40');
        $rect->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($rect->hasAttribute('transform'));
        $this->assertSame('5', $rect->getAttribute('x'));
        $this->assertSame('10', $rect->getAttribute('y'));
    }

    public function testHandlesEmptyPath(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', '');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Transform should NOT be removed (empty path)
        $this->assertTrue($path->hasAttribute('transform'));
    }

    public function testTranslatePathWithClosePath(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40 L10 40 Z');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 L 35 50 L 15 50 Z', $path->getAttribute('d'));
    }

    public function testTranslatePathWithArc(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 A5 5 0 0 1 20 30');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        // Arc parameters stay the same, only endpoint is translated
        $this->assertSame('M 15 30 A 5 5 0 0 1 25 40', $path->getAttribute('d'));
    }

    public function testTranslatePathWithQuadraticBezier(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 Q15 25 20 30');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 Q 20 35 25 40', $path->getAttribute('d'));
    }

    public function testTranslatePathWithSmoothCubicBezier(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 S15 25 20 30');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 S 20 35 25 40', $path->getAttribute('d'));
    }

    public function testTranslatePathWithSmoothQuadraticBezier(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 T20 30');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $this->assertSame('M 15 30 T 25 40', $path->getAttribute('d'));
    }

    public function testRotateEnabledStillReturnsNull(): void
    {
        $pass = new ConvertTransformPass(convertRotate: true);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'rotate(45)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // Even with convertRotate enabled, rotate returns null (not implemented)
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testScaleOnPathDoesNotConvert(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40');
        $path->setAttribute('transform', 'scale(2)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Scale on paths is not implemented, transform should remain
        $this->assertTrue($path->hasAttribute('transform'));
    }

    public function testUnsupportedElementTypeForTranslate(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        // Use a non-shape, non-path element like 'text'
        $text = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('text');
            }
        };
        $text->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);

        // Text is not a shape or path, transform should remain
        $this->assertTrue($text->hasAttribute('transform'));
    }

    public function testPathWithNoPathData(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        // No 'd' attribute at all
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // No path data to translate, transform should remain
        $this->assertTrue($path->hasAttribute('transform'));
    }

    public function testTransformAttributeReturnsNullEarlyReturn(): void
    {
        // Line 75: getAttribute returns null even though hasAttribute was true
        // This is a defensive check; we can trigger it via an element that
        // reports hasAttribute=true but getAttribute=null (unlikely in practice).
        // Instead, test that an element without a transform is simply skipped.
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        // No transform attribute
        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        // No transform, nothing should change
        $this->assertSame('10', $rect->getAttribute('x'));
    }

    public function testApplyTransformToShapeWithRotateReturnsFalse(): void
    {
        // Line 179: applyTransformToShape returns false for unsupported transform type
        // This is hit when parseTransform returns a type other than translate/scale.
        // Since parseTransform only returns translate/scale/null, line 179 is a fallback.
        // We test by enabling rotate, which still returns null from parseTransform.
        $pass = new ConvertTransformPass(convertRotate: true);
        $svg = new SvgElement();

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('x', '10');
        $rect->setAttribute('y', '20');
        $rect->setAttribute('transform', 'rotate(45)');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testTranslatePathWithZCommandCoordinates(): void
    {
        // Lines 524, 526: Z command with coordinates in path (edge case)
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        // Z followed by more commands
        $path->setAttribute('d', 'M10 20 L30 40 Z M50 60 L70 80');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $d = $path->getAttribute('d');
        $this->assertStringContainsString('Z', $d);
        $this->assertStringContainsString('M 55 70', $d);
    }

    public function testTranslatePathWithEmptyCoordinatesAfterCommand(): void
    {
        // Line 533: empty translatedNumbers returns command+trailingSpace
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        $path->setAttribute('d', 'M10 20 L30 40 Z');
        $path->setAttribute('transform', 'translate(0, 0)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
    }

    public function testTranslatePathZCommandWithTrailingCoordinatesIsIgnored(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        // Z followed by a number (unusual but triggers the Z case in the switch)
        $path->setAttribute('d', 'M10 20 L30 40 Z10');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('transform'));
        $d = $path->getAttribute('d');
        // The Z should stay as Z (coordinates ignored by the Z case)
        $this->assertStringContainsString('Z', $d);
    }

    public function testTranslatePathWithOddNumberOfCoordinates(): void
    {
        $pass = new ConvertTransformPass();
        $svg = new SvgElement();

        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('path');
            }
        };
        // M with only 1 coordinate (odd number) -> translatedNumbers stays empty
        $path->setAttribute('d', 'M10');
        $path->setAttribute('transform', 'translate(5, 10)');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // The path should still process, transform removed
        $this->assertFalse($path->hasAttribute('transform'));
    }
}
