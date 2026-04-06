<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyAttrsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEmptyAttrsPass::class)]
final class RemoveEmptyAttrsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveEmptyAttrsPass();

        $this->assertSame('remove-empty-attrs', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new RemoveEmptyAttrsPass();

        $this->assertInstanceOf(RemoveEmptyAttrsPass::class, $pass);
    }

    public function testConstructorWithCustomPreserveList(): void
    {
        $pass = new RemoveEmptyAttrsPass(['alt', 'title']);

        $this->assertInstanceOf(RemoveEmptyAttrsPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveEmptyStringAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-value', '');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertTrue($path->hasAttribute('data-value'));

        $pass->optimize($document);

        // Empty attribute should be removed
        $this->assertFalse($path->hasAttribute('data-value'));
    }

    public function testRemoveWhitespaceOnlyAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-value', '   ');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertTrue($path->hasAttribute('data-value'));

        $pass->optimize($document);

        // Whitespace-only attribute should be removed
        $this->assertFalse($path->hasAttribute('data-value'));
    }

    public function testRemoveTabsAndNewlines(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-value', "\t\n  \r\n");

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertTrue($path->hasAttribute('data-value'));

        $pass->optimize($document);

        // Whitespace with tabs/newlines should be removed
        $this->assertFalse($path->hasAttribute('data-value'));
    }

    public function testKeepNonEmptyAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-value', 'content');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Non-empty attribute should be kept
        $this->assertTrue($path->hasAttribute('data-value'));
        $this->assertSame('content', $path->getAttribute('data-value'));
    }

    public function testPreserveAltAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $svg->setAttribute('alt', '');

        $document = new Document($svg);

        $pass->optimize($document);

        // Empty alt should be preserved (meaningful for accessibility)
        $this->assertTrue($svg->hasAttribute('alt'));
    }

    public function testPreserveRoleAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $svg->setAttribute('role', '');

        $document = new Document($svg);

        $pass->optimize($document);

        // Empty role should be preserved
        $this->assertTrue($svg->hasAttribute('role'));
    }

    public function testPreserveAriaLabelAttribute(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $svg->setAttribute('aria-label', '');

        $document = new Document($svg);

        $pass->optimize($document);

        // Empty aria-label should be preserved
        $this->assertTrue($svg->hasAttribute('aria-label'));
    }

    public function testCustomPreserveList(): void
    {
        $pass = new RemoveEmptyAttrsPass(['title']);
        $svg = new SvgElement();
        $svg->setAttribute('title', '');
        $svg->setAttribute('alt', '');
        $svg->setAttribute('data-value', '');

        $document = new Document($svg);

        $pass->optimize($document);

        // Only title should be preserved (custom list)
        $this->assertTrue($svg->hasAttribute('title'));
        $this->assertFalse($svg->hasAttribute('alt'));
        $this->assertFalse($svg->hasAttribute('data-value'));
    }

    public function testRemoveMultipleEmptyAttributes(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-a', '');
        $path->setAttribute('data-b', '  ');
        $path->setAttribute('data-c', "\t");
        $path->setAttribute('data-d', 'value');

        $svg->appendChild($path);
        $document = new Document($svg);

        $this->assertCount(4, $path->getAttributes());

        $pass->optimize($document);

        // Three empty attributes should be removed, one kept
        $this->assertCount(1, $path->getAttributes());
        $this->assertTrue($path->hasAttribute('data-d'));
    }

    public function testProcessNestedElements(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->setAttribute('data-empty', '');
        $group->setAttribute('data-empty', '   ');
        $path->setAttribute('data-empty', "\t\n");

        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // All empty attributes should be removed from all levels
        $this->assertFalse($svg->hasAttribute('data-empty'));
        $this->assertFalse($group->hasAttribute('data-empty'));
        $this->assertFalse($path->hasAttribute('data-empty'));
    }

    public function testMixedPreserveAndRemove(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $svg->setAttribute('alt', '');           // Preserve
        $svg->setAttribute('data-value', '');    // Remove
        $svg->setAttribute('role', '   ');       // Preserve
        $svg->setAttribute('class', '  ');       // Remove

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($svg->hasAttribute('alt'));
        $this->assertFalse($svg->hasAttribute('data-value'));
        $this->assertTrue($svg->hasAttribute('role'));
        $this->assertFalse($svg->hasAttribute('class'));
    }

    public function testEmptyPreserveList(): void
    {
        // Empty preserve list means nothing is preserved
        $pass = new RemoveEmptyAttrsPass([]);
        $svg = new SvgElement();
        $svg->setAttribute('alt', '');
        $svg->setAttribute('role', '');
        $svg->setAttribute('data-value', '');

        $document = new Document($svg);

        $pass->optimize($document);

        // All empty attributes should be removed (no preserve list)
        $this->assertFalse($svg->hasAttribute('alt'));
        $this->assertFalse($svg->hasAttribute('role'));
        $this->assertFalse($svg->hasAttribute('data-value'));
    }

    public function testDoNotRemoveAttributesWithValue(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();

        $path->setAttribute('fill', 'red');
        $path->setAttribute('stroke', 'blue');
        $path->setAttribute('d', 'M 0 0 L 10 10');
        $path->setAttribute('id', 'path1');

        $svg->appendChild($path);
        $document = new Document($svg);

        $originalCount = count($path->getAttributes());

        $pass->optimize($document);

        // All attributes with values should be kept
        $this->assertCount($originalCount, $path->getAttributes());
    }

    public function testAttributeWithLeadingTrailingSpaces(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('data-a', '  value  ');

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Attribute with content (even with spaces) should be kept
        $this->assertTrue($path->hasAttribute('data-a'));
    }

    public function testRealWorldScenario(): void
    {
        $pass = new RemoveEmptyAttrsPass();
        $svg = new SvgElement();

        // Image element with empty alt (should be preserved)
        $image = new class extends \Atelier\Svg\Element\AbstractElement {
            public function __construct()
            {
                parent::__construct('image');
            }
        };
        $image->setAttribute('alt', '');
        $image->setAttribute('href', 'image.png');
        $image->setAttribute('data-caption', '');    // Should be removed

        // Group with various attributes
        $group = new GroupElement();
        $group->setAttribute('id', 'layer1');
        $group->setAttribute('data-visible', '');    // Should be removed
        $group->setAttribute('transform', 'translate(10, 20)');

        $svg->appendChild($image);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Check image
        $this->assertTrue($image->hasAttribute('alt'));          // Preserved
        $this->assertTrue($image->hasAttribute('href'));         // Has value
        $this->assertFalse($image->hasAttribute('data-caption')); // Removed

        // Check group
        $this->assertTrue($group->hasAttribute('id'));           // Has value
        $this->assertFalse($group->hasAttribute('data-visible')); // Removed
        $this->assertTrue($group->hasAttribute('transform'));    // Has value
    }
}
