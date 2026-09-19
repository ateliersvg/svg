---
order: 40
---
# Merge

These passes restructure the SVG document tree: merging elements, collapsing groups, extracting styles, sorting attributes, simplifying paths and transforms, and adjusting coordinates.

## Summary

| Pass | Description |
|---|---|
| `MergePathsPass` | Merges consecutive `<path>` elements with identical styling |
| `MergeStylesPass` | Merges multiple `<style>` elements into one, optionally minifies CSS |
| `CollapseGroupsPass` | Collapses single-child groups and removes empty groups |
| `InlineStylesPass` | Inlines CSS class styles as element attributes |
| `MoveAttributesToGroupPass` | Moves common attributes from children to parent group |
| `MoveGroupAttrsToElemsPass` | Moves a group's transform down onto its children |
| `SortAttributesPass` | Sorts attributes alphabetically for better compression |
| `SimplifyPathPass` | Reduces path points using a simplification algorithm |
| `SimplifyTransformsPass` | Removes identity transforms, simplifies transform values |
| `RoundValuesPass` | Rounds numeric values with per-context precision |
| `ScaleCoordinatesPass` | Scales all coordinates by a factor |
| `PrefixIdsPass` | Adds a prefix to all IDs and their references |
| `AddClassesToSVGPass` | Extracts common inline styles into CSS classes |

All classes live in the `Atelier\Svg\Optimizer\Pass` namespace.

## MergePathsPass

Merges consecutive `<path>` siblings that have identical styling attributes. Paths are only merged when they have the same fill, stroke, transform, and no ID attribute.

```php
new MergePathsPass(
    ignoreClass: false, // merge even with different class attributes (default: false)
);
```

Run after `ConvertShapeToPathPass` to maximize merge opportunities.

## MergeStylesPass

Finds all `<style>` elements, merges their CSS content into a single `<style>` element, deduplicates rules, and optionally minifies the result.

```php
new MergeStylesPass(
    minify: false, // minify merged CSS (default: false)
);
```

## CollapseGroupsPass

Removes empty `<g>` elements and collapses groups that contain only one child. When collapsing, group attributes are merged into the child element.

```php
new CollapseGroupsPass();
```

No constructor options.

## MoveGroupAttrsToElemsPass

Moves a group's `transform` onto each of its children, so `CollapseGroupsPass` can then remove
the group. Only `transform` moves: an inherited attribute such as `fill` already reaches every
child at no cost, and copying it onto each of them would trade one attribute for as many as
there are children.

```php
new MoveGroupAttrsToElemsPass();
```

No constructor options. The group is left alone unless every condition holds: it carries a
`transform`, none of its attributes references a URL, and no child has an `id` or is an element
that ignores `transform`. A group transform is prepended to a child's own, since the group
applies first.

**Not in any preset.** Pushing a transform down duplicates it once per child to save the eight
characters of a `<g>`, which measured as a net loss across the package figures and the benchmark
fixtures. Add it explicitly when flattening the tree matters more than size, for instance to let
a later pass merge paths that only a group was keeping apart.

## InlineStylesPass

Reads styles from `<style>` elements and applies class-based CSS rules directly as element attributes. The opposite of `AddClassesToSVGPass`. Useful when the SVG must work without CSS support (e.g. in email clients).

```php
new InlineStylesPass(
    removeStyleElements: true,    // remove <style> after inlining (default: true)
    removeClassAttributes: true,  // remove class attributes after inlining (default: true)
);
```

## MoveAttributesToGroupPass

Detects attributes shared by all children of a group and moves them to the parent `<g>` element. Only inheritable presentation attributes (fill, stroke, opacity, font properties, etc.) are moved.

```php
new MoveAttributesToGroupPass(
    minChildrenCount: 2, // minimum children required to move attributes (default: 2)
);
```

## SortAttributesPass

Sorts element attributes alphabetically. Priority attributes (`id`, `class`) are kept first. Improves gzip/brotli compression and produces consistent, diffable output.

```php
new SortAttributesPass(
    priorityOrder: ['id', 'class'], // attributes to place first (default: ['id', 'class'])
);
```

## SimplifyPathPass

Reduces the number of points in path data using a simplification algorithm (e.g. Ramer-Douglas-Peucker). Higher tolerance produces more aggressive simplification. Only line segments are simplified; curve commands are preserved.

```php
use Atelier\Svg\Path\Simplifier\Simplifier;

new SimplifyPathPass(
    simplifier: new Simplifier(),  // simplification algorithm
    tolerance: 1.0,                // user units, higher = more aggressive (default: 1.0)
    relativeTolerance: 0.002,      // ceiling as a fraction of the drawing span (default: none)
);
```

Typical tolerance values:
- `0.1`: safe preset (near-lossless)
- `0.5`: default preset
- `1.0`: web preset
- `2.0`: aggressive preset

### Tolerance and document scale

The tolerance is a distance in user units, so what it removes depends on the scale the document is drawn at. A tolerance of `1.0` is a hairline in a 1000-unit banner and a whole module in a 33-unit barcode, where it takes corners off: a seven-by-one rectangle comes back a triangle, a one-by-one rectangle comes back a zero-width line.

`relativeTolerance` bounds the tolerance by the drawing's own span. The pass applies the smaller of `tolerance` and `relativeTolerance * span`, where the span is the diagonal of the root `viewBox`, or of `width` and `height` when there is no `viewBox`. A document that exposes neither keeps `tolerance`. Leaving `relativeTolerance` unset keeps `tolerance` for every document.

The presets set it. Each value reproduces that preset's absolute tolerance at a span of 500 user units, so documents at that scale or larger are simplified exactly as before:

| Preset | `tolerance` | `relativeTolerance` |
|---|---|---|
| `safe` | 0.1 | `SimplifyPathPass::RELATIVE_TOLERANCE_SAFE` (0.0002) |
| `default` | 0.5 | `SimplifyPathPass::RELATIVE_TOLERANCE_DEFAULT` (0.001) |
| `web` | 1.0 | `SimplifyPathPass::RELATIVE_TOLERANCE_WEB` (0.002) |
| `aggressive` | 2.0 | `SimplifyPathPass::RELATIVE_TOLERANCE_AGGRESSIVE` (0.004) |

`resolveTolerance()` reports what a document resolves to, without optimizing it:

```php
$pass = new SimplifyPathPass(new Simplifier(), 1.0, 0.002);

$pass->resolveTolerance($document); // 0.093 for viewBox="0 0 33 33"
```

`Optimizer::simplifyPaths($document, $tolerance)` takes an absolute tolerance and applies it as given, since the caller states the distance.

## SimplifyTransformsPass

Removes identity transforms (`translate(0,0)`, `scale(1,1)`, `rotate(0)`) and simplifies numeric values in transform strings.

```php
new SimplifyTransformsPass(
    precision: 3,        // decimal precision for transform values (default: 3)
    removeDefaults: true, // remove identity transforms (default: true)
);
```

## RoundValuesPass

Rounds numeric attribute values to a specified precision. Supports per-context precision: coordinates, transforms, and path data can each use a different number of decimal places.

```php
use Atelier\Svg\Optimizer\PrecisionConfig;

new RoundValuesPass(
    precision: PrecisionConfig::COORDINATE_DEFAULT,           // coordinate precision (default: 2)
    transformPrecision: PrecisionConfig::TRANSFORM_DEFAULT,   // transform precision (default: 3)
    pathPrecision: PrecisionConfig::PATH_DEFAULT,             // path data precision (default: 3)
);
```

Precision can be adjusted after construction:

```php
$pass = new RoundValuesPass(2);
$pass->setPrecision(3);
```

## ScaleCoordinatesPass

Scales all coordinate and dimension attributes by a given factor. Also scales `viewBox`, path data, and `points`. Useful for scaling coordinates up before rounding to integers, preserving relative precision.

This pass is not included in any preset. Use it in custom pipelines when you need to rescale SVG coordinates.

```php
new ScaleCoordinatesPass(
    scaleFactor: 10.0, // multiplication factor (default: 10.0, must be > 0)
);
```

## PrefixIdsPass

Adds a prefix to all `id` attributes and updates every reference (`url(#...)`, `href="#..."`, etc.). Prevents ID conflicts when multiple SVGs are combined on the same page.

```php
new PrefixIdsPass(
    prefix: null,       // prefix string (null = auto-generate from document hash)
    delimiter: '__',    // separator between prefix and ID (default: '__')
);
```

## AddClassesToSVGPass

Identifies elements with common style attributes, extracts those styles into CSS classes in a `<style>` element, and replaces the inline attributes with class references.

```php
new AddClassesToSVGPass(
    minOccurrences: 2,              // minimum elements with same styles to create a class (default: 2)
    classPrefix: 'cls-',            // prefix for generated class names (default: 'cls-')
    preserveExistingClasses: true,  // keep existing class attributes (default: true)
);
```

Styleable attributes include: `fill`, `stroke`, `stroke-width`, `opacity`, `font-family`, `font-size`, `font-weight`, and more.
