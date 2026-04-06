<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeTileElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeTileElement::class)]
final class FeTileElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeTileElement();

        $this->assertSame('feTile', $element->getTagName());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeTileElement();
        $result = $element->setIn('SourceGraphic');

        $this->assertSame($element, $result);
        $this->assertSame('SourceGraphic', $element->getIn());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeTileElement();

        $this->assertNull($element->getIn());
    }
}
