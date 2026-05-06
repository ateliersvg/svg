---
order: 80
description: "Convert SVG shape elements - rect, circle, ellipse, line, polygon, polyline - to path elements for animation, morphing, and further optimization."
---
# Convert Shapes to Paths

SVG has dedicated elements for common shapes: `<rect>`, `<circle>`, `<ellipse>`, `<line>`, `<polygon>`, and `<polyline>`. Converting them to `<path>` unlocks capabilities that shape elements do not support directly: morphing, path merging, and certain path-data optimizations.

## Why convert

- **Morphing** - `ShapeMorpher` works on `Data` objects from path elements. A `<circle>` cannot be morphed directly; its path equivalent can.
- **Path merging** - `MergePathsPass` combines adjacent `<path>` elements with the same presentation attributes. Shapes are not eligible until converted.
- **Uniform representation** - tools that traverse `d` attributes work on everything without shape-specific branches.

Converting is not always smaller. A `<circle cx="50" cy="50" r="40"/>` is 28 characters; its path equivalent is longer. By default the pass converts regardless. Pass `allowExpansion: false` to skip conversions where the path data would be longer than the original attributes.

## Via the optimizer

`ConvertShapeToPathPass` handles all six shape types. Use it directly, or use any preset that includes it (`web`, `aggressive`).

```php
<?php

use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;

$loader   = new DomLoader();
$document = $loader->loadFromFile('shapes.svg');

$optimizer = new Optimizer([
    new ConvertShapeToPathPass(),
]);
$optimizer->optimize($document);

(new CompactXmlDumper())->dumpToFile($document, 'paths.svg');
```

### Convert only specific shape types

Each shape type has its own constructor flag, all `true` by default:

```php
<?php

use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;

// Convert circles and ellipses only; leave rects, lines, polygons unchanged
$pass = new ConvertShapeToPathPass(
    convertRects:     false,
    convertCircles:   true,
    convertEllipses:  true,
    convertLines:     false,
    convertPolygons:  false,
    convertPolylines: false,
);
```

### Skip expansions

```php
<?php

use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;

// Only convert when the resulting path data is no longer than the
// shape-specific attributes it replaces
$pass = new ConvertShapeToPathPass(allowExpansion: false);
```

## Via a preset

`OptimizerPresets::web()` and `OptimizerPresets::aggressive()` include `ConvertShapeToPathPass` with default settings. Use them when you want shape conversion as part of a broader optimization pipeline:

```php
<?php

use Atelier\Svg\Svg;

Svg::load('input.svg')
    ->optimizeWeb()
    ->save('output.svg');
```

## Before and after

Input:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <rect x="10" y="10" width="80" height="40" fill="#3b82f6"/>
  <circle cx="50" cy="75" r="15" fill="#ef4444"/>
</svg>
```

After `ConvertShapeToPathPass`:

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
  <path d="M10 10 L90 10 L90 50 L10 50 Z" fill="#3b82f6"/>
  <path d="M65 75 A15 15 0 1 0 35 75 A15 15 0 1 0 65 75 Z" fill="#ef4444"/>
</svg>
```

All presentation attributes (`fill`, `stroke`, `opacity`, `class`, etc.) are copied to the new `<path>`. Shape-specific attributes (`x`, `y`, `width`, `height`, `r`, `cx`, `cy`, `rx`, `ry`, `x1`, `y1`, `x2`, `y2`, `points`) are dropped.

## Morphing after conversion

Once shapes are paths, they can be morphed with `ShapeMorpher`:

```php
<?php

use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;
use Atelier\Svg\Morphing\ShapeMorpher;
use Atelier\Svg\Path\Data;

$loader    = new DomLoader();
$optimizer = new Optimizer([new ConvertShapeToPathPass()]);

$circleDoc = $loader->loadFromFile('circle.svg');
$starDoc   = $loader->loadFromFile('star.svg');

$optimizer->optimize($circleDoc);
$optimizer->optimize($starDoc);

$circlePath = $circleDoc->querySelector('path')?->getAttribute('d');
$starPath   = $starDoc->querySelector('path')?->getAttribute('d');

if ($circlePath !== null && $starPath !== null) {
    $morpher = new ShapeMorpher();
    $frames  = $morpher->generateFrames(
        Data::parse($circlePath),
        Data::parse($starPath),
        60,
        'ease-in-out',
    );
}
```

## See also

- [Animate shapes](animate-shapes.md): export morphing animations to SVG
- [Batch optimize](batch-optimize.md): run conversion over a directory of files
- [Morphing: how it works](../morphing/how-it-works.md): normalization and interpolation internals
