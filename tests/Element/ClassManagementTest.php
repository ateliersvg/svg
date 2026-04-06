<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractElement::class)]
final class ClassManagementTest extends TestCase
{
    public function testAddSingleClass(): void
    {
        $element = new RectElement();
        $result = $element->addClass('button');

        $this->assertSame($element, $result, 'addClass should return self for chaining');
        $this->assertTrue($element->hasClass('button'));
        $this->assertSame('button', $element->getAttribute('class'));
    }

    public function testAddMultipleClassesAtOnce(): void
    {
        $element = new RectElement();
        $element->addClass('button primary large');

        $this->assertTrue($element->hasClass('button'));
        $this->assertTrue($element->hasClass('primary'));
        $this->assertTrue($element->hasClass('large'));
        $this->assertSame(['button', 'primary', 'large'], $element->getClasses());
    }

    public function testAddClassDoesNotDuplicate(): void
    {
        $element = new RectElement();
        $element->addClass('button');
        $element->addClass('button');

        $this->assertSame('button', $element->getAttribute('class'));
        $this->assertSame(['button'], $element->getClasses());
    }

    public function testAddClassToExistingClasses(): void
    {
        $element = new RectElement();
        $element->setAttribute('class', 'existing');
        $element->addClass('new');

        $this->assertSame(['existing', 'new'], $element->getClasses());
    }

    public function testRemoveSingleClass(): void
    {
        $element = new RectElement();
        $element->addClass('button primary');
        $result = $element->removeClass('button');

        $this->assertSame($element, $result, 'removeClass should return self for chaining');
        $this->assertFalse($element->hasClass('button'));
        $this->assertTrue($element->hasClass('primary'));
        $this->assertSame('primary', $element->getAttribute('class'));
    }

    public function testRemoveMultipleClasses(): void
    {
        $element = new RectElement();
        $element->addClass('button primary large active');
        $element->removeClass('primary large');

        $this->assertSame(['button', 'active'], $element->getClasses());
    }

    public function testRemoveLastClassRemovesAttribute(): void
    {
        $element = new RectElement();
        $element->addClass('button');
        $element->removeClass('button');

        $this->assertNull($element->getAttribute('class'));
        $this->assertSame([], $element->getClasses());
    }

    public function testRemoveNonExistentClassDoesNothing(): void
    {
        $element = new RectElement();
        $element->addClass('button');
        $element->removeClass('nonexistent');

        $this->assertSame(['button'], $element->getClasses());
    }

    public function testHasClassReturnsTrueWhenPresent(): void
    {
        $element = new RectElement();
        $element->addClass('button primary');

        $this->assertTrue($element->hasClass('button'));
        $this->assertTrue($element->hasClass('primary'));
    }

    public function testHasClassReturnsFalseWhenNotPresent(): void
    {
        $element = new RectElement();
        $element->addClass('button');

        $this->assertFalse($element->hasClass('primary'));
        $this->assertFalse($element->hasClass('nonexistent'));
    }

    public function testHasClassReturnsFalseWhenNoClasses(): void
    {
        $element = new RectElement();

        $this->assertFalse($element->hasClass('button'));
    }

    public function testToggleClassAddsWhenNotPresent(): void
    {
        $element = new RectElement();
        $result = $element->toggleClass('active');

        $this->assertSame($element, $result, 'toggleClass should return self for chaining');
        $this->assertTrue($element->hasClass('active'));
    }

    public function testToggleClassRemovesWhenPresent(): void
    {
        $element = new RectElement();
        $element->addClass('active');
        $element->toggleClass('active');

        $this->assertFalse($element->hasClass('active'));
    }

    public function testToggleClassMultipleTimes(): void
    {
        $element = new RectElement();

        $element->toggleClass('active');
        $this->assertTrue($element->hasClass('active'));

        $element->toggleClass('active');
        $this->assertFalse($element->hasClass('active'));

        $element->toggleClass('active');
        $this->assertTrue($element->hasClass('active'));
    }

    public function testGetClassesReturnsEmptyArrayWhenNoClasses(): void
    {
        $element = new RectElement();

        $this->assertSame([], $element->getClasses());
    }

    public function testGetClassesReturnsArrayOfClasses(): void
    {
        $element = new RectElement();
        $element->addClass('button primary large');

        $classes = $element->getClasses();
        $this->assertIsArray($classes);
        $this->assertCount(3, $classes);
        $this->assertSame(['button', 'primary', 'large'], $classes);
    }

    public function testGetClassesHandlesExtraWhitespace(): void
    {
        $element = new RectElement();
        $element->setAttribute('class', '  button   primary  ');

        $classes = $element->getClasses();
        $this->assertSame(['button', 'primary'], $classes);
    }

    public function testClassManagementChaining(): void
    {
        $element = new RectElement();
        $result = $element
            ->addClass('button')
            ->addClass('primary')
            ->removeClass('nonexistent')
            ->toggleClass('active');

        $this->assertSame($element, $result);
        $this->assertSame(['button', 'primary', 'active'], $element->getClasses());
    }

    public function testClassManagementWithDifferentElementTypes(): void
    {
        $group = new GroupElement();
        $group->addClass('layer');

        $this->assertTrue($group->hasClass('layer'));
        $this->assertSame(['layer'], $group->getClasses());
    }

    public function testAddClassWithEmptyString(): void
    {
        $element = new RectElement();
        $element->addClass('');

        $this->assertSame([], $element->getClasses());
    }

    public function testAddClassWithOnlyWhitespace(): void
    {
        $element = new RectElement();
        $element->addClass('   ');

        $this->assertSame([], $element->getClasses());
    }

    public function testComplexClassManipulation(): void
    {
        $element = new RectElement();

        // Start with some classes
        $element->addClass('btn btn-primary btn-large');
        $this->assertSame(['btn', 'btn-primary', 'btn-large'], $element->getClasses());

        // Remove one
        $element->removeClass('btn-large');
        $this->assertSame(['btn', 'btn-primary'], $element->getClasses());

        // Add new ones
        $element->addClass('active disabled');
        $this->assertSame(['btn', 'btn-primary', 'active', 'disabled'], $element->getClasses());

        // Toggle existing and new
        $element->toggleClass('active'); // Should remove
        $element->toggleClass('hover'); // Should add
        $this->assertSame(['btn', 'btn-primary', 'disabled', 'hover'], $element->getClasses());
    }
}
