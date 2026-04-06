<?php

namespace Atelier\Svg\Tests\Element\Animation;

use Atelier\Svg\Element\Builder\AnimationBuilder;
use Atelier\Svg\Element\Shape\CircleElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnimationBuilder::class)]
final class AnimationBuilderTest extends TestCase
{
    public function testAnimate(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animate($circle, 'opacity');

        $this->assertEquals('animate', $helper->getAnimation()->getTagName());
        $this->assertEquals('opacity', $helper->getAnimation()->getAttribute('attributeName'));
    }

    public function testAnimateTransform(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animateTransform($circle, 'rotate');

        $animation = $helper->getAnimation();
        $this->assertEquals('animateTransform', $animation->getTagName());
        $this->assertEquals('rotate', $animation->getAttribute('type'));
        $this->assertEquals('transform', $animation->getAttribute('attributeName'));
    }

    public function testFromTo(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animate($circle, 'opacity')
            ->from(0)
            ->to(1);

        $animation = $helper->getAnimation();
        $this->assertEquals('0', $animation->getAttribute('from'));
        $this->assertEquals('1', $animation->getAttribute('to'));
    }

    public function testDuration(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animate($circle, 'opacity')
            ->duration('2s');

        $this->assertEquals('2s', $helper->getAnimation()->getAttribute('dur'));
    }

    public function testDurationWithMilliseconds(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animate($circle, 'opacity')
            ->duration(1000);

        $this->assertEquals('1000ms', $helper->getAnimation()->getAttribute('dur'));
    }

    public function testRepeatCount(): void
    {
        $circle = new CircleElement();
        $helper = AnimationBuilder::animate($circle, 'opacity')
            ->repeatCount('indefinite');

        $this->assertEquals('indefinite', $helper->getAnimation()->getAttribute('repeatCount'));
    }

    public function testFadeIn(): void
    {
        $circle = new CircleElement();
        $animation = AnimationBuilder::fadeIn($circle);

        $this->assertEquals('0', $animation->getAttribute('from'));
        $this->assertEquals('1', $animation->getAttribute('to'));
        $this->assertEquals('freeze', $animation->getAttribute('fill'));
    }

    public function testFadeOut(): void
    {
        $circle = new CircleElement();
        $animation = AnimationBuilder::fadeOut($circle, '2s');

        $this->assertEquals('1', $animation->getAttribute('from'));
        $this->assertEquals('0', $animation->getAttribute('to'));
        $this->assertEquals('2s', $animation->getAttribute('dur'));
    }

    public function testRotate(): void
    {
        $circle = new CircleElement();
        $animation = AnimationBuilder::rotate($circle, 0, 360, '3s');

        $this->assertEquals('0', $animation->getAttribute('from'));
        $this->assertEquals('360', $animation->getAttribute('to'));
        $this->assertEquals('3s', $animation->getAttribute('dur'));
    }

    public function testScale(): void
    {
        $circle = new CircleElement();
        $animation = AnimationBuilder::scale($circle, 1, 2);

        $this->assertEquals('1', $animation->getAttribute('from'));
        $this->assertEquals('2', $animation->getAttribute('to'));
    }
}
