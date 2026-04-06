<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\AbstractContainerElement;
use Atelier\Svg\Element\AbstractElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedDefsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveUnusedDefsPass::class)]
final class RemoveUnusedDefsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveUnusedDefsPass();

        $this->assertSame('remove-unused-defs', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRemoveUnusedDefinition(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        // Create a defs element
        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Add an unused gradient to defs
        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $gradient->setAttribute('id', 'unused-gradient');

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $this->assertCount(1, $defs->getChildren());

        $pass->optimize($document);

        // Unused gradient should be removed
        $this->assertCount(0, $defs->getChildren());
    }

    public function testKeepUsedDefinitionWithHref(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $gradient->setAttribute('id', 'used-gradient');

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        // Reference the gradient with href
        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $path->setAttribute('fill', 'url(#used-gradient)');
        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // Gradient should be kept
        $this->assertCount(1, $defs->getChildren());
    }

    public function testKeepUsedDefinitionWithUrl(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $clipPath = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('clipPath');
            }
        };
        $clipPath->setAttribute('id', 'clip1');

        $defs->appendChild($clipPath);
        $svg->appendChild($defs);

        // Reference the clipPath
        $path2 = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('circle');
            }
        };
        $path2->setAttribute('clip-path', 'url(#clip1)');
        $svg->appendChild($path2);

        $document = new Document($svg);

        $pass->optimize($document);

        // ClipPath should be kept
        $this->assertCount(1, $defs->getChildren());
    }

    public function testRemoveMixedUsedAndUnusedDefinitions(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Add used gradient
        $usedGradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $usedGradient->setAttribute('id', 'used');

        // Add unused gradient
        $unusedGradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $unusedGradient->setAttribute('id', 'unused');

        $defs->appendChild($usedGradient);
        $defs->appendChild($unusedGradient);
        $svg->appendChild($defs);

        // Reference only the first gradient
        $path = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('rect');
            }
        };
        $path->setAttribute('fill', 'url(#used)');
        $svg->appendChild($path);

        $document = new Document($svg);

        $this->assertCount(2, $defs->getChildren());

        $pass->optimize($document);

        // Only used gradient should remain
        $this->assertCount(1, $defs->getChildren());
        $this->assertSame($usedGradient, $defs->getChildren()[0]);
    }

    public function testHandleMultipleUrlReferences(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $filter = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('filter');
            }
        };
        $filter->setAttribute('id', 'blur');

        $mask = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('mask');
            }
        };
        $mask->setAttribute('id', 'mask1');

        $defs->appendChild($filter);
        $defs->appendChild($mask);
        $svg->appendChild($defs);

        // Element with multiple url() references
        $group = new GroupElement();
        $group->setAttribute('filter', 'url(#blur)');
        $group->setAttribute('mask', 'url(#mask1)');
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        // Both should be kept
        $this->assertCount(2, $defs->getChildren());
    }

    public function testHandleNestedDefs(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        // Nested structure with defs
        $group = new GroupElement();
        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $pattern = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('pattern');
            }
        };
        $pattern->setAttribute('id', 'pattern1');

        $defs->appendChild($pattern);
        $group->appendChild($defs);
        $svg->appendChild($group);

        $document = new Document($svg);

        $this->assertCount(1, $defs->getChildren());

        $pass->optimize($document);

        // Unused pattern should be removed
        $this->assertCount(0, $defs->getChildren());
    }

    public function testHandleReferenceInStyleAttribute(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $gradient->setAttribute('id', 'grad1');

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        // Reference via style attribute
        $path = new PathElement();
        $path->setAttribute('style', 'fill: url(#grad1); stroke: black');
        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // Gradient should be kept (referenced in style)
        $this->assertCount(1, $defs->getChildren());
    }

    public function testHandleXlinkHref(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $symbol = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('symbol');
            }
        };
        $symbol->setAttribute('id', 'icon');

        $defs->appendChild($symbol);
        $svg->appendChild($defs);

        // Reference via xlink:href
        $use = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('use');
            }
        };
        $use->setAttribute('xlink:href', '#icon');
        $svg->appendChild($use);

        $document = new Document($svg);

        $pass->optimize($document);

        // Symbol should be kept (referenced via xlink:href)
        $this->assertCount(1, $defs->getChildren());
    }

    public function testDoNotRemoveDefsWithoutId(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        // Element without ID (cannot be referenced, but also cannot be removed safely)
        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $document = new Document($svg);

        $pass->optimize($document);

        // Element without ID should be kept (we can't determine if it's unused)
        $this->assertCount(1, $defs->getChildren());
    }

    public function testUnregisterRemovedElementIds(): void
    {
        $pass = new RemoveUnusedDefsPass();
        $svg = new SvgElement();

        $defs = new class extends AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('defs');
            }
        };

        $gradient = new class extends AbstractElement {
            public function __construct()
            {
                parent::__construct('linearGradient');
            }
        };
        $gradient->setAttribute('id', 'unused-grad');

        $defs->appendChild($gradient);
        $svg->appendChild($defs);

        $document = new Document($svg);

        // Verify ID is registered
        $this->assertNotNull($document->getElementById('unused-grad'));

        $pass->optimize($document);

        // Verify ID is unregistered after removal
        $this->assertNull($document->getElementById('unused-grad'));
    }
}
