<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Optimizer\Pass\PreservingAttributesTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(PreservingAttributesTrait::class)]
final class PreservingAttributesTraitTest extends TestCase
{
    private object $traitUser;

    protected function setUp(): void
    {
        $this->traitUser = new class {
            use PreservingAttributesTrait {
                hasPreservingAttributes as public;
                getDefaultPreservingAttributes as public;
            }
        };
    }

    public function testGetDefaultPreservingAttributes(): void
    {
        $defaults = $this->traitUser->getDefaultPreservingAttributes();

        $this->assertIsArray($defaults);
        $this->assertContains('id', $defaults);
        $this->assertContains('class', $defaults);
        $this->assertContains('onclick', $defaults);
        $this->assertContains('onload', $defaults);
        $this->assertContains('onmouseover', $defaults);
    }

    public function testHasPreservingAttributesReturnsTrueWhenIdPresent(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'my-id');

        $result = $this->traitUser->hasPreservingAttributes($element, ['id', 'class']);

        $this->assertTrue($result);
    }

    public function testHasPreservingAttributesReturnsTrueWhenClassPresent(): void
    {
        $element = new RectElement();
        $element->setAttribute('class', 'my-class');

        $result = $this->traitUser->hasPreservingAttributes($element, ['id', 'class']);

        $this->assertTrue($result);
    }

    public function testHasPreservingAttributesReturnsFalseWhenNoPreservingAttribute(): void
    {
        $element = new RectElement();
        $element->setAttribute('fill', 'red');
        $element->setAttribute('stroke', 'blue');

        $result = $this->traitUser->hasPreservingAttributes($element, ['id', 'class']);

        $this->assertFalse($result);
    }

    public function testHasPreservingAttributesWithEmptyList(): void
    {
        $element = new RectElement();
        $element->setAttribute('id', 'my-id');

        $result = $this->traitUser->hasPreservingAttributes($element, []);

        $this->assertFalse($result);
    }

    public function testHasPreservingAttributesWithEventHandler(): void
    {
        $element = new RectElement();
        $element->setAttribute('onclick', "alert('clicked')");

        $defaults = $this->traitUser->getDefaultPreservingAttributes();
        $result = $this->traitUser->hasPreservingAttributes($element, $defaults);

        $this->assertTrue($result);
    }
}
