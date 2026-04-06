<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveHiddenElementsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveHiddenElementsPass::class)]
final class RemoveHiddenElementsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveHiddenElementsPass();

        $this->assertSame('remove-hidden-elements', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new RemoveHiddenElementsPass();

        $this->assertInstanceOf(RemoveHiddenElementsPass::class, $pass);
    }

    public function testConstructorWithCustomOptions(): void
    {
        $pass = new RemoveHiddenElementsPass(
            removeDisplayNone: false,
            removeVisibilityHidden: false,
            removeOpacityZero: true,
            preserveWithId: false
        );

        $this->assertInstanceOf(RemoveHiddenElementsPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveDisplayNone(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('display', 'none');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Element with display="none" should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepDisplayNoneWhenDisabled(): void
    {
        $pass = new RemoveHiddenElementsPass(removeDisplayNone: false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('display', 'none');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element should be kept when removeDisplayNone is false
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemoveVisibilityHidden(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('visibility', 'hidden');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Element with visibility="hidden" should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepVisibilityHiddenWhenDisabled(): void
    {
        $pass = new RemoveHiddenElementsPass(removeVisibilityHidden: false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('visibility', 'hidden');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element should be kept when removeVisibilityHidden is false
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepOpacityZeroByDefault(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with opacity="0" should be kept by default
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemoveOpacityZeroWhenEnabled(): void
    {
        $pass = new RemoveHiddenElementsPass(removeOpacityZero: true);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Element with opacity="0" should be removed when enabled
        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveOpacityZeroFloat(): void
    {
        $pass = new RemoveHiddenElementsPass(removeOpacityZero: true);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '0.0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with opacity="0.0" should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepNonZeroOpacity(): void
    {
        $pass = new RemoveHiddenElementsPass(removeOpacityZero: true);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('opacity', '0.5');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with non-zero opacity should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testPreserveHiddenElementWithIdByDefault(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'important');
        $path->setAttribute('display', 'none');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with ID should be preserved by default
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemoveHiddenElementWithIdWhenDisabled(): void
    {
        $pass = new RemoveHiddenElementsPass(preserveWithId: false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'important');
        $path->setAttribute('display', 'none');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Element with ID should be removed when preserveWithId is false
        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveNestedHiddenElements(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('display', 'none');

        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $this->assertCount(1, $group->getChildren());

        $pass->optimize($document);

        // Nested hidden element should be removed
        $this->assertCount(0, $group->getChildren());
    }

    public function testMixedVisibleAndHiddenElements(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();

        $visiblePath = new PathElement();
        $hiddenPath1 = new PathElement();
        $hiddenPath1->setAttribute('display', 'none');
        $hiddenPath2 = new PathElement();
        $hiddenPath2->setAttribute('visibility', 'hidden');

        $svg->appendChild($visiblePath);
        $svg->appendChild($hiddenPath1);
        $svg->appendChild($hiddenPath2);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        // Only visible element should remain
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($visiblePath, $svg->getChildren()[0]);
    }

    public function testDisplayBlock(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('display', 'block');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with display="block" should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testVisibilityVisible(): void
    {
        $pass = new RemoveHiddenElementsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('visibility', 'visible');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element with visibility="visible" should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testMultipleHiddenProperties(): void
    {
        $pass = new RemoveHiddenElementsPass(removeOpacityZero: true);
        $svg = new SvgElement();

        $path = new PathElement();
        $path->setAttribute('display', 'none');
        $path->setAttribute('visibility', 'hidden');
        $path->setAttribute('opacity', '0');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Element with multiple hidden properties should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testOnlyRemoveFirstMatchingProperty(): void
    {
        // When an element matches display:none, it should be removed
        // even if other properties wouldn't trigger removal
        $pass = new RemoveHiddenElementsPass(
            removeDisplayNone: true,
            removeVisibilityHidden: false,
            removeOpacityZero: false
        );
        $svg = new SvgElement();

        $path1 = new PathElement();
        $path1->setAttribute('display', 'none');

        $path2 = new PathElement();
        $path2->setAttribute('visibility', 'hidden');

        $path3 = new PathElement();
        $path3->setAttribute('opacity', '0');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $svg->appendChild($path3);

        $document = new Document($svg);

        $this->assertCount(3, $svg->getChildren());

        $pass->optimize($document);

        // Only path1 should be removed
        $this->assertCount(2, $svg->getChildren());
    }

    public function testOpacityNullReturnsNotHidden(): void
    {
        $pass = new RemoveHiddenElementsPass(removeOpacityZero: true);
        $svg = new SvgElement();
        $path = new PathElement();
        // No opacity attribute set at all

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Element without opacity should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRealWorldScenario(): void
    {
        $pass = new RemoveHiddenElementsPass(
            removeDisplayNone: true,
            removeVisibilityHidden: true,
            removeOpacityZero: false,
            preserveWithId: true
        );
        $svg = new SvgElement();

        // Background layer (visible)
        $bg = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $bg->setAttribute('id', 'background');

        // Hidden guide layer
        $guide = new GroupElement();
        $guide->setAttribute('display', 'none');

        // Important hidden element with ID
        $overlay = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $overlay->setAttribute('id', 'overlay');
        $overlay->setAttribute('display', 'none');

        // Transparent element
        $transparent = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $transparent->setAttribute('opacity', '0');

        $svg->appendChild($bg);
        $svg->appendChild($guide);
        $svg->appendChild($overlay);
        $svg->appendChild($transparent);

        $document = new Document($svg);

        $pass->optimize($document);

        // bg, overlay (has ID), and transparent (opacity not removed) should remain
        // guide should be removed
        $this->assertCount(3, $svg->getChildren());
    }
}
