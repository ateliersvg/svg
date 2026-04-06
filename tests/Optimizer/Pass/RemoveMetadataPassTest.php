<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveMetadataPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveMetadataPass::class)]
final class RemoveMetadataPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveMetadataPass();

        $this->assertSame('remove-metadata', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new RemoveMetadataPass();

        // Just verify it constructs without error
        $this->assertInstanceOf(RemoveMetadataPass::class, $pass);
    }

    public function testConstructorWithOptions(): void
    {
        $pass = new RemoveMetadataPass(false, true);

        // Just verify it constructs without error
        $this->assertInstanceOf(RemoveMetadataPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveMetadataPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveMetadataElement(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();

        // Create a metadata element (using AbstractElement to simulate it)
        $metadata = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('metadata');
            }
        };

        $svg->appendChild($metadata);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveDescElementByDefault(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();

        $desc = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('desc');
            }
        };

        $svg->appendChild($desc);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepDescElementWhenConfigured(): void
    {
        $pass = new RemoveMetadataPass(false);
        $svg = new SvgElement();

        $desc = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('desc');
            }
        };

        $svg->appendChild($desc);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepTitleElementByDefault(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();

        $title = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('title');
            }
        };

        $svg->appendChild($title);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemoveTitleElementWhenConfigured(): void
    {
        $pass = new RemoveMetadataPass(true, true);
        $svg = new SvgElement();

        $title = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('title');
            }
        };

        $svg->appendChild($title);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveEditorAttributes(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();
        $svg->setAttribute('inkscape:version', '1.0');
        $svg->setAttribute('sodipodi:docname', 'test.svg');
        $svg->setAttribute('sketch:id', '123');
        $svg->setAttribute('width', '100');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertFalse($svg->hasAttribute('inkscape:version'));
        $this->assertFalse($svg->hasAttribute('sodipodi:docname'));
        $this->assertFalse($svg->hasAttribute('sketch:id'));
        $this->assertTrue($svg->hasAttribute('width'));
    }

    public function testRemoveMetadataFromNestedElements(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $metadata = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('metadata');
            }
        };

        $svg->appendChild($group);
        $group->appendChild($metadata);
        $group->appendChild($path);

        $document = new Document($svg);

        $this->assertCount(2, $group->getChildren());

        $pass->optimize($document);

        $this->assertCount(1, $group->getChildren());
        $this->assertSame($path, $group->getChildren()[0]);
    }

    public function testPreserveNonMetadataElements(): void
    {
        $pass = new RemoveMetadataPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();

        $svg->appendChild($group);
        $svg->appendChild($path1);
        $group->appendChild($path2);

        $document = new Document($svg);

        $originalChildCount = $svg->getChildCount();

        $pass->optimize($document);

        // All non-metadata elements should be preserved
        $this->assertCount($originalChildCount, $svg->getChildren());
    }
}
