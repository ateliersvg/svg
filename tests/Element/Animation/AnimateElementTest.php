<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Animation;

use Atelier\Svg\Element\Animation\AnimateElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnimateElement::class)]
final class AnimateElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $animate = new AnimateElement();

        $this->assertSame('animate', $animate->getTagName());
    }

    public function testSetAttributeName(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setAttributeName('opacity');

        $this->assertSame($animate, $result, 'Should return self for chaining');
        $this->assertSame('opacity', $animate->getAttribute('attributeName'));
    }

    public function testSetFromWithString(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setFrom('0');

        $this->assertSame($animate, $result);
        $this->assertSame('0', $animate->getAttribute('from'));
    }

    public function testSetFromWithNumeric(): void
    {
        $animate = new AnimateElement();

        $animate->setFrom(0);
        $this->assertSame('0', $animate->getAttribute('from'));

        $animate->setFrom(0.5);
        $this->assertSame('0.5', $animate->getAttribute('from'));
    }

    public function testSetToWithString(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setTo('1');

        $this->assertSame($animate, $result);
        $this->assertSame('1', $animate->getAttribute('to'));
    }

    public function testSetToWithNumeric(): void
    {
        $animate = new AnimateElement();

        $animate->setTo(100);
        $this->assertSame('100', $animate->getAttribute('to'));

        $animate->setTo(50.75);
        $this->assertSame('50.75', $animate->getAttribute('to'));
    }

    public function testSetValues(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setValues('0;0.5;1');

        $this->assertSame($animate, $result);
        $this->assertSame('0;0.5;1', $animate->getAttribute('values'));
    }

    public function testSetDur(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setDur('2s');

        $this->assertSame($animate, $result);
        $this->assertSame('2s', $animate->getAttribute('dur'));
    }

    public function testSetRepeatCountWithNumber(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setRepeatCount(5);

        $this->assertSame($animate, $result);
        $this->assertSame('5', $animate->getAttribute('repeatCount'));
    }

    public function testSetRepeatCountWithIndefinite(): void
    {
        $animate = new AnimateElement();
        $animate->setRepeatCount('indefinite');

        $this->assertSame('indefinite', $animate->getAttribute('repeatCount'));
    }

    public function testSetFill(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setFill('freeze');

        $this->assertSame($animate, $result);
        $this->assertSame('freeze', $animate->getAttribute('fill'));
    }

    public function testSetBegin(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setBegin('2s');

        $this->assertSame($animate, $result);
        $this->assertSame('2s', $animate->getAttribute('begin'));
    }

    public function testSetCalcMode(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setCalcMode('linear');

        $this->assertSame($animate, $result);
        $this->assertSame('linear', $animate->getAttribute('calcMode'));
    }

    public function testSetAdditive(): void
    {
        $animate = new AnimateElement();
        $result = $animate->setAdditive('sum');

        $this->assertSame($animate, $result);
        $this->assertSame('sum', $animate->getAttribute('additive'));
    }

    public function testMethodChaining(): void
    {
        $animate = new AnimateElement();

        $result = $animate
            ->setAttributeName('opacity')
            ->setFrom(0)
            ->setTo(1)
            ->setDur('1s')
            ->setRepeatCount('indefinite');

        $this->assertSame($animate, $result);
        $this->assertSame('opacity', $animate->getAttribute('attributeName'));
        $this->assertSame('0', $animate->getAttribute('from'));
        $this->assertSame('1', $animate->getAttribute('to'));
        $this->assertSame('1s', $animate->getAttribute('dur'));
        $this->assertSame('indefinite', $animate->getAttribute('repeatCount'));
    }
}
