<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Animation;

use Atelier\Svg\Element\Animation\AnimateTransformElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnimateTransformElement::class)]
final class AnimateTransformElementTest extends TestCase
{
    public function testConstruct(): void
    {
        $animate = new AnimateTransformElement();

        $this->assertSame('animateTransform', $animate->getTagName());
    }

    public function testSetType(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setType('rotate');

        $this->assertSame($animate, $result, 'Should return self for chaining');
        $this->assertSame('rotate', $animate->getAttribute('type'));
    }

    public function testSetTypeWithVariousTypes(): void
    {
        $animate = new AnimateTransformElement();

        $types = ['translate', 'scale', 'rotate', 'skewX', 'skewY'];

        foreach ($types as $type) {
            $animate->setType($type);
            $this->assertSame($type, $animate->getAttribute('type'));
        }
    }

    public function testSetAttributeName(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setAttributeName('transform');

        $this->assertSame($animate, $result);
        $this->assertSame('transform', $animate->getAttribute('attributeName'));
    }

    public function testSetFromWithString(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setFrom('0 0');

        $this->assertSame($animate, $result);
        $this->assertSame('0 0', $animate->getAttribute('from'));
    }

    public function testSetFromWithNumeric(): void
    {
        $animate = new AnimateTransformElement();

        $animate->setFrom(0);
        $this->assertSame('0', $animate->getAttribute('from'));

        $animate->setFrom(45.5);
        $this->assertSame('45.5', $animate->getAttribute('from'));
    }

    public function testSetToWithString(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setTo('360 50 50');

        $this->assertSame($animate, $result);
        $this->assertSame('360 50 50', $animate->getAttribute('to'));
    }

    public function testSetToWithNumeric(): void
    {
        $animate = new AnimateTransformElement();

        $animate->setTo(360);
        $this->assertSame('360', $animate->getAttribute('to'));

        $animate->setTo(180.75);
        $this->assertSame('180.75', $animate->getAttribute('to'));
    }

    public function testSetDur(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setDur('3s');

        $this->assertSame($animate, $result);
        $this->assertSame('3s', $animate->getAttribute('dur'));
    }

    public function testSetRepeatCountWithNumber(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setRepeatCount(10);

        $this->assertSame($animate, $result);
        $this->assertSame('10', $animate->getAttribute('repeatCount'));
    }

    public function testSetRepeatCountWithIndefinite(): void
    {
        $animate = new AnimateTransformElement();
        $animate->setRepeatCount('indefinite');

        $this->assertSame('indefinite', $animate->getAttribute('repeatCount'));
    }

    public function testSetFill(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setFill('freeze');

        $this->assertSame($animate, $result);
        $this->assertSame('freeze', $animate->getAttribute('fill'));
    }

    public function testSetAdditive(): void
    {
        $animate = new AnimateTransformElement();
        $result = $animate->setAdditive('sum');

        $this->assertSame($animate, $result);
        $this->assertSame('sum', $animate->getAttribute('additive'));
    }

    public function testMethodChainingRotate(): void
    {
        $animate = new AnimateTransformElement();

        $result = $animate
            ->setType('rotate')
            ->setAttributeName('transform')
            ->setFrom(0)
            ->setTo(360)
            ->setDur('2s')
            ->setRepeatCount('indefinite');

        $this->assertSame($animate, $result);
        $this->assertSame('rotate', $animate->getAttribute('type'));
        $this->assertSame('transform', $animate->getAttribute('attributeName'));
        $this->assertSame('0', $animate->getAttribute('from'));
        $this->assertSame('360', $animate->getAttribute('to'));
        $this->assertSame('2s', $animate->getAttribute('dur'));
        $this->assertSame('indefinite', $animate->getAttribute('repeatCount'));
    }

    public function testMethodChainingScale(): void
    {
        $animate = new AnimateTransformElement();

        $result = $animate
            ->setType('scale')
            ->setFrom('1')
            ->setTo('1.5')
            ->setDur('0.5s')
            ->setFill('freeze');

        $this->assertSame($animate, $result);
        $this->assertSame('scale', $animate->getAttribute('type'));
        $this->assertSame('1', $animate->getAttribute('from'));
        $this->assertSame('1.5', $animate->getAttribute('to'));
        $this->assertSame('0.5s', $animate->getAttribute('dur'));
        $this->assertSame('freeze', $animate->getAttribute('fill'));
    }
}
