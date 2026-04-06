<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Filter;

use Atelier\Svg\Element\Filter\FeConvolveMatrixElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FeConvolveMatrixElement::class)]
final class FeConvolveMatrixElementTest extends TestCase
{
    public function testGetTagName(): void
    {
        $element = new FeConvolveMatrixElement();

        $this->assertSame('feConvolveMatrix', $element->getTagName());
    }

    public function testSetAndGetOrder(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setOrder('3');

        $this->assertSame($element, $result);
        $this->assertSame('3', $element->getOrder());
    }

    public function testSetAndGetKernelMatrix(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setKernelMatrix('0 -1 0 -1 5 -1 0 -1 0');

        $this->assertSame($element, $result);
        $this->assertSame('0 -1 0 -1 5 -1 0 -1 0', $element->getKernelMatrix());
    }

    public function testSetAndGetDivisor(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setDivisor(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getDivisor());
    }

    public function testSetAndGetBias(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setBias(0.5);

        $this->assertSame($element, $result);
        $this->assertSame('0.5', $element->getBias());
    }

    public function testSetAndGetTargetX(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setTargetX(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getTargetX());
    }

    public function testSetAndGetTargetY(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setTargetY(1);

        $this->assertSame($element, $result);
        $this->assertSame('1', $element->getTargetY());
    }

    public function testSetAndGetEdgeMode(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setEdgeMode('duplicate');

        $this->assertSame($element, $result);
        $this->assertSame('duplicate', $element->getEdgeMode());
    }

    public function testSetAndGetPreserveAlpha(): void
    {
        $element = new FeConvolveMatrixElement();
        $result = $element->setPreserveAlpha(true);

        $this->assertSame($element, $result);
        $this->assertSame('true', $element->getPreserveAlpha());
    }

    public function testEdgeModes(): void
    {
        $element = new FeConvolveMatrixElement();

        $modes = ['duplicate', 'wrap', 'none'];
        foreach ($modes as $mode) {
            $element->setEdgeMode($mode);
            $this->assertSame($mode, $element->getEdgeMode());
        }
    }

    public function testGetAttributeWhenNotSet(): void
    {
        $element = new FeConvolveMatrixElement();

        $this->assertNull($element->getOrder());
        $this->assertNull($element->getKernelMatrix());
        $this->assertNull($element->getDivisor());
        $this->assertNull($element->getBias());
        $this->assertNull($element->getTargetX());
        $this->assertNull($element->getTargetY());
        $this->assertNull($element->getEdgeMode());
        $this->assertNull($element->getPreserveAlpha());
    }
}
