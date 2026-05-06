---
order: 25
description: Use groups to scope transforms, share attributes, and structure complex SVG documents.
---

# Working with Groups

The most common reason to reach for a group is a transform that should apply to several elements at once - move, rotate, or scale them together without touching each one individually. Groups also let you set shared opacity, attach an id or class for later querying, and layer content into named sections.

## Build a group from scratch

`Svg::group()` opens a group and returns a `Builder`. Add elements to it, then call `end()` to close the group.

```php
<?php

use Atelier\Svg\Svg;

$svg = Svg::create(400, 200)
    ->group()
        ->transform('translate(100, 50)')
        ->rect(0, 0, 80, 80)->fill('#4f46e5')
        ->circle(120, 40, 30)->fill('#e11d48')
    ->end()
    ->toString();
```

Both shapes shift together. Change the `translate` once and the whole group moves.

## Layer content with multiple groups

After `end()` closes a group, the builder is back at the root level. Open another group with `g()`.

```php
<?php

use Atelier\Svg\Svg;

$svg = Svg::create(400, 300)
    ->group()
        ->id('background')
        ->rect(0, 0, 400, 300)->fill('#f8f8f8')
        ->rect(0, 0, 400, 40)->fill('#e0e0e0')
    ->end()
    ->g()
        ->id('content')->transform('translate(20, 60)')
        ->text(0, 0, 'Hello')->fill('#111')
    ->end()
    ->toString();
```

`id()` on a group is useful for querying it later with `querySelector('#background')`.

## Nest groups for compound transforms

Inner groups inherit the outer group's coordinate system.

```php
<?php

use Atelier\Svg\Svg;

// An icon rotated 45° and shifted to the centre
$svg = Svg::create(200, 200)
    ->group()
        ->transform('translate(100, 100)')   // move origin to centre
        ->g()
            ->transform('rotate(45)')        // rotate around new origin
            ->rect(-30, -30, 60, 60)->fill('#4f46e5')
        ->end()
    ->end()
    ->toString();
```

## Group existing elements in a parsed document

When you load an SVG and want to wrap a set of elements in a group after the fact, use `Document::groupElements()`.

```php
<?php

use Atelier\Svg\Svg;

$doc = Svg::load('chart.svg')->getDocument();

// Wrap all data points in a group so they can be toggled together
$points = $doc->querySelectorAll('.data-point');
$doc->groupElements(
    $points->toArray(),
    id: 'data-layer',
    attributes: ['opacity' => '1'],
);

// Now you can fade the whole layer with one attribute change
$doc->querySelector('#data-layer')?->setAttribute('opacity', '0.3');
```

## Ungroup and flatten

`ungroup()` removes a group and hoists its children into the parent - the group's transform is lost.

```php
<?php

use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Svg;

$doc = Svg::load('exported.svg')->getDocument();

// Design tools often leave unnecessary wrapper groups - remove them
$wrapper = $doc->querySelector('#Layer_1');
if ($wrapper instanceof GroupElement) {
    $doc->ungroup($wrapper);
}
```

To strip all groups at once - useful before merging paths or running the optimizer:

```php
<?php

$doc->flattenGroups();            // all levels
$doc->flattenGroups(maxDepth: 2); // stop after 2 levels
```

## Shared opacity and compositing

One practical difference between group opacity and per-element opacity: a group composites before blending. If two shapes overlap inside a group, they do not show through each other - the whole group is rendered first, then made transparent.

```php
<?php

use Atelier\Svg\Svg;

// Watermark overlay - the rect and text blend together, not separately
$svg = Svg::create(400, 300)
    ->group()
        ->attr('opacity', '0.15')
        ->rect(0, 0, 400, 300)->fill('#000')
        ->text(200, 160, 'DRAFT')
            ->attr('text-anchor', 'middle')
            ->attr('font-size', '72')
            ->fill('#fff')
    ->end()
    ->toString();
```

Set `opacity` per element if you want them to blend independently.

## See also

- [Structural elements](../elements/structure.md) - symbols, defs, use
- [Transforms](../styling/transforms.md) - coordinate systems in depth
- [Merge pass](../optimization/merge.md) - automatic group collapsing during optimization
