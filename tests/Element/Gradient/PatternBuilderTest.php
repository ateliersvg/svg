<?php

namespace Atelier\Svg\Tests\Element\Gradient;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Builder\PatternBuilder;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatternBuilder::class)]
final class PatternBuilderTest extends TestCase
{
    private ?Document $doc = null;

    protected function setUp(): void
    {
        $this->doc = Document::create();
    }

    private function getDoc(): Document
    {
        $doc = $this->doc;
        $this->assertNotNull($doc);

        return $doc;
    }

    public function testCreatePattern(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern1');
        $pattern = $helper->getPattern();

        $this->assertEquals('pattern1', $pattern->getId());
        $this->assertEquals('pattern', $pattern->getTagName());
    }

    public function testSetSize(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(10, 20);

        $pattern = $helper->getPattern();
        $this->assertEquals('10', $pattern->getAttribute('width'));
        $this->assertEquals('20', $pattern->getAttribute('height'));
    }

    public function testSetPosition(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->position(5, 10);

        $pattern = $helper->getPattern();
        $this->assertEquals('5', $pattern->getAttribute('x'));
        $this->assertEquals('10', $pattern->getAttribute('y'));
    }

    public function testSetBounds(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->bounds(1, 2, 10, 20);

        $pattern = $helper->getPattern();
        $this->assertEquals('1', $pattern->getAttribute('x'));
        $this->assertEquals('2', $pattern->getAttribute('y'));
        $this->assertEquals('10', $pattern->getAttribute('width'));
        $this->assertEquals('20', $pattern->getAttribute('height'));
    }

    public function testSetUnits(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->units('userSpaceOnUse');

        $pattern = $helper->getPattern();
        $this->assertEquals('userSpaceOnUse', $pattern->getAttribute('patternUnits'));
    }

    public function testSetContentUnits(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->contentUnits('objectBoundingBox');

        $pattern = $helper->getPattern();
        $this->assertEquals('objectBoundingBox', $pattern->getAttribute('patternContentUnits'));
    }

    public function testSetTransform(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->transform('rotate(45)');

        $pattern = $helper->getPattern();
        $this->assertEquals('rotate(45)', $pattern->getAttribute('patternTransform'));
    }

    public function testSetViewBox(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->viewBox(0, 0, 100, 100);

        $pattern = $helper->getPattern();
        $this->assertEquals('0 0 100 100', $pattern->getAttribute('viewBox'));
    }

    public function testAddRect(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(20, 20)
            ->addRect(0, 0, 10, 10, '#ff0000');

        $pattern = $helper->getPattern();
        $children = iterator_to_array($pattern->getChildren());

        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertEquals('0', $children[0]->getAttribute('x'));
        $this->assertEquals('10', $children[0]->getAttribute('width'));
        $this->assertEquals('#ff0000', $children[0]->getAttribute('fill'));
    }

    public function testAddRectWithStroke(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(20, 20)
            ->addRect(0, 0, 10, 10, '#ff0000', '#0000ff');

        $pattern = $helper->getPattern();
        $children = iterator_to_array($pattern->getChildren());

        $this->assertEquals('#0000ff', $children[0]->getAttribute('stroke'));
    }

    public function testAddCircle(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(20, 20)
            ->addCircle(10, 10, 5, '#00ff00');

        $pattern = $helper->getPattern();
        $children = iterator_to_array($pattern->getChildren());

        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);
        $this->assertEquals('10', $children[0]->getAttribute('cx'));
        $this->assertEquals('5', $children[0]->getAttribute('r'));
        $this->assertEquals('#00ff00', $children[0]->getAttribute('fill'));
    }

    public function testAddElement(): void
    {
        $rect = new RectElement();
        $rect->setX(5)->setY(5)->setWidth(10)->setHeight(10);

        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(20, 20)
            ->addElement($rect);

        $pattern = $helper->getPattern();
        $children = iterator_to_array($pattern->getChildren());

        $this->assertCount(1, $children);
        $this->assertSame($rect, $children[0]);
    }

    public function testAddToDefs(): void
    {
        $helper = PatternBuilder::create($this->getDoc(), 'pattern')
            ->size(10, 10)
            ->addToDefs();

        $root = $this->getDoc()->getRootElement();
        $this->assertNotNull($root);
        $children = iterator_to_array($root->getChildren());

        $this->assertCount(1, $children);
        $this->assertEquals('defs', $children[0]->getTagName());

        $defs = $children[0];
        $this->assertInstanceOf(\Atelier\Svg\Element\Structural\DefsElement::class, $defs);
        $defsChildren = iterator_to_array($defs->getChildren());
        $this->assertCount(1, $defsChildren);
        $this->assertEquals('pattern', $defsChildren[0]->getTagName());
    }

    public function testCreateDotsPattern(): void
    {
        $pattern = PatternBuilder::createDots($this->getDoc(), 'dots', 10, 2, '#3b82f6');

        $this->assertEquals('dots', $pattern->getId());
        $this->assertEquals('10', $pattern->getAttribute('width'));
        $this->assertEquals('10', $pattern->getAttribute('height'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(1, $children);
        $this->assertInstanceOf(CircleElement::class, $children[0]);
        $this->assertEquals('5', $children[0]->getAttribute('cx')); // Center
        $this->assertEquals('2', $children[0]->getAttribute('r'));
        $this->assertEquals('#3b82f6', $children[0]->getAttribute('fill'));
    }

    public function testCreateDotsPatternWithBackground(): void
    {
        $pattern = PatternBuilder::createDots($this->getDoc(), 'dots', 10, 2, '#3b82f6', '#ffffff');

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(2, $children); // Background rect + circle
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertEquals('#ffffff', $children[0]->getAttribute('fill'));
    }

    public function testCreateStripesPattern(): void
    {
        $pattern = PatternBuilder::createStripes($this->getDoc(), 'stripes', 10, 5, '#8b5cf6');

        $this->assertEquals('stripes', $pattern->getId());
        $this->assertEquals('10', $pattern->getAttribute('width'));
        $this->assertEquals('10', $pattern->getAttribute('height'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);
        $this->assertEquals('5', $children[0]->getAttribute('height'));
        $this->assertEquals('#8b5cf6', $children[0]->getAttribute('fill'));
    }

    public function testCreateDiagonalStripesPattern(): void
    {
        $pattern = PatternBuilder::createDiagonalStripes($this->getDoc(), 'diagonal', 20, 10, '#000000');

        $this->assertEquals('diagonal', $pattern->getId());
        $this->assertEquals('20', $pattern->getAttribute('width'));
        $this->assertEquals('userSpaceOnUse', $pattern->getAttribute('patternUnits'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(1, $children);
        $this->assertInstanceOf(RectElement::class, $children[0]);

        $transform = $children[0]->getAttribute('transform');
        $this->assertNotNull($transform);
        $this->assertStringContainsString('rotate(45', $transform);
    }

    public function testCreateCheckerboardPattern(): void
    {
        $pattern = PatternBuilder::createCheckerboard($this->getDoc(), 'checker', 20, '#000', '#fff');

        $this->assertEquals('checker', $pattern->getId());
        $this->assertEquals('40', $pattern->getAttribute('width')); // 2x square size
        $this->assertEquals('40', $pattern->getAttribute('height'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(3, $children); // Background + 2 squares
        $this->assertEquals('#000', $children[0]->getAttribute('fill'));
        $this->assertEquals('#fff', $children[1]->getAttribute('fill'));
        $this->assertEquals('#fff', $children[2]->getAttribute('fill'));
    }

    public function testCreateGridPattern(): void
    {
        $pattern = PatternBuilder::createGrid($this->getDoc(), 'grid', 20, 1, '#cccccc');

        $this->assertEquals('grid', $pattern->getId());
        $this->assertEquals('20', $pattern->getAttribute('width'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(2, $children); // Horizontal + vertical lines
        $this->assertEquals('1', $children[0]->getAttribute('height'));
        $this->assertEquals('1', $children[1]->getAttribute('width'));
    }

    public function testCreateGridPatternWithBackground(): void
    {
        $pattern = PatternBuilder::createGrid($this->getDoc(), 'grid', 20, 1, '#ccc', '#fff');

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(3, $children); // Background + 2 lines
        $this->assertEquals('#fff', $children[0]->getAttribute('fill'));
    }

    public function testCreateCrosshatchPattern(): void
    {
        $pattern = PatternBuilder::createCrosshatch($this->getDoc(), 'crosshatch', 20, 1, '#000');

        $this->assertEquals('crosshatch', $pattern->getId());
        $this->assertEquals('20', $pattern->getAttribute('width'));
        $this->assertEquals('userSpaceOnUse', $pattern->getAttribute('patternUnits'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(2, $children); // Two diagonal lines

        $transform1 = $children[0]->getAttribute('transform');
        $this->assertNotNull($transform1);
        $this->assertStringContainsString('rotate(45', $transform1);

        $transform2 = $children[1]->getAttribute('transform');
        $this->assertNotNull($transform2);
        $this->assertStringContainsString('rotate(-45', $transform2);
    }

    public function testFluentChaining(): void
    {
        $pattern = PatternBuilder::create($this->getDoc(), 'complex')
            ->size(30, 30)
            ->position(0, 0)
            ->units('userSpaceOnUse')
            ->contentUnits('userSpaceOnUse')
            ->viewBox(0, 0, 30, 30)
            ->addRect(0, 0, 15, 15, '#ff0000')
            ->addCircle(22.5, 22.5, 7.5, '#0000ff')
            ->addToDefs()
            ->getPattern();

        $this->assertEquals('30', $pattern->getAttribute('width'));
        $this->assertEquals('30', $pattern->getAttribute('height'));
        $this->assertEquals('userSpaceOnUse', $pattern->getAttribute('patternUnits'));
        $this->assertEquals('0 0 30 30', $pattern->getAttribute('viewBox'));

        $children = iterator_to_array($pattern->getChildren());
        $this->assertCount(2, $children);
    }

    public function testApplyPatternToElement(): void
    {
        PatternBuilder::createDots($this->getDoc(), 'dots', 10, 2, '#000');

        $rect = new RectElement();
        $rect->setFillPaintServer('dots');

        $this->assertEquals('url(#dots)', $rect->getAttribute('fill'));
    }
}
