<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AbstractElement ID management methods.
 *
 * Covers the setId() and getId() methods added for filter support.
 */
#[CoversClass(AbstractElement::class)]
final class AbstractElementIdTest extends TestCase
{
    private RectElement $element;

    protected function setUp(): void
    {
        $this->element = new RectElement();
    }

    public function testSetIdSetsTheIdAttribute(): void
    {
        $result = $this->element->setId('myElement');

        $this->assertSame($this->element, $result, 'setId should return $this for method chaining');
        $this->assertSame('myElement', $this->element->getAttribute('id'));
    }

    public function testGetIdReturnsTheIdAttribute(): void
    {
        $this->element->setAttribute('id', 'testId');

        $this->assertSame('testId', $this->element->getId());
    }

    public function testGetIdReturnsNullWhenIdNotSet(): void
    {
        $this->assertNull($this->element->getId());
    }

    public function testSetIdOverwritesExistingId(): void
    {
        $this->element->setId('firstId');
        $this->element->setId('secondId');

        $this->assertSame('secondId', $this->element->getId());
        $this->assertSame('secondId', $this->element->getAttribute('id'));
    }

    public function testSetIdCanBeChained(): void
    {
        $result = $this->element
            ->setId('chainedId')
            ->setX(10)
            ->setY(20);

        $this->assertSame($this->element, $result);
        $this->assertSame('chainedId', $this->element->getId());
        $this->assertSame('10', $this->element->getAttribute('x'));
        $this->assertSame('20', $this->element->getAttribute('y'));
    }

    public function testSetIdWithEmptyString(): void
    {
        $this->element->setId('');

        $this->assertSame('', $this->element->getId());
        $this->assertSame('', $this->element->getAttribute('id'));
    }

    public function testSetIdWithNumericString(): void
    {
        $this->element->setId('123');

        $this->assertSame('123', $this->element->getId());
    }

    public function testSetIdWithSpecialCharacters(): void
    {
        // SVG IDs can contain various characters
        $this->element->setId('my-element_123.test');

        $this->assertSame('my-element_123.test', $this->element->getId());
    }

    public function testGetIdAfterRemovingIdAttribute(): void
    {
        $this->element->setId('testId');
        $this->element->removeAttribute('id');

        $this->assertNull($this->element->getId());
    }

    public function testSetIdThenGetIdRoundTrip(): void
    {
        $ids = ['simple', 'with-dashes', 'with_underscores', 'with.dots', 'CamelCase', 'números123'];

        foreach ($ids as $id) {
            $this->element->setId($id);
            $this->assertSame($id, $this->element->getId(), "Round-trip failed for ID: {$id}");
        }
    }
}
