<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Visitor\VisitorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractElement::class)]
final class AbstractElementAttributeAndVisitorTest extends TestCase
{
    public function testSetAttributeThrowsOnProtectedAttribute(): void
    {
        $element = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('test', ['protected-attr']);
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot modify protected attribute: protected-attr');

        $element->setAttribute('protected-attr', 'value');
    }

    public function testRemoveAttributeThrowsOnProtectedAttribute(): void
    {
        $element = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('test', ['protected-attr']);
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot remove protected attribute: protected-attr');

        $element->removeAttribute('protected-attr');
    }

    public function testAcceptDelegatesToSpecificVisitMethod(): void
    {
        $rect = new RectElement();
        $visitor = new class implements VisitorInterface {
            public bool $specificCalled = false;

            public function visit(ElementInterface $element): mixed
            {
                return 'generic';
            }

            public function visitRectElement(RectElement $element): mixed
            {
                $this->specificCalled = true;

                return 'specific';
            }
        };

        $result = $rect->accept($visitor);

        $this->assertSame('specific', $result);
        $this->assertTrue($visitor->specificCalled);
    }

    public function testAcceptFallsBackToGenericVisitMethod(): void
    {
        $rect = new RectElement();
        $visitor = new class implements VisitorInterface {
            public function visit(ElementInterface $element): mixed
            {
                return 'generic';
            }
        };

        $result = $rect->accept($visitor);

        $this->assertSame('generic', $result);
    }

    public function testBboxReturnsBoundingBoxCalculator(): void
    {
        $rect = new RectElement();

        $bbox = $rect->bbox();

        $this->assertInstanceOf(\Atelier\Svg\Geometry\BoundingBoxCalculator::class, $bbox);
    }

    public function testCropReturnsDocumentCropper(): void
    {
        $rect = new RectElement();

        $crop = $rect->crop();

        $this->assertInstanceOf(\Atelier\Svg\Document\DocumentCropper::class, $crop);
    }
}
