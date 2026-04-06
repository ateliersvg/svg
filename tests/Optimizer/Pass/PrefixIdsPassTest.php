<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\PrefixIdsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrefixIdsPass::class)]
final class PrefixIdsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new PrefixIdsPass();

        $this->assertSame('prefix-ids', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new PrefixIdsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testPrefixSimpleId(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // ID should be prefixed
        $this->assertSame('test__my-rect', $rect->getAttribute('id'));
    }

    public function testPrefixIdAndUrlReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad1');
        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#grad1)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // ID should be prefixed
        $this->assertSame('test__grad1', $gradient->getAttribute('id'));

        // Reference should be updated
        $this->assertSame('url(#test__grad1)', $rect->getAttribute('fill'));
    }

    public function testPrefixHrefReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'my-group');
        $svg->appendChild($group);

        $rect = new RectElement();
        $rect->setAttribute('href', '#my-group');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // ID should be prefixed
        $this->assertSame('test__my-group', $group->getAttribute('id'));

        // Reference should be updated
        $this->assertSame('#test__my-group', $rect->getAttribute('href'));
    }

    public function testPrefixXlinkHrefReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'template');
        $defs->appendChild($rect1);
        $svg->appendChild($defs);

        $rect2 = new RectElement();
        $rect2->setAttribute('xlink:href', '#template');
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // ID should be prefixed
        $this->assertSame('test__template', $rect1->getAttribute('id'));

        // Reference should be updated
        $this->assertSame('#test__template', $rect2->getAttribute('xlink:href'));
    }

    public function testPrefixMultipleReferences(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad');
        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $rect1 = new RectElement();
        $rect1->setAttribute('fill', 'url(#grad)');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('stroke', 'url(#grad)');
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // All references should be updated
        $this->assertSame('url(#test__grad)', $rect1->getAttribute('fill'));
        $this->assertSame('url(#test__grad)', $rect2->getAttribute('stroke'));
    }

    public function testPrefixClipPathReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'clip1');
        $defs->appendChild($group);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('clip-path', 'url(#clip1)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Reference should be updated
        $this->assertSame('url(#test__clip1)', $rect->getAttribute('clip-path'));
    }

    public function testPrefixMaskReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'mask1');
        $defs->appendChild($group);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('mask', 'url(#mask1)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Reference should be updated
        $this->assertSame('url(#test__mask1)', $rect->getAttribute('mask'));
    }

    public function testPrefixFilterReference(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'filter1');
        $defs->appendChild($group);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('filter', 'url(#filter1)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Reference should be updated
        $this->assertSame('url(#test__filter1)', $rect->getAttribute('filter'));
    }

    public function testPrefixMarkerReferences(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $marker = new GroupElement();
        $marker->setAttribute('id', 'arrow');
        $defs->appendChild($marker);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('marker-start', 'url(#arrow)');
        $rect->setAttribute('marker-mid', 'url(#arrow)');
        $rect->setAttribute('marker-end', 'url(#arrow)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // All marker references should be updated
        $this->assertSame('url(#test__arrow)', $rect->getAttribute('marker-start'));
        $this->assertSame('url(#test__arrow)', $rect->getAttribute('marker-mid'));
        $this->assertSame('url(#test__arrow)', $rect->getAttribute('marker-end'));
    }

    public function testPrefixStyleAttribute(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad1');
        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('style', 'fill: url(#grad1); stroke: url(#grad1);');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // References in style attribute should be updated
        $this->assertSame('fill: url(#test__grad1); stroke: url(#test__grad1);', $rect->getAttribute('style'));
    }

    public function testCustomDelimiter(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test', delimiter: '-');
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // ID should use custom delimiter
        $this->assertSame('test-my-rect', $rect->getAttribute('id'));
    }

    public function testNoIdsNoChanges(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // No IDs, so nothing should change
        $this->assertCount(1, $svg->getChildren());
    }

    public function testEmptyPrefixNoChanges(): void
    {
        $pass = new PrefixIdsPass(prefix: '');
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Empty prefix, so no changes
        $this->assertSame('my-rect', $rect->getAttribute('id'));
    }

    public function testNestedElements(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $group = new GroupElement();
        $group->setAttribute('id', 'group1');

        $rect = new RectElement();
        $rect->setAttribute('id', 'rect1');
        $group->appendChild($rect);

        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        // Both IDs should be prefixed
        $this->assertSame('test__group1', $group->getAttribute('id'));
        $this->assertSame('test__rect1', $rect->getAttribute('id'));
    }

    public function testMultipleIds(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'rect1');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'rect2');
        $svg->appendChild($rect2);

        $rect3 = new RectElement();
        $rect3->setAttribute('id', 'rect3');
        $svg->appendChild($rect3);

        $document = new Document($svg);
        $pass->optimize($document);

        // All IDs should be prefixed
        $this->assertSame('test__rect1', $rect1->getAttribute('id'));
        $this->assertSame('test__rect2', $rect2->getAttribute('id'));
        $this->assertSame('test__rect3', $rect3->getAttribute('id'));
    }

    public function testPreserveNonIdAttributes(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $rect->setAttribute('class', 'shape');
        $rect->setAttribute('fill', 'red');
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Only ID should change
        $this->assertSame('test__my-rect', $rect->getAttribute('id'));
        $this->assertSame('shape', $rect->getAttribute('class'));
        $this->assertSame('red', $rect->getAttribute('fill'));
        $this->assertSame('10', $rect->getAttribute('x'));
    }

    public function testAutoGeneratedPrefixWhenNoPrefixProvided(): void
    {
        $pass = new PrefixIdsPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'my-rect');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $id = $rect->getAttribute('id');
        $this->assertNotNull($id);
        // Auto-generated prefix starts with 'svg_' and uses '__' delimiter
        $this->assertStringStartsWith('svg_', $id);
        $this->assertStringContainsString('__my-rect', $id);
    }

    public function testUrlReferenceToNonExistentIdRemainsUnchanged(): void
    {
        $pass = new PrefixIdsPass(prefix: 'test');
        $svg = new SvgElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'existing');
        $svg->appendChild($rect1);

        $rect2 = new RectElement();
        // References an ID that does not exist in the document
        $rect2->setAttribute('fill', 'url(#nonexistent)');
        $svg->appendChild($rect2);

        $document = new Document($svg);
        $pass->optimize($document);

        // The reference to a non-existent ID should remain unchanged
        $this->assertSame('url(#nonexistent)', $rect2->getAttribute('fill'));
        // The existing ID should be prefixed
        $this->assertSame('test__existing', $rect1->getAttribute('id'));
    }
}
