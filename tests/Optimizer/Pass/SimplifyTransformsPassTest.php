<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\SimplifyTransformsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimplifyTransformsPass::class)]
final class SimplifyTransformsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new SimplifyTransformsPass();

        $this->assertSame('simplify-transforms', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new SimplifyTransformsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveIdentityTranslate(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'translate(0, 0)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity translate should be removed
        $this->assertFalse($rect->hasAttribute('transform'));
    }

    public function testRemoveIdentityScale(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'scale(1, 1)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity scale should be removed
        $this->assertFalse($rect->hasAttribute('transform'));
    }

    public function testRemoveIdentityRotate(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'rotate(0)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity rotate should be removed
        $this->assertFalse($rect->hasAttribute('transform'));
    }

    public function testRemoveIdentityMatrix(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'matrix(1, 0, 0, 1, 0, 0)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity matrix should be removed
        $this->assertFalse($rect->hasAttribute('transform'));
    }

    public function testSimplifyTransformNumbers(): void
    {
        $pass = new SimplifyTransformsPass(precision: 2);
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'translate(10.000, 20.500)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
        // Trailing zeros should be removed
        $this->assertStringNotContainsString('10.000', $transform);
        $this->assertStringContainsString('10', $transform);
        $this->assertStringContainsString('20.5', $transform);
    }

    public function testPreserveNonIdentityTransform(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'translate(10, 20)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Non-identity transform should be preserved
        $this->assertTrue($rect->hasAttribute('transform'));
        $this->assertNotNull($rect->getAttribute('transform'));
    }

    public function testDisableRemoveDefaults(): void
    {
        $pass = new SimplifyTransformsPass(removeDefaults: false);
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'translate(0, 0)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity transform should NOT be removed when removeDefaults is false
        $this->assertTrue($rect->hasAttribute('transform'));
    }

    public function testSimplifyWithCustomPrecision(): void
    {
        $pass = new SimplifyTransformsPass(precision: 1);
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', 'translate(10.123456, 20.987654)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
        // Should be rounded to 1 decimal place
        $this->assertStringContainsString('10.1', $transform);
        $this->assertStringContainsString('21', $transform); // 20.987654 rounds to 21.0 -> 21
    }

    public function testHandleEmptyTransform(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $rect->setAttribute('transform', '');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Empty transform should be removed
        $this->assertFalse($rect->hasAttribute('transform'));
    }

    public function testSimplifyNestedElements(): void
    {
        $pass = new SimplifyTransformsPass();
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('transform', 'translate(0, 0)');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('transform', 'scale(1)');
        $svg->appendChild($rect2);

        $rect3 = new RectElement();
        $rect3->setAttribute('transform', 'translate(10, 20)');
        $svg->appendChild($rect3);

        $document = new Document($svg);
        $pass->optimize($document);

        // Identity transforms should be removed
        $this->assertFalse($rect1->hasAttribute('transform'));
        $this->assertFalse($rect2->hasAttribute('transform'));

        // Non-identity should be preserved
        $this->assertTrue($rect3->hasAttribute('transform'));
    }

    public function testConvertIntegerWhenWhole(): void
    {
        $pass = new SimplifyTransformsPass(precision: 3);
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('transform', 'translate(10.0, 20.0)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $transform = $rect->getAttribute('transform');
        $this->assertNotNull($transform);
        // Should convert to integers
        $this->assertStringContainsString('translate(10', $transform);
        $this->assertStringNotContainsString('10.0', $transform);
    }
}
