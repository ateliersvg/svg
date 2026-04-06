<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\CleanupIdsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleanupIdsPass::class)]
final class CleanupIdsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new CleanupIdsPass();

        $this->assertSame('cleanup-ids', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new CleanupIdsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveUnusedId(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'unused');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($path->hasAttribute('id'));
    }

    public function testPreserveUsedIdInHref(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('id', 'myRect');
        $use = new GroupElement();
        $use->setAttribute('href', '#myRect');

        $group->appendChild($path);
        $svg->appendChild($group);
        $svg->appendChild($use);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('id'));
        $this->assertSame('myRect', $path->getAttribute('id'));
    }

    public function testPreserveUsedIdInUrl(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('id', 'gradient');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'url(#gradient)');

        $group->appendChild($path);
        $svg->appendChild($group);
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('id'));
        $this->assertSame('gradient', $path->getAttribute('id'));
    }

    public function testMinifyIds(): void
    {
        $pass = new CleanupIdsPass(false, true);
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setAttribute('id', 'veryLongIdName1');
        $path2 = new PathElement();
        $path2->setAttribute('id', 'veryLongIdName2');
        $path2->setAttribute('href', '#veryLongIdName1');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('a', $path1->getAttribute('id'));
        $this->assertSame('b', $path2->getAttribute('id'));
        $this->assertSame('#a', $path2->getAttribute('href'));
    }

    public function testMinifyIdsWithPrefix(): void
    {
        $pass = new CleanupIdsPass(false, true, 'svg-');
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'myId');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('svg-a', $path->getAttribute('id'));
    }

    public function testPrefixWithoutMinify(): void
    {
        $pass = new CleanupIdsPass(false, false, 'prefix-');
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'myId');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'url(#myId)');

        $svg->appendChild($path);
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('prefix-myId', $path->getAttribute('id'));
        $this->assertSame('url(#prefix-myId)', $path2->getAttribute('fill'));
    }

    public function testPreserveSpecificIds(): void
    {
        $pass = new CleanupIdsPass(false, true, '', ['keep-*']);
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setAttribute('id', 'keep-this');
        $path2 = new PathElement();
        $path2->setAttribute('id', 'minify-this');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('keep-this', $path1->getAttribute('id'));
        $this->assertSame('a', $path2->getAttribute('id'));
    }

    public function testRemoveUnusedButPreservePattern(): void
    {
        $pass = new CleanupIdsPass(true, false, '', ['important-*']);
        $svg = new SvgElement();
        $path1 = new PathElement();
        $path1->setAttribute('id', 'important-unused');
        $path2 = new PathElement();
        $path2->setAttribute('id', 'unused');

        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path1->hasAttribute('id'));
        $this->assertSame('important-unused', $path1->getAttribute('id'));
        $this->assertFalse($path2->hasAttribute('id'));
    }

    public function testMinifySequence(): void
    {
        $pass = new CleanupIdsPass(false, true);
        $svg = new SvgElement();

        // Create 27 elements to test sequence: a-z, aa
        for ($i = 0; $i < 27; ++$i) {
            $path = new PathElement();
            $path->setAttribute('id', 'id'.$i);
            $svg->appendChild($path);
        }

        $document = new Document($svg);

        $pass->optimize($document);

        $children = $svg->getChildren();
        $this->assertSame('a', $children[0]->getAttribute('id'));
        $this->assertSame('z', $children[25]->getAttribute('id'));
        $this->assertSame('aa', $children[26]->getAttribute('id'));
    }

    public function testPreserveXlinkHref(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();
        $path->setAttribute('id', 'myPath');
        $use = new GroupElement();
        $use->setAttribute('xlink:href', '#myPath');

        $group->appendChild($path);
        $svg->appendChild($group);
        $svg->appendChild($use);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($path->hasAttribute('id'));
        $this->assertSame('myPath', $path->getAttribute('id'));
    }

    public function testReplaceMultipleReferences(): void
    {
        $pass = new CleanupIdsPass(false, true);
        $svg = new SvgElement();
        $group = new GroupElement();
        $gradient = new GroupElement();
        $gradient->setAttribute('id', 'gradient');

        $path1 = new PathElement();
        $path1->setAttribute('fill', 'url(#gradient)');
        $path2 = new PathElement();
        $path2->setAttribute('fill', 'url(#gradient)');
        $path3 = new PathElement();
        $path3->setAttribute('stroke', 'url(#gradient)');

        $group->appendChild($gradient);
        $svg->appendChild($group);
        $svg->appendChild($path1);
        $svg->appendChild($path2);
        $svg->appendChild($path3);
        $document = new Document($svg);

        $pass->optimize($document);

        $newId = $gradient->getAttribute('id');
        $this->assertSame('a', $newId);
        $this->assertSame('url(#a)', $path1->getAttribute('fill'));
        $this->assertSame('url(#a)', $path2->getAttribute('fill'));
        $this->assertSame('url(#a)', $path3->getAttribute('stroke'));
    }

    public function testClipPathReference(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $clipPath = new GroupElement();
        $clipPath->setAttribute('id', 'clip');
        $path = new PathElement();
        $path->setAttribute('clip-path', 'url(#clip)');

        $group->appendChild($clipPath);
        $svg->appendChild($group);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($clipPath->hasAttribute('id'));
        $this->assertSame('clip', $clipPath->getAttribute('id'));
    }

    public function testFilterReference(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $filter = new GroupElement();
        $filter->setAttribute('id', 'blur');
        $path = new PathElement();
        $path->setAttribute('filter', 'url(#blur)');

        $group->appendChild($filter);
        $svg->appendChild($group);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($filter->hasAttribute('id'));
        $this->assertSame('blur', $filter->getAttribute('id'));
    }

    public function testMaskReference(): void
    {
        $pass = new CleanupIdsPass(true, false);
        $svg = new SvgElement();
        $group = new GroupElement();
        $mask = new GroupElement();
        $mask->setAttribute('id', 'mask1');
        $path = new PathElement();
        $path->setAttribute('mask', 'url(#mask1)');

        $group->appendChild($mask);
        $svg->appendChild($group);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertTrue($mask->hasAttribute('id'));
        $this->assertSame('mask1', $mask->getAttribute('id'));
    }

    public function testDisableRemoval(): void
    {
        $pass = new CleanupIdsPass(false, false);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setAttribute('id', 'unused');
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Should not remove even though unused
        $this->assertTrue($path->hasAttribute('id'));
        $this->assertSame('unused', $path->getAttribute('id'));
    }

    public function testMinifyReplacesUrlReferencesInNonStandardAttributes(): void
    {
        $pass = new CleanupIdsPass(false, true);
        $svg = new SvgElement();

        $gradient = new GroupElement();
        $gradient->setAttribute('id', 'myGradient');

        $path = new PathElement();
        // Use a non-standard attribute that has url() reference
        $path->setAttribute('custom-attr', 'url(#myGradient)');

        $svg->appendChild($gradient);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // The url() reference in custom-attr should be updated
        $this->assertSame('url(#a)', $path->getAttribute('custom-attr'));
    }

    public function testMinifyPreservesNonMappedUrlReferences(): void
    {
        $pass = new CleanupIdsPass(false, true, '', ['preserved-*']);
        $svg = new SvgElement();

        $gradient = new GroupElement();
        $gradient->setAttribute('id', 'preserved-grad');

        $path = new PathElement();
        $path->setAttribute('fill', 'url(#preserved-grad)');

        $svg->appendChild($gradient);
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // preserved-grad should not be minified
        $this->assertSame('preserved-grad', $gradient->getAttribute('id'));
        $this->assertSame('url(#preserved-grad)', $path->getAttribute('fill'));
    }
}
