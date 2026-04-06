<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeComponentTransferElement;
use Atelier\Svg\Element\Filter\FeFuncRElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeComponentTransferElement::class)]
final class FeComponentTransferElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeComponentTransferElement();

        $this->assertSame('feComponentTransfer', $element->getTagName());
    }

    public function testCanContainFuncElements(): void
    {
        $transfer = new FeComponentTransferElement();
        $funcR = new FeFuncRElement();

        $transfer->appendChild($funcR);

        $this->assertTrue($transfer->hasChildren());
        $this->assertSame(1, $transfer->getChildCount());
    }

    public function testSetAndGetIn(): void
    {
        $element = new FeComponentTransferElement();
        $result = $element->setIn('SourceGraphic');

        $this->assertSame($element, $result);
        $this->assertSame('SourceGraphic', $element->getIn());
    }

    public function testSetAndGetResult(): void
    {
        $element = new FeComponentTransferElement();
        $result = $element->setResult('transfer');

        $this->assertSame($element, $result);
        $this->assertSame('transfer', $element->getResult());
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeComponentTransferElement();

        $this->assertNull($element->getIn());
        $this->assertNull($element->getResult());
    }
}
