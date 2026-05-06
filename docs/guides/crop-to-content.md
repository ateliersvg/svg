---
order: 100
description: "Trim an SVG's viewBox and dimensions to fit its actual content, removing whitespace from third-party files or after deleting elements."
---

# Crop SVG to its Content

When you load an SVG produced by a design tool, exported from a chart library, or assembled from arbitrary parts, the `viewBox` often spans much more area than the visible content. Sometimes the `<svg>` is `1000×1000` but everything sits in the bottom-right corner. Sometimes it's the leftover from a deleted element. Cropping the canvas to the actual content reduces visual emptiness and makes the file behave predictably when scaled.

The recipe is two lines of math: union the bounding boxes of every direct child of the root, then write a new `viewBox` that frames it.

## The simplest case

Every SVG element exposes `bbox()`, which returns a calculator. Calling `->get()` gives a `BoundingBox` with `minX`, `minY`, `maxX`, `maxY`, and helpers like `getWidth()` and `union()`.

```php
<?php

use Atelier\Svg\Svg;

$svg = Svg::fromString($xmlString);
$root = $svg->getDocument()->getRootElement();

$bbox = null;
foreach ($root->getChildren() as $child) {
    $childBox = $child->bbox()->get();
    $bbox = null === $bbox ? $childBox : $bbox->union($childBox);
}
```

Asking the root element directly for its bbox is **not** what you want here - `<svg>` reports its declared `viewBox` or `width`/`height`, not the extent of its children. Walking the children is what gives you the content extent.

## Apply the new viewBox

Once you have the content bbox, set the root's `viewBox`, `width`, and `height`. A small padding around the content keeps the content from touching the edge:

```php
<?php

if (null === $bbox) {
    return; // empty SVG, nothing to crop
}

$pad = 8;
$x = $bbox->minX - $pad;
$y = $bbox->minY - $pad;
$w = $bbox->getWidth()  + $pad * 2;
$h = $bbox->getHeight() + $pad * 2;

$root->setViewbox(sprintf('%g %g %g %g', $x, $y, $w, $h));
$root->setWidth($w);
$root->setHeight($h);
```

`%g` in `sprintf` strips trailing zeros - `230.0 230.0 130.0 130.0` becomes `230 230 130 130`, smaller and cleaner.

You can keep the original `width` and `height` to preserve the rendered size, or set new ones to match the cropped area. Setting both `viewBox` and matching dimensions makes the file behave the same whether it's inlined, used as `<img>`, or referenced from CSS.

## End-to-end

Putting it together, with a sample input that has content tucked away in the bottom-right:

```php
<?php

use Atelier\Svg\Svg;

$source = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" width="1000" height="1000">
  <rect x="400" y="450" width="120" height="80" fill="#3b82f6"/>
  <circle cx="600" cy="490" r="35" fill="#10b981"/>
</svg>
SVG;

$svg  = Svg::fromString($source);
$root = $svg->getDocument()->getRootElement();

$bbox = null;
foreach ($root->getChildren() as $child) {
    $cb = $child->bbox()->get();
    $bbox = null === $bbox ? $cb : $bbox->union($cb);
}

$pad = 8;
$root->setViewbox(sprintf(
    '%g %g %g %g',
    $bbox->minX - $pad,
    $bbox->minY - $pad,
    $bbox->getWidth()  + $pad * 2,
    $bbox->getHeight() + $pad * 2,
));
$root->setWidth($bbox->getWidth()   + $pad * 2);
$root->setHeight($bbox->getHeight() + $pad * 2);

echo $svg->toPrettyString();
```

The output crops `1000×1000` down to `251×96` with a tight `viewBox` of `392 442 251 96`.

## What `bbox()` covers

`bbox()->get()` returns the bounding box _with the element's own `transform` applied_, but not its parents'. For most cropping cases that's exactly right - children of the root SVG don't have an outer transform to inherit. If your top-level structure wraps content in a `<g transform="...">`, the wrapper's bbox already accounts for the transform, and the union still works.

For elements buried deeper that need their full ancestry baked in, use `bbox()->getScreen()` instead, which walks the parent chain.

## Edge cases worth handling

- **Empty SVG.** `getChildren()` may include `<defs>` or comments only. If `$bbox` stays `null`, leave the document alone.
- **Off-canvas content.** Negative coordinates are valid - `minX = -120` just means content extends to the left of the original origin. The padded `viewBox` handles this naturally.
- **Stroke width.** `bbox()` returns the geometric extent and ignores `stroke-width`. If you stroke a 1×1 rect with width 4, half the stroke (2 units) renders outside the bbox. Bump `$pad` if your strokes are heavy.
- **Filter regions.** Drop shadows and blurs render outside the geometric bbox. Pad accordingly when keeping filtered elements.

## Quick reference

| | Why |
|---|---|
| `$el->bbox()->get()` | Bounding box of an element in its own coordinate space |
| `$el->bbox()->getScreen()` | Same, but with all parent transforms applied |
| `$bbox->union($other)` | Merge two boxes into the smallest one that contains both |
| Walk root children, don't ask root | Root reports its declared `viewBox`, not its content extent |
| `%g` in `sprintf` | Drops trailing zeros so `230 230 130 130` stays clean |
| Pad with `stroke-width / 2` | Ensures stroked content isn't clipped at the new edge |

## See also

- [Path overview](../path/overview.md) - measuring and computing path geometry
- [Document overview](../document/overview.md) - root element, attributes, children
- [Optimization overview](../optimization/overview.md) - combine cropping with size-reduction passes
