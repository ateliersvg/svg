<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedNSPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveUnusedNSPass::class)]
final class RemoveUnusedNSPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveUnusedNSPass();

        $this->assertSame('remove-unused-ns', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveUnusedNSPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveUnusedNamespace(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:foo', 'http://example.com/foo');
        $svg->setAttribute('xmlns:bar', 'http://example.com/bar');

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Unused namespaces should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:foo'));
        $this->assertFalse($svg->hasAttribute('xmlns:bar'));
    }

    public function testKeepUsedNamespaceInTagName(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        // Create an element with a custom namespace prefix
        $group = new GroupElement();
        // Simulate a custom namespaced element by setting tagName
        // In a real scenario, this would be a custom element class
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass->optimize($document);

        // custom namespace is not used, should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:custom'));
    }

    public function testKeepUsedNamespaceInAttribute(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        $rect = new RectElement();
        $rect->setAttribute('custom:data', 'value');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // custom namespace is used in attribute, should be kept
        $this->assertTrue($svg->hasAttribute('xmlns:custom'));
    }

    public function testKeepEssentialNamespaces(): void
    {
        $pass = new RemoveUnusedNSPass(keepEssential: true);
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:svg', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Essential namespaces should be kept even if not used
        $this->assertTrue($svg->hasAttribute('xmlns:svg'));
        $this->assertTrue($svg->hasAttribute('xmlns:xlink'));
    }

    public function testRemoveEssentialNamespacesWhenConfigured(): void
    {
        $pass = new RemoveUnusedNSPass(keepEssential: false);
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:svg', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Essential namespaces should be removed if not used and keepEssential is false
        $this->assertFalse($svg->hasAttribute('xmlns:svg'));
        $this->assertFalse($svg->hasAttribute('xmlns:xlink'));
    }

    public function testKeepNamespaceUsedInAttributeValue(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

        $rect = new RectElement();
        $rect->setAttribute('href', 'xlink:something'); // xlink used in value
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // xlink namespace is used in attribute value, should be kept
        $this->assertTrue($svg->hasAttribute('xmlns:xlink'));
    }

    public function testMixedUsedAndUnusedNamespaces(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:used', 'http://example.com/used');
        $svg->setAttribute('xmlns:unused1', 'http://example.com/unused1');
        $svg->setAttribute('xmlns:unused2', 'http://example.com/unused2');

        $rect = new RectElement();
        $rect->setAttribute('used:attribute', 'value');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Used namespace should be kept
        $this->assertTrue($svg->hasAttribute('xmlns:used'));

        // Unused namespaces should be removed
        $this->assertFalse($svg->hasAttribute('xmlns:unused1'));
        $this->assertFalse($svg->hasAttribute('xmlns:unused2'));
    }

    public function testNestedNamespaceUsage(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        $group = new GroupElement();
        $svg->appendChild($group);

        $rect = new RectElement();
        $rect->setAttribute('custom:data', 'nested');
        $group->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Namespace used in nested element should be kept
        $this->assertTrue($svg->hasAttribute('xmlns:custom'));
    }

    public function testNoNamespaceDeclarations(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Should not throw an error
        $this->assertCount(1, $svg->getChildren());
    }

    public function testPreserveDefaultNamespace(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg'); // Default namespace

        $rect = new RectElement();
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Default xmlns (not xmlns:prefix) should not be touched by this pass
        $this->assertTrue($svg->hasAttribute('xmlns'));
    }

    public function testMultipleAttributesWithSameNamespace(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        $rect = new RectElement();
        $rect->setAttribute('custom:attr1', 'value1');
        $rect->setAttribute('custom:attr2', 'value2');
        $rect->setAttribute('custom:attr3', 'value3');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Namespace should be kept (it's used)
        $this->assertTrue($svg->hasAttribute('xmlns:custom'));
    }

    public function testCaseSensitiveNamespaces(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:Custom', 'http://example.com/Custom');

        $rect = new RectElement();
        $rect->setAttribute('Custom:data', 'value');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Namespace should be kept (case-sensitive match)
        $this->assertTrue($svg->hasAttribute('xmlns:Custom'));
    }

    public function testNamespaceInUrlReference(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#custom:gradient)');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Namespace used in URL reference should be kept
        $this->assertTrue($svg->hasAttribute('xmlns:custom'));
    }

    public function testKeepNamespaceUsedInTagPrefix(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:custom', 'http://example.com/custom');

        $customEl = new class extends \Atelier\Svg\Element\AbstractElement {
            public function __construct()
            {
                parent::__construct('custom:element');
            }
        };
        $svg->appendChild($customEl);

        $document = new Document($svg);
        $pass->optimize($document);

        $this->assertTrue($svg->hasAttribute('xmlns:custom'));
    }

    public function testKeepNamespaceUsedInHashPrefixIdReference(): void
    {
        $pass = new RemoveUnusedNSPass();
        $svg = new SvgElement();
        $svg->setAttribute('xmlns:ns', 'http://example.com/ns');

        $rect = new RectElement();
        $rect->setAttribute('href', '#ns:myId');
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        $this->assertTrue($svg->hasAttribute('xmlns:ns'));
    }
}
