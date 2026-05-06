---
order: 35
description: Build accessible, optimized SVG icons - viewBox setup, currentColor, accessibility annotations, and production export.
---

# Creating Icons

Icons have stricter requirements than general SVGs: they must scale to any size, inherit their surrounding color, stay lean for inlining, and be usable by screen readers. This guide shows the full pipeline from an empty document to a production-ready file.

## The baseline setup

Two things every icon needs: a `viewBox` so it scales freely, and no hard-coded `width`/`height` so CSS controls its size.

```php
<?php

use Atelier\Svg\Svg;

$doc = Svg::create(24, 24)
    ->path('M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z', ['fill' => 'currentColor'])
    ->getDocument();

$root = $doc->getRootElement();
$root->setViewbox('0 0 24 24');
$root->removeAttribute('width');
$root->removeAttribute('height');
```

`24×24` is the most common grid. Design on whole-number coordinates - fractional values cause subpixel blurring at small sizes.

`fill="currentColor"` makes the icon inherit whatever text color surrounds it in HTML. No hex values, no CSS overrides needed.

## Accessibility

Whether an icon needs an accessible name depends on its context. Get this wrong and screen readers either announce nothing useful, or announce the same thing twice.

### Decorative icon - next to a visible label

The icon is redundant. Hide it entirely.

```php
<?php

$root->setAttribute('aria-hidden', 'true');
$root->setAttribute('focusable', 'false'); // IE11 sends focus to SVGs by default
```

### Standalone icon - no visible label nearby

The icon carries meaning on its own. Give it a title and announce it as an image.

```php
<?php

use Atelier\Svg\Element\Accessibility\Accessibility;

Accessibility::setTitle($doc, 'Home');
Accessibility::setAriaRole($root, 'img');
$root->setAttribute('aria-labelledby', 'icon-title');
$doc->querySelector('title')?->setId('icon-title');
```

The `<title>` becomes the accessible name. `aria-labelledby` points to it explicitly - more reliable than relying on implicit title association across browsers.

## Optimize

Running an icon through a full aggressive preset can strip the title and break accessibility. Use a targeted pipeline instead:

```php
<?php

use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\CleanupAttributesPass;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDefaultAttributesPass;
use Atelier\Svg\Optimizer\Pass\RemoveDimensionsPass;
use Atelier\Svg\Optimizer\Pass\RemoveUnusedDefsPass;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;

$optimizer = new Optimizer([
    new RemoveDefaultAttributesPass(),
    new RemoveUnusedDefsPass(),
    new CleanupAttributesPass(),
    new ConvertShapeToPathPass(convertRects: true, convertCircles: true, convertEllipses: true),
    new MergePathsPass(),            // requires shapes to be paths first
    new RoundValuesPass(precision: 2),
    new RemoveDimensionsPass(),      // strips width/height, keeps viewBox
]);

$optimizer->optimize($doc);
```

`RemoveTitlePass` and `RemoveDescPass` are not in this list intentionally - they would delete the accessibility annotations added in the previous step.

For icons where accessibility is handled in HTML (e.g. a button with its own label), `OptimizerPresets::web()` is a good shortcut.

## Export

```php
<?php

// Inline in a template
echo $doc->toString();

// Write to disk
Svg::fromDocument($doc)->save('dist/home.svg');

// Data URI - URL-encoded is smaller and works in CSS url()
$uri = Svg::fromDocument($doc)->toDataUri(base64: false);

// Data URI - base64 is larger but safe in every context including HTML attributes
$b64 = Svg::fromDocument($doc)->toDataUri(base64: true);
```

## Quick reference

| | Why |
|---|---|
| `viewBox` without `width`/`height` | Scales freely via CSS font-size or width |
| `fill="currentColor"` | Inherits text color, survives dark mode |
| `aria-hidden` on decorative icons | Avoids announcing the icon name next to its label |
| `role="img"` + `<title>` on standalone icons | Gives screen readers an accessible name |
| `RemoveDimensionsPass` | Strips the dimensions the `Svg::create()` added |
| No `RemoveTitlePass` | Keeps accessibility annotations intact |
| Prefix IDs before bundling | Prevents collisions when used in a sprite sheet |

## See also

- [Sprite sheets](sprites-and-symbols.md) - bundle multiple icons into one file
- [Accessibility guide](accessibility.md) - aria patterns in depth
- [Optimization overview](../optimization/overview.md) - full pass reference
