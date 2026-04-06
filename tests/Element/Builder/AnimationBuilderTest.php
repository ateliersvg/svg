<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Builder;

use Atelier\Svg\Element\Animation\AnimateElement;
use Atelier\Svg\Element\Animation\AnimateTransformElement;
use Atelier\Svg\Element\Builder\AnimationBuilder;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AnimationBuilder::class)]
final class AnimationBuilderTest extends TestCase
{
    public function testAnimate(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animate($rect, 'opacity');

        $this->assertInstanceOf(AnimationBuilder::class, $builder);
        $this->assertInstanceOf(AnimateElement::class, $builder->getAnimation());
        $this->assertSame('animate', $builder->getAnimation()->getTagName());
        $this->assertSame('opacity', $builder->getAnimation()->getAttribute('attributeName'));
    }

    public function testAnimateTransform(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animateTransform($rect, 'rotate');

        $animation = $builder->getAnimation();
        $this->assertInstanceOf(AnimateTransformElement::class, $animation);
        $this->assertSame('animateTransform', $animation->getTagName());
        $this->assertSame('rotate', $animation->getAttribute('type'));
        $this->assertSame('transform', $animation->getAttribute('attributeName'));
    }

    public function testFluentApi(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animate($rect, 'opacity')
            ->from(0)
            ->to(1)
            ->duration('1s')
            ->repeatCount('indefinite')
            ->fillMode('freeze')
            ->additive();

        $animation = $builder->getAnimation();
        $this->assertSame('0', $animation->getAttribute('from'));
        $this->assertSame('1', $animation->getAttribute('to'));
        $this->assertSame('1s', $animation->getAttribute('dur'));
        $this->assertSame('indefinite', $animation->getAttribute('repeatCount'));
        $this->assertSame('freeze', $animation->getAttribute('fill'));
        $this->assertSame('sum', $animation->getAttribute('additive'));
    }

    public function testValuesOnlyAppliesToAnimateElement(): void
    {
        $rect = new RectElement();

        $animateBuilder = AnimationBuilder::animate($rect, 'opacity')
            ->values('0;0.5;1');
        $this->assertSame('0;0.5;1', $animateBuilder->getAnimation()->getAttribute('values'));

        $transformBuilder = AnimationBuilder::animateTransform($rect, 'rotate')
            ->values('0;180;360');
        $this->assertNull($transformBuilder->getAnimation()->getAttribute('values'));
    }

    public function testBeginOnlyAppliesToAnimateElement(): void
    {
        $rect = new RectElement();

        $animateBuilder = AnimationBuilder::animate($rect, 'opacity')
            ->begin('2s');
        $this->assertSame('2s', $animateBuilder->getAnimation()->getAttribute('begin'));

        $transformBuilder = AnimationBuilder::animateTransform($rect, 'rotate')
            ->begin('click');
        $this->assertNull($transformBuilder->getAnimation()->getAttribute('begin'));
    }

    public function testCalcModeOnlyAppliesToAnimateElement(): void
    {
        $rect = new RectElement();

        $animateBuilder = AnimationBuilder::animate($rect, 'opacity')
            ->calcMode('spline');
        $this->assertSame('spline', $animateBuilder->getAnimation()->getAttribute('calcMode'));

        $transformBuilder = AnimationBuilder::animateTransform($rect, 'rotate')
            ->calcMode('linear');
        $this->assertNull($transformBuilder->getAnimation()->getAttribute('calcMode'));
    }

    public function testDurationWithIntConvertsToMs(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animate($rect, 'opacity')
            ->duration(500);

        $this->assertSame('500ms', $builder->getAnimation()->getAttribute('dur'));
    }

    public function testApplyAppendsToElement(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animate($rect, 'opacity')
            ->from(0)
            ->to(1)
            ->duration('1s');

        $result = $builder->apply();

        $this->assertSame($rect, $result);
        $children = iterator_to_array($rect->getChildren());
        $this->assertCount(1, $children);
        $this->assertInstanceOf(AnimateElement::class, $children[0]);
    }

    public function testFadeIn(): void
    {
        $rect = new RectElement();
        $animation = AnimationBuilder::fadeIn($rect, '2s');

        $this->assertInstanceOf(AnimateElement::class, $animation);
        $this->assertSame('opacity', $animation->getAttribute('attributeName'));
        $this->assertSame('0', $animation->getAttribute('from'));
        $this->assertSame('1', $animation->getAttribute('to'));
        $this->assertSame('2s', $animation->getAttribute('dur'));
        $this->assertSame('freeze', $animation->getAttribute('fill'));

        $children = iterator_to_array($rect->getChildren());
        $this->assertCount(1, $children);
    }

    public function testFadeOut(): void
    {
        $rect = new RectElement();
        $animation = AnimationBuilder::fadeOut($rect, '3s');

        $this->assertInstanceOf(AnimateElement::class, $animation);
        $this->assertSame('opacity', $animation->getAttribute('attributeName'));
        $this->assertSame('1', $animation->getAttribute('from'));
        $this->assertSame('0', $animation->getAttribute('to'));
        $this->assertSame('3s', $animation->getAttribute('dur'));
        $this->assertSame('freeze', $animation->getAttribute('fill'));

        $children = iterator_to_array($rect->getChildren());
        $this->assertCount(1, $children);
    }

    public function testRotate(): void
    {
        $rect = new RectElement();
        $animation = AnimationBuilder::rotate($rect, 0, 360, '4s');

        $this->assertInstanceOf(AnimateTransformElement::class, $animation);
        $this->assertSame('rotate', $animation->getAttribute('type'));
        $this->assertSame('transform', $animation->getAttribute('attributeName'));
        $this->assertSame('0', $animation->getAttribute('from'));
        $this->assertSame('360', $animation->getAttribute('to'));
        $this->assertSame('4s', $animation->getAttribute('dur'));
        $this->assertSame('freeze', $animation->getAttribute('fill'));

        $children = iterator_to_array($rect->getChildren());
        $this->assertCount(1, $children);
    }

    public function testScale(): void
    {
        $rect = new RectElement();
        $animation = AnimationBuilder::scale($rect, 1, 2, '5s');

        $this->assertInstanceOf(AnimateTransformElement::class, $animation);
        $this->assertSame('scale', $animation->getAttribute('type'));
        $this->assertSame('transform', $animation->getAttribute('attributeName'));
        $this->assertSame('1', $animation->getAttribute('from'));
        $this->assertSame('2', $animation->getAttribute('to'));
        $this->assertSame('5s', $animation->getAttribute('dur'));
        $this->assertSame('freeze', $animation->getAttribute('fill'));

        $children = iterator_to_array($rect->getChildren());
        $this->assertCount(1, $children);
    }

    public function testAddCssAnimation(): void
    {
        $rect = new RectElement();
        $keyframes = [
            'from' => ['opacity' => '0'],
            'to' => ['opacity' => '1'],
        ];

        AnimationBuilder::addCssAnimation($rect, 'fadeIn', $keyframes, '2s', 'ease', 'infinite');

        $classes = $rect->getClasses();
        $this->assertCount(1, $classes);
        $this->assertStringStartsWith('anim-', $classes[0]);
    }

    public function testAddCssAnimationWithMultipleProperties(): void
    {
        $rect = new RectElement();
        $keyframes = [
            '0%' => ['opacity' => '0', 'transform' => 'scale(0.5)'],
            '50%' => ['opacity' => '0.5', 'transform' => 'scale(1.2)'],
            '100%' => ['opacity' => '1', 'transform' => 'scale(1)'],
        ];

        AnimationBuilder::addCssAnimation($rect, 'complexAnim', $keyframes, '3s', 'linear', 2);

        $classes = $rect->getClasses();
        $this->assertCount(1, $classes);
    }

    public function testRepeatCountWithInt(): void
    {
        $rect = new RectElement();
        $builder = AnimationBuilder::animate($rect, 'opacity')
            ->repeatCount(3);

        $this->assertSame('3', $builder->getAnimation()->getAttribute('repeatCount'));
    }

    public function testGetAnimationReturnsCorrectType(): void
    {
        $rect = new RectElement();

        $animate = AnimationBuilder::animate($rect, 'opacity');
        $this->assertInstanceOf(AnimateElement::class, $animate->getAnimation());

        $transform = AnimationBuilder::animateTransform($rect, 'rotate');
        $this->assertInstanceOf(AnimateTransformElement::class, $transform->getAnimation());
    }
}
