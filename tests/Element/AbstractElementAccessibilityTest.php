<?php

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Atelier\Svg\Element\AbstractElement::class)]
final class AbstractElementAccessibilityTest extends TestCase
{
    public function testAddTitleConvenienceMethod(): void
    {
        $group = new GroupElement();
        $result = $group->addTitle('Test Title');

        $this->assertSame($group, $result);

        $children = $group->getChildren();
        $titleElement = null;
        foreach ($children as $child) {
            if ($child instanceof TitleElement) {
                $titleElement = $child;
                break;
            }
        }

        $this->assertNotNull($titleElement);
        $this->assertEquals('Test Title', $titleElement->getContent());
    }

    public function testAddDescriptionConvenienceMethod(): void
    {
        $group = new GroupElement();
        $result = $group->addDescription('Test Description');

        $this->assertSame($group, $result);

        $children = $group->getChildren();
        $descElement = null;
        foreach ($children as $child) {
            if ($child instanceof DescElement) {
                $descElement = $child;
                break;
            }
        }

        $this->assertNotNull($descElement);
        $this->assertEquals('Test Description', $descElement->getContent());
    }

    public function testSetAriaLabelConvenienceMethod(): void
    {
        $element = new RectElement();
        $result = $element->setAriaLabel('Icon Label');

        $this->assertSame($element, $result);
        $this->assertEquals('Icon Label', $element->getAttribute('aria-label'));
    }

    public function testSetAriaRoleConvenienceMethod(): void
    {
        $element = new RectElement();
        $result = $element->setAriaRole('img');

        $this->assertSame($element, $result);
        $this->assertEquals('img', $element->getAttribute('role'));
    }

    public function testSetFocusableConvenienceMethod(): void
    {
        $element = new RectElement();
        $result = $element->setFocusable(true);

        $this->assertSame($element, $result);
        $this->assertEquals('true', $element->getAttribute('focusable'));
    }

    public function testSetTabIndexConvenienceMethod(): void
    {
        $element = new RectElement();
        $result = $element->setTabIndex(0);

        $this->assertSame($element, $result);
        $this->assertEquals('0', $element->getAttribute('tabindex'));
    }

    public function testFluentChaining(): void
    {
        $group = new GroupElement();

        $result = $group
            ->addTitle('Title')
            ->addDescription('Description')
            ->setAriaLabel('Label')
            ->setAriaRole('img')
            ->setFocusable(true)
            ->setTabIndex(0);

        $this->assertSame($group, $result);
    }

    public function testAddTitleToNonContainerElement(): void
    {
        $stop = new StopElement();
        $result = $stop->addTitle('Stop Title');

        $this->assertSame($stop, $result);
        $this->assertEquals('Stop Title', $stop->getAttribute('aria-label'));
    }
}
