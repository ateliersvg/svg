<?php

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Value\TransformList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Transform API methods on AbstractElement.
 */
#[CoversClass(AbstractElement::class)]
final class ElementTransformApiTest extends TestCase
{
    public function testGetTransformReturnsTransformList(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20) rotate(45)');

        $transformList = $element->getTransform();

        $this->assertInstanceOf(TransformList::class, $transformList);
        $this->assertCount(2, $transformList->getTransforms());
    }

    public function testGetTransformReturnsEmptyListWhenNoTransform(): void
    {
        $element = new RectElement();

        $transformList = $element->getTransform();

        $this->assertInstanceOf(TransformList::class, $transformList);
        $this->assertTrue($transformList->isEmpty());
    }

    public function testSetTransformWithString(): void
    {
        $element = new RectElement();

        $element->setTransform('translate(10,20) rotate(45)');

        $this->assertStringContainsString('translate', $element->getAttribute('transform'));
        $this->assertStringContainsString('rotate', $element->getAttribute('transform'));
    }

    public function testSetTransformWithTransformList(): void
    {
        $element = new RectElement();
        $transformList = TransformList::parse('translate(10,20) scale(2)');

        $element->setTransform($transformList);

        $this->assertStringContainsString('translate', $element->getAttribute('transform'));
        $this->assertStringContainsString('scale', $element->getAttribute('transform'));
    }

    public function testSetTransformRemovesAttributeWhenEmpty(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $element->setTransform(TransformList::parse(''));

        $this->assertNull($element->getAttribute('transform'));
    }

    public function testApplyTransformWithString(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $element->applyTransform('rotate(45)');

        $transform = $element->getAttribute('transform');
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('rotate', $transform);
    }

    public function testApplyTransformWithTransformList(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $element->applyTransform(TransformList::parse('scale(2)'));

        $transform = $element->getAttribute('transform');
        $this->assertStringContainsString('translate', $transform);
        $this->assertStringContainsString('scale', $transform);
    }

    public function testApplyTransformComposesTransforms(): void
    {
        $element = new RectElement();

        $element->setTransform('translate(10,20)');
        $element->applyTransform('rotate(45)');
        $element->applyTransform('scale(2)');

        $transformList = $element->getTransform();
        $this->assertCount(3, $transformList->getTransforms());
    }

    public function testClearTransform(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20) rotate(45)');

        $element->clearTransform();

        $this->assertNull($element->getAttribute('transform'));
    }

    public function testClearTransformWhenNoTransform(): void
    {
        $element = new RectElement();

        $element->clearTransform();

        $this->assertNull($element->getAttribute('transform'));
    }

    public function testGetTransformMatrix(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $matrix = $element->getTransformMatrix();

        $this->assertEquals(10, $matrix->e);
        $this->assertEquals(20, $matrix->f);
    }

    public function testGetTransformMatrixIdentityWhenNoTransform(): void
    {
        $element = new RectElement();

        $matrix = $element->getTransformMatrix();

        $this->assertTrue($matrix->isIdentity());
    }

    public function testSetTransformChaining(): void
    {
        $element = new RectElement();

        $result = $element->setTransform('translate(10,20)');

        $this->assertSame($element, $result);
        $this->assertNotNull($element->getAttribute('transform'));
    }

    public function testApplyTransformChaining(): void
    {
        $element = new RectElement();

        $result = $element->applyTransform('translate(10,20)');

        $this->assertSame($element, $result);
        $this->assertNotNull($element->getAttribute('transform'));
    }

    public function testClearTransformChaining(): void
    {
        $element = new RectElement();
        $element->setAttribute('transform', 'translate(10,20)');

        $result = $element->clearTransform();

        $this->assertSame($element, $result);
        $this->assertNull($element->getAttribute('transform'));
    }
}
