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
    simplifier: new Simplifier(), // simplification algorithm
    tolerance: 1.0,               // higher = more aggressive (default: 1.0)
);
```

Typical tolerance values:
- `0.1`: safe preset (near-lossless)
- `0.5`: default preset
- `1.0`: aggressive preset

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

## See also

- [Optimization overview](../overview.md)
- [Cleanup passes](cleanup.md)
- [Conversion passes](convert.md)
- [Removal passes](remove.md)
- [Writing a custom pass](../custom-pass.md)
