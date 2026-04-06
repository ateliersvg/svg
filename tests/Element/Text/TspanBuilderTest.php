<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Text;

use Atelier\Svg\Element\Builder\TspanBuilder;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TspanBuilder::class)]
final class TspanBuilderTest extends TestCase
{
    public function testConstructor(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);

        $this->assertSame(10.0, $builder->getCurrentX());
        $this->assertSame(20.0, $builder->getCurrentY());
        $this->assertSame($text, $builder->getTextElement());
    }

    public function testConstructorWithDefaultPosition(): void
    {
        $text = new TextElement();
        $builder = new TspanBuilder($text);

        $this->assertSame(0.0, $builder->getCurrentX());
        $this->assertSame(0.0, $builder->getCurrentY());
    }

    public function testSetAndGetDefaultGap(): void
    {
        $text = new TextElement();
        $builder = new TspanBuilder($text);

        $result = $builder->setDefaultGap(10.5);

        $this->assertSame($builder, $result, 'setDefaultGap should return self for chaining');
        $this->assertSame(10.5, $builder->getDefaultGap());
    }

    public function testSetAvgCharWidth(): void
    {
        $text = new TextElement();
        $builder = new TspanBuilder($text);

        $result = $builder->setAvgCharWidth(12.0);

        $this->assertSame($builder, $result, 'setAvgCharWidth should return self for chaining');
        // The effect is tested indirectly through position calculations
    }

    public function testAddSingleTspan(): void
    {
        $text = new TextElement();
        $text->setPosition(100, 200);

        $builder = new TspanBuilder($text);
        $result = $builder->add('Hello');

        $this->assertSame($builder, $result, 'add should return self for chaining');
        $this->assertSame(1, $text->getChildCount());

        $tspan = $text->getChildren()[0];
        $this->assertInstanceOf(TspanElement::class, $tspan);
        $this->assertSame('Hello', $tspan->getTextContent());
        $this->assertSame('100', $tspan->getAttribute('x'));
        $this->assertSame('200', $tspan->getAttribute('y'));
    }

    public function testAddWithGap(): void
    {
        $text = new TextElement();
        $text->setPosition(100, 200);

        $builder = new TspanBuilder($text);
        $builder->add('First', 0)
                ->add('Second', 10);

        $this->assertSame(2, $text->getChildCount());

        $children = $text->getChildren();
        $first = $children[0];
        $second = $children[1];

        $this->assertSame('First', $first->getTextContent());
        $this->assertSame('100', $first->getAttribute('x'));

        $this->assertSame('Second', $second->getTextContent());
        // Position should be: 100 (start) + 0 (gap from first) + 40 (5 chars * 8px) + 10 (gap) = 150
        $this->assertSame('150', $second->getAttribute('x'));
    }

    public function testAddWithStyles(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);
        $builder->add('Styled', 0, [
            'fill' => '#ff0000',
            'font-size' => '24',
            'font-weight' => 'bold',
        ]);

        $tspan = $text->getChildren()[0];
        $this->assertSame('#ff0000', $tspan->getAttribute('fill'));
        $this->assertSame('24', $tspan->getAttribute('font-size'));
        $this->assertSame('bold', $tspan->getAttribute('font-weight'));
    }

    public function testAddWithDefaultGap(): void
    {
        $text = new TextElement();
        $text->setPosition(100, 200);

        $builder = new TspanBuilder($text);
        $builder->setDefaultGap(15.0);
        $builder->add('First')
                ->add('Second');

        $children = $text->getChildren();
        $first = $children[0];
        $second = $children[1];

        $this->assertSame('100', $first->getAttribute('x'));
        // Position should be: 100 + 40 (5 chars * 8px) + 15 (default gap) = 155
        $this->assertSame('155', $second->getAttribute('x'));
    }

    public function testAddAt(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);
        $builder->addAt('Positioned', 50, 30);

        $tspan = $text->getChildren()[0];
        $this->assertSame('Positioned', $tspan->getTextContent());
        $this->assertSame('50', $tspan->getAttribute('x'));
        $this->assertSame('30', $tspan->getAttribute('y'));
    }

    public function testAddAtWithNullY(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);
        $builder->addAt('Test', 50);

        $tspan = $text->getChildren()[0];
        $this->assertSame('50', $tspan->getAttribute('x'));
        $this->assertSame('20', $tspan->getAttribute('y')); // Uses current Y
    }

    public function testAddAtWithStyles(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);
        $builder->addAt('Test', 50, 30, ['fill' => '#00ff00']);

        $tspan = $text->getChildren()[0];
        $this->assertSame('#00ff00', $tspan->getAttribute('fill'));
    }

    public function testDistributeEvenly(): void
    {
        $text = new TextElement();
        $text->setPosition(0, 50);

        $builder = new TspanBuilder($text);
        $builder->distributeEvenly(['A', 'B', 'C'], 200);

        $this->assertSame(3, $text->getChildCount());

        $children = $text->getChildren();
        $this->assertSame('A', $children[0]->getTextContent());
        $this->assertSame('0', $children[0]->getAttribute('x'));

        $this->assertSame('B', $children[1]->getTextContent());
        $this->assertSame('100', $children[1]->getAttribute('x'));

        $this->assertSame('C', $children[2]->getTextContent());
        $this->assertSame('200', $children[2]->getAttribute('x'));
    }

    public function testDistributeEvenlyWithCommonStyles(): void
    {
        $text = new TextElement();
        $text->setPosition(0, 50);

        $builder = new TspanBuilder($text);
        $builder->distributeEvenly(['X', 'Y'], 100, ['fill' => '#0000ff']);

        $children = $text->getChildren();
        $this->assertSame('#0000ff', $children[0]->getAttribute('fill'));
        $this->assertSame('#0000ff', $children[1]->getAttribute('fill'));
    }

    public function testDistributeEvenlyWithEmptyArray(): void
    {
        $text = new TextElement();
        $text->setPosition(0, 50);

        $builder = new TspanBuilder($text);
        $builder->distributeEvenly([], 100);

        $this->assertSame(0, $text->getChildCount());
    }

    public function testDistributeEvenlyWithSingleElement(): void
    {
        $text = new TextElement();
        $text->setPosition(50, 100);

        $builder = new TspanBuilder($text);
        $builder->distributeEvenly(['Single'], 200);

        $this->assertSame(1, $text->getChildCount());
        $tspan = $text->getChildren()[0];
        $this->assertSame('50', $tspan->getAttribute('x')); // At start position
    }

    public function testStackVertically(): void
    {
        $text = new TextElement();
        $text->setPosition(50, 100);

        $builder = new TspanBuilder($text);
        $builder->stackVertically(['Line 1', 'Line 2', 'Line 3'], 20);

        $this->assertSame(3, $text->getChildCount());

        $children = $text->getChildren();
        $this->assertSame('Line 1', $children[0]->getTextContent());
        $this->assertSame('50', $children[0]->getAttribute('x'));
        $this->assertSame('100', $children[0]->getAttribute('y'));

        $this->assertSame('Line 2', $children[1]->getTextContent());
        $this->assertSame('50', $children[1]->getAttribute('x'));
        $this->assertSame('120', $children[1]->getAttribute('y'));

        $this->assertSame('Line 3', $children[2]->getTextContent());
        $this->assertSame('50', $children[2]->getAttribute('x'));
        $this->assertSame('140', $children[2]->getAttribute('y'));
    }

    public function testStackVerticallyWithCommonStyles(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 20);

        $builder = new TspanBuilder($text);
        $builder->stackVertically(['A', 'B'], 30, ['font-size' => '16']);

        $children = $text->getChildren();
        $this->assertSame('16', $children[0]->getAttribute('font-size'));
        $this->assertSame('16', $children[1]->getAttribute('font-size'));
    }

    public function testReset(): void
    {
        $text = new TextElement();
        $text->setPosition(100, 200);

        $builder = new TspanBuilder($text);
        $builder->add('First', 10);

        // Position should have moved
        $this->assertGreaterThan(100, $builder->getCurrentX());

        $result = $builder->reset();

        $this->assertSame($builder, $result, 'reset should return self for chaining');
        $this->assertSame(100.0, $builder->getCurrentX());
        $this->assertSame(200.0, $builder->getCurrentY());
    }

    public function testComplexMultiColorExample(): void
    {
        $text = new TextElement();
        $text->setPosition(10, 50);
        $text->setAttribute('font-size', '20');

        $builder = new TspanBuilder($text);
        $builder->add('Hello', 0, ['fill' => '#000000'])
                ->add(' ', 5)
                ->add('World', 5, ['fill' => '#ff0000'])
                ->add('!', 5, ['fill' => '#0000ff', 'font-weight' => 'bold']);

        $this->assertSame(4, $text->getChildCount());

        $children = $text->getChildren();
        $this->assertSame('Hello', $children[0]->getTextContent());
        $this->assertSame('#000000', $children[0]->getAttribute('fill'));

        $this->assertSame(' ', $children[1]->getTextContent());

        $this->assertSame('World', $children[2]->getTextContent());
        $this->assertSame('#ff0000', $children[2]->getAttribute('fill'));

        $this->assertSame('!', $children[3]->getTextContent());
        $this->assertSame('#0000ff', $children[3]->getAttribute('fill'));
        $this->assertSame('bold', $children[3]->getAttribute('font-weight'));
    }

    public function testMethodChaining(): void
    {
        $text = new TextElement();
        $text->setPosition(0, 0);

        $builder = new TspanBuilder($text);
        $result = $builder
            ->setDefaultGap(10)
            ->setAvgCharWidth(10)
            ->add('A')
            ->add('B')
            ->reset()
            ->add('C');

        $this->assertSame($builder, $result);
        $this->assertSame(3, $text->getChildCount());
    }

    public function testCustomAvgCharWidth(): void
    {
        $text = new TextElement();
        $text->setPosition(0, 0);

        // Test with custom character width
        $builder = new TspanBuilder($text, 10.0);
        $builder->add('ABC'); // 3 chars * 10px = 30px
        $builder->add('XYZ', 5); // Gap 5 + previous width

        $children = $text->getChildren();
        $this->assertSame('0', $children[0]->getAttribute('x'));
        // 0 + 30 (ABC width with 10px/char) + 5 (gap) = 35
        $this->assertSame('35', $children[1]->getAttribute('x'));
    }
}
