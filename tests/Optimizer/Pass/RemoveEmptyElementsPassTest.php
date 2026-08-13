<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\ScriptElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\StyleElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyElementsPass;
use Atelier\Svg\Svg;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveEmptyElementsPass::class)]
final class RemoveEmptyElementsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveEmptyElementsPass();

        $this->assertSame('remove-empty-elements', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new RemoveEmptyElementsPass();

        $this->assertInstanceOf(RemoveEmptyElementsPass::class, $pass);
    }

    public function testConstructorWithCustomCheckableElements(): void
    {
        $pass = new RemoveEmptyElementsPass(['g', 'text']);

        $this->assertInstanceOf(RemoveEmptyElementsPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveEmptyGroup(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();

        $svg->appendChild($group);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Empty group should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepGroupWithChildren(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $group->appendChild($path);
        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Group with children should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepEmptyGroupWithId(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('id', 'important');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Empty group with ID should be kept (might be referenced)
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepEmptyGroupWithClass(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('class', 'styled');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Empty group with class should be kept (might be styled)
        $this->assertCount(1, $svg->getChildren());
    }

    public function testKeepEmptyGroupWithEventHandler(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('onclick', 'alert("clicked")');

        $svg->appendChild($group);
        $document = new Document($svg);

        $pass->optimize($document);

        // Empty group with event handler should be kept
        $this->assertCount(1, $svg->getChildren());
    }

    public function testRemoveEmptyText(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('text');
            }
        };

        $svg->appendChild($text);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Empty text should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveEmptyTspan(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('text');
            }
        };

        $tspan = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('tspan');
            }
        };

        $text->appendChild($tspan);
        $svg->appendChild($text);
        $document = new Document($svg);

        $this->assertCount(1, $text->getChildren());

        $pass->optimize($document);

        // Empty tspan should be removed, then empty text
        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepTextWithContent(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = TextElement::create(10, 40, 'Hello');

        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($text, $svg->getChildren()[0]);
    }

    public function testRemoveTextWithWhitespaceOnlyContent(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = TextElement::create(10, 40, "  \n\t ");

        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testKeepTspanWithContent(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = new TextElement();
        $tspan = new TspanElement();
        $tspan->setTextContent('Hello');

        $text->appendChild($tspan);
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);

        // The tspan holds the text, so neither it nor its parent is empty
        $this->assertCount(1, $svg->getChildren());
        $this->assertCount(1, $text->getChildren());
        $this->assertSame($tspan, $text->getChildren()[0]);
    }

    public function testRemoveTextWhoseOnlyTspanIsEmpty(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $text = new TextElement();
        $text->appendChild(new TspanElement());
        $svg->appendChild($text);
        $document = new Document($svg);

        $pass->optimize($document);

        // Bottom-up: the empty tspan goes first, leaving the text empty too
        $this->assertCount(0, $svg->getChildren());
    }

    public function testTextIsPreservedThroughEveryPreset(): void
    {
        $markup = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60">'
            .'<text x="10" y="40" font-size="20" fill="#000">Hello</text></svg>';

        foreach (['optimizeSafe', 'optimize', 'optimizeWeb', 'optimizeAggressive'] as $method) {
            $output = (string) Svg::fromString($markup)->{$method}();

            $this->assertStringContainsString('Hello', $output, $method.' dropped the text content');
            $this->assertStringContainsString('<text', $output, $method.' dropped the text element');
        }
    }

    public function testRemoveEmptyDefs(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $svg->appendChild($defs);
        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // Empty defs should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testRemoveNestedEmptyGroups(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $group1 = new GroupElement();
        $group2 = new GroupElement();
        $group3 = new GroupElement();

        $group1->appendChild($group2);
        $group2->appendChild($group3);
        $svg->appendChild($group1);

        $document = new Document($svg);

        $this->assertCount(1, $svg->getChildren());

        $pass->optimize($document);

        // All nested empty groups should be removed
        $this->assertCount(0, $svg->getChildren());
    }

    public function testCustomCheckableElements(): void
    {
        // Only check 'g' elements
        $pass = new RemoveEmptyElementsPass(['g']);
        $svg = new SvgElement();

        $group = new GroupElement();
        $text = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('text');
            }
        };

        $svg->appendChild($group);
        $svg->appendChild($text);
        $document = new Document($svg);

        $this->assertCount(2, $svg->getChildren());

        $pass->optimize($document);

        // Only empty group should be removed, text should stay
        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($text, $svg->getChildren()[0]);
    }

    public function testDoNotRemoveNonCheckableElements(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        // Empty rect (not in checkable list)
        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };

        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        // Non-checkable elements should not be removed
        $this->assertCount(1, $svg->getChildren());
    }

    public function testBottomUpProcessing(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        // Create nested structure where inner group becomes empty first
        $outerGroup = new GroupElement();
        $innerGroup = new GroupElement();
        $path = new PathElement();

        $innerGroup->appendChild($path);
        $outerGroup->appendChild($innerGroup);
        $svg->appendChild($outerGroup);

        $document = new Document($svg);

        // Manually remove the path to simulate bottom-up processing
        $innerGroup->removeChild($path);

        $pass->optimize($document);

        // Both empty groups should be removed (bottom-up)
        $this->assertCount(0, $svg->getChildren());
    }

    public function testPreserveGroupWithMixedContent(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $group = new GroupElement();
        $emptySubGroup = new GroupElement();
        $path = new PathElement();

        $group->appendChild($emptySubGroup);
        $group->appendChild($path);
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        // Outer group should be kept (has path)
        // Empty sub-group should be removed
        $this->assertCount(1, $svg->getChildren());
        $this->assertCount(1, $group->getChildren());
        $this->assertSame($path, $group->getChildren()[0]);
    }

    public function testEmptyNonContainerElementIsRemoved(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        // A non-container element in the checkable list (like script)
        $script = new ScriptElement();

        $svg->appendChild($script);
        $document = new Document($svg);

        $pass->optimize($document);

        // Nothing to run and nothing to reference: the element has no effect
        $this->assertCount(0, $svg->getChildren());
    }

    public function testNonContainerElementWithTextContentIsKept(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $style = new StyleElement();
        $style->setContent('.a{fill:red}');

        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($style, $svg->getChildren()[0]);
    }

    public function testNonContainerElementWithWhitespaceContentIsRemoved(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $style = new StyleElement();
        $style->setContent("  \n\t ");

        $svg->appendChild($style);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(0, $svg->getChildren());
    }

    public function testNonContainerElementWithExternalResourceIsKept(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        // An external script has no character data but still loads something
        $script = new ScriptElement();
        $script->setAttribute('href', 'app.js');

        $svg->appendChild($script);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertCount(1, $svg->getChildren());
        $this->assertSame($script, $svg->getChildren()[0]);
    }

    public function testMultipleEventHandlers(): void
    {
        $pass = new RemoveEmptyElementsPass();
        $svg = new SvgElement();

        $group1 = new GroupElement();
        $group1->setAttribute('onload', 'init()');

        $group2 = new GroupElement();
        $group2->setAttribute('onmouseover', 'highlight()');

        $group3 = new GroupElement();
        $group3->setAttribute('onfocus', 'focus()');

        $svg->appendChild($group1);
        $svg->appendChild($group2);
        $svg->appendChild($group3);

        $document = new Document($svg);

        $pass->optimize($document);

        // All groups with event handlers should be preserved
        $this->assertCount(3, $svg->getChildren());
    }
}
