<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\CleanupEnableBackgroundPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleanupEnableBackgroundPass::class)]
final class CleanupEnableBackgroundPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new CleanupEnableBackgroundPass();
        $this->assertSame('cleanup-enable-background', $pass->getName());
    }

    public function testRemovesEnableBackground(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('enable-background', 'new 0 0 100 100');
        $group->setAttribute('fill', 'red');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass = new CleanupEnableBackgroundPass();
        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('enable-background'));
        $this->assertTrue($group->hasAttribute('fill'), 'Should preserve other attributes');
    }

    public function testRemovesEnableBackgroundFromMultipleElements(): void
    {
        $svg = new SvgElement();
        $group1 = new GroupElement();
        $group2 = new GroupElement();
        $group1->setAttribute('enable-background', 'new');
        $group2->setAttribute('enable-background', 'accumulate');

        $svg->appendChild($group1);
        $svg->appendChild($group2);
        $document = new Document($svg);

        $pass = new CleanupEnableBackgroundPass();
        $pass->optimize($document);

        $this->assertFalse($group1->hasAttribute('enable-background'));
        $this->assertFalse($group2->hasAttribute('enable-background'));
    }

    public function testHandlesNestedElements(): void
    {
        $svg = new SvgElement();
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $outerGroup->setAttribute('enable-background', 'new');
        $innerGroup->setAttribute('enable-background', 'new');

        $svg->appendChild($outerGroup);
        $outerGroup->appendChild($innerGroup);
        $document = new Document($svg);

        $pass = new CleanupEnableBackgroundPass();
        $pass->optimize($document);

        $this->assertFalse($outerGroup->hasAttribute('enable-background'));
        $this->assertFalse($innerGroup->hasAttribute('enable-background'));
    }

    public function testHandlesElementsWithoutEnableBackground(): void
    {
        $svg = new SvgElement();
        $rect = new RectElement();
        $rect->setAttribute('width', '100');

        $svg->appendChild($rect);
        $document = new Document($svg);

        $pass = new CleanupEnableBackgroundPass();
        $pass->optimize($document);

        $this->assertTrue($rect->hasAttribute('width'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new CleanupEnableBackgroundPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }
}
