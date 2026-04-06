<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveDuplicateDefsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveDuplicateDefsPass::class)]
final class RemoveDuplicateDefsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveDuplicateDefsPass();

        $this->assertSame('remove-duplicate-defs', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveDuplicateLinearGradient(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create two identical gradients
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');
        $grad1->setAttribute('y1', '0');
        $grad1->setAttribute('x2', '1');
        $grad1->setAttribute('y2', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');
        $grad2->setAttribute('y1', '0');
        $grad2->setAttribute('x2', '1');
        $grad2->setAttribute('y2', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        // Add elements using the gradients
        $rect1 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect1->setAttribute('fill', 'url(#grad1)');

        $rect2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect2->setAttribute('fill', 'url(#grad2)');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Only one gradient should remain
        $this->assertCount(1, $defs->getChildren());

        // Both references should point to grad1
        $this->assertSame('url(#grad1)', $rect1->getAttribute('fill'));
        $this->assertSame('url(#grad1)', $rect2->getAttribute('fill'));
    }

    public function testKeepNonDuplicateGradients(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create two different gradients
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');
        $grad1->setAttribute('y1', '0');
        $grad1->setAttribute('x2', '1');
        $grad1->setAttribute('y2', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');
        $grad2->setAttribute('y1', '0');
        $grad2->setAttribute('x2', '0');
        $grad2->setAttribute('y2', '1'); // Different

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Both gradients should remain (they're different)
        $this->assertCount(2, $defs->getChildren());
    }

    public function testRemoveDuplicateWithChildren(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create two identical gradients with stops
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');

        $stop1a = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop1a->setAttribute('offset', '0');
        $stop1a->setAttribute('stop-color', 'red');

        $stop1b = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop1b->setAttribute('offset', '1');
        $stop1b->setAttribute('stop-color', 'blue');

        $grad1->appendChild($stop1a);
        $grad1->appendChild($stop1b);

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');

        $stop2a = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop2a->setAttribute('offset', '0');
        $stop2a->setAttribute('stop-color', 'red');

        $stop2b = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop2b->setAttribute('offset', '1');
        $stop2b->setAttribute('stop-color', 'blue');

        $grad2->appendChild($stop2a);
        $grad2->appendChild($stop2b);

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('fill', 'url(#grad2)');
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Only one gradient should remain
        $this->assertCount(1, $defs->getChildren());

        // Reference should point to grad1
        $this->assertSame('url(#grad1)', $rect->getAttribute('fill'));
    }

    public function testKeepGradientsWithDifferentChildren(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create two gradients with different stops
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');

        $stop1 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop1->setAttribute('offset', '0');
        $stop1->setAttribute('stop-color', 'red');

        $grad1->appendChild($stop1);

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');

        $stop2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('stop');
            }
        };
        $stop2->setAttribute('offset', '0');
        $stop2->setAttribute('stop-color', 'blue'); // Different color

        $grad2->appendChild($stop2);

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Both gradients should remain (different children)
        $this->assertCount(2, $defs->getChildren());
    }

    public function testUpdateMultipleReferences(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create duplicate gradients
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        // Multiple elements using grad2
        $rect1 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect1->setAttribute('fill', 'url(#grad2)');

        $rect2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect2->setAttribute('stroke', 'url(#grad2)');

        $rect3 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect3->setAttribute('style', 'fill: url(#grad2); stroke: black');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);
        $svg->appendChild($rect3);

        $document = new Document($svg);

        $pass->optimize($document);

        // All references should be updated
        $this->assertSame('url(#grad1)', $rect1->getAttribute('fill'));
        $this->assertSame('url(#grad1)', $rect2->getAttribute('stroke'));
        $this->assertSame('fill: url(#grad1); stroke: black', $rect3->getAttribute('style'));
    }

    public function testUpdateHrefStyleReferences(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create duplicate patterns
        $pattern1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('pattern');
            }
        };
        $pattern1->setAttribute('id', 'pattern1');

        $pattern2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('pattern');
            }
        };
        $pattern2->setAttribute('id', 'pattern2');

        $defs->appendChild($pattern1);
        $defs->appendChild($pattern2);
        $svg->appendChild($defs);

        // Use element with href
        $use = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('use');
            }
        };
        $use->setAttribute('href', '#pattern2');

        $svg->appendChild($use);

        $document = new Document($svg);

        $pass->optimize($document);

        // href reference should be updated
        $this->assertSame('#pattern1', $use->getAttribute('href'));
    }

    public function testRemoveMultipleDuplicates(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create three identical gradients
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');

        $grad3 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad3->setAttribute('id', 'grad3');
        $grad3->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $defs->appendChild($grad3);
        $svg->appendChild($defs);

        $rect1 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect1->setAttribute('fill', 'url(#grad2)');

        $rect2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect2->setAttribute('fill', 'url(#grad3)');

        $svg->appendChild($rect1);
        $svg->appendChild($rect2);

        $document = new Document($svg);

        $this->assertCount(3, $defs->getChildren());

        $pass->optimize($document);

        // Only one gradient should remain
        $this->assertCount(1, $defs->getChildren());

        // All references should point to grad1
        $this->assertSame('url(#grad1)', $rect1->getAttribute('fill'));
        $this->assertSame('url(#grad1)', $rect2->getAttribute('fill'));
    }

    public function testHandleNestedDefs(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        // Top-level defs
        $defs1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');

        $defs1->appendChild($grad1);
        $svg->appendChild($defs1);

        // Nested defs in a group
        $group = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('g');
            }
        };

        $defs2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');

        $defs2->appendChild($grad2);
        $group->appendChild($defs2);
        $svg->appendChild($group);

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('fill', 'url(#grad2)');
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(1, $defs1->getChildren());
        $this->assertCount(1, $defs2->getChildren());

        $pass->optimize($document);

        // grad2 should be removed from nested defs
        $this->assertCount(1, $defs1->getChildren());
        $this->assertCount(0, $defs2->getChildren());

        // Reference should be updated to grad1
        $this->assertSame('url(#grad1)', $rect->getAttribute('fill'));
    }

    public function testUnregisterRemovedElementIds(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $document = new Document($svg);

        // Verify both IDs are registered
        $this->assertNotNull($document->getElementById('grad1'));
        $this->assertNotNull($document->getElementById('grad2'));

        $pass->optimize($document);

        // grad2 should be unregistered
        $this->assertNotNull($document->getElementById('grad1'));
        $this->assertNull($document->getElementById('grad2'));
    }

    public function testIgnoreDefsWithoutId(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Element without ID
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $this->assertCount(1, $defs->getChildren());

        $pass->optimize($document);

        // Element without ID should be kept
        $this->assertCount(1, $defs->getChildren());
    }

    public function testHandleRadialGradients(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create duplicate radial gradients
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('radialGradient');
            }
        };
        $grad1->setAttribute('id', 'radial1');
        $grad1->setAttribute('cx', '0.5');
        $grad1->setAttribute('cy', '0.5');
        $grad1->setAttribute('r', '0.5');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('radialGradient');
            }
        };
        $grad2->setAttribute('id', 'radial2');
        $grad2->setAttribute('cx', '0.5');
        $grad2->setAttribute('cy', '0.5');
        $grad2->setAttribute('r', '0.5');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $circle = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('circle');
            }
        };
        $circle->setAttribute('fill', 'url(#radial2)');
        $svg->appendChild($circle);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Only one radial gradient should remain
        $this->assertCount(1, $defs->getChildren());
        $this->assertSame('url(#radial1)', $circle->getAttribute('fill'));
    }

    public function testDoNotMergeDifferentTypes(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create linear and radial gradient with same attributes
        $linear = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $linear->setAttribute('id', 'linear1');

        $radial = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('radialGradient');
            }
        };
        $radial->setAttribute('id', 'radial1');

        $defs->appendChild($linear);
        $defs->appendChild($radial);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Both should remain (different tag names)
        $this->assertCount(2, $defs->getChildren());
    }

    public function testDefWithoutIdIsSkipped(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        // No id attribute set

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        $grad2->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $pass->optimize($document);

        // Both should remain since grad1 has no id and is skipped
        $this->assertCount(2, $defs->getChildren());
    }

    public function testAttributeOrderDoesNotMatter(): void
    {
        $pass = new RemoveDuplicateDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Create two gradients with same attributes in different order
        $grad1 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad1->setAttribute('id', 'grad1');
        $grad1->setAttribute('x1', '0');
        $grad1->setAttribute('x2', '1');
        $grad1->setAttribute('y1', '0');
        $grad1->setAttribute('y2', '0');

        $grad2 = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $grad2->setAttribute('id', 'grad2');
        // Set in different order
        $grad2->setAttribute('y2', '0');
        $grad2->setAttribute('y1', '0');
        $grad2->setAttribute('x2', '1');
        $grad2->setAttribute('x1', '0');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $svg->appendChild($defs);

        $rect = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $rect->setAttribute('fill', 'url(#grad2)');
        $svg->appendChild($rect);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Should be treated as duplicates despite different order
        $this->assertCount(1, $defs->getChildren());
        $this->assertSame('url(#grad1)', $rect->getAttribute('fill'));
    }
}
