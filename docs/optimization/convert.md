---
order: 30
---
# Conversion

Conversion passes transform SVG elements and attributes from one representation to another, typically to enable further optimization or to produce shorter output.

## Summary

| Pass | Description |
|---|---|
| `ConvertColorsPass` | Converts colors to shortest representation (#fff, named, short hex) |
| `ConvertShapeToPathPass` | Converts shapes (rect, circle, ellipse, line, polygon, polyline) to `<path>` |
| `ConvertTransformPass` | Applies simple transforms (translate, scale) directly to coordinates |
| `ConvertPathDataPass` | Optimizes path `d` strings: abs/rel comparison, L-to-H/V shorthand, number formatting |
| `ConvertStyleToAttrsPass` | Converts inline `style=""` properties to presentation attributes |
| `ConvertEllipseToCirclePass` | Converts ellipses with equal radii (rx = ry) to circles |

All classes live in the `Atelier\Svg\Optimizer\Pass` namespace.

## ConvertColorsPass

Converts color values to their shortest form. Handles hex shortening (`#ffffff` to `#fff`), named colors (`red` instead of `#f00` when shorter), and `rgb()` to hex conversion.

Affected attributes: `fill`, `stroke`, `stop-color`, `flood-color`, `lighting-color`, `color`.

```php
new ConvertColorsPass(
    convertToShortHex: true, // #ffffff -> #fff (default: true)
    convertToNames: true,    // #f00 -> red when shorter (default: true)
    convertRgb: true,        // rgb(255,0,0) -> #f00 (default: true)
);
```

## ConvertShapeToPathPass

Converts simple SVG shape elements to `<path>` elements. This enables path-specific optimizations (merging, simplification) and often produces shorter markup.

```php
new ConvertShapeToPathPass(
    convertRects: true,      // <rect> to <path> (default: true)
    convertCircles: true,    // <circle> to <path> (default: true)
    convertEllipses: true,   // <ellipse> to <path> (default: true)
    convertLines: true,      // <line> to <path> (default: true)
    convertPolygons: true,   // <polygon> to <path> (default: true)
    convertPolylines: true,  // <polyline> to <path> (default: true)
    allowExpansion: true,    // allow conversion even when path data is longer (default: true)
);
```

Typically used in the aggressive preset. Run before `MergePathsPass` and `SimplifyPathPass`.

## ConvertTransformPass

Applies simple transform operations (`translate`, `scale`) directly into element coordinates, then removes the transform attribute. Does not handle `rotate` or `skew` by default.

```php
new ConvertTransformPass(
    convertTranslate: true,  // apply translate to coordinates (default: true)
    convertScale: true,      // apply scale to coordinates (default: true)
    convertRotate: false,    // apply rotate to coordinates (default: false)
    convertOnPaths: true,    // convert transforms on <path> elements (default: true)
    convertOnShapes: true,   // convert transforms on shape elements (default: true)
);
```

Should run before path optimization passes to ensure coordinates are finalized.

## ConvertPathDataPass

Optimizes SVG path `d` attribute strings using the parsed path infrastructure. Instead of regex-based string manipulation, the pass parses each path into typed segments, computes both absolute and relative representations for each segment, and picks the shorter one.

Key optimizations:
- **Absolute/relative comparison**: each segment is serialized both ways; the shorter representation wins.
- **Line-to-shorthand**: `L dx 0` becomes `H`/`h`, `L 0 dy` becomes `V`/`v`.
- **Cubic-to-quadratic (C-to-Q)**: when a cubic bezier's control points satisfy the quadratic relationship, the pass emits `Q` (4 args) instead of `C` (6 args), saving ~40% per curve.
- **Smooth cubic (C-to-S)**: when a cubic's first control point is the reflection of the previous curve's CP2, emits `S` (4 args) instead of `C` (6 args).
- **Smooth quadratic (Q-to-T)**: same smooth-continuation logic for quadratic curves; emits `T` (2 args) instead of `Q` (4 args).
- **Compact arc flags**: arc sweep/large-arc flags are single digits (0/1) that need no separator between them.
- **Redundant command elision**: consecutive same commands share a single command letter.
- **Compact number formatting**: trailing zeros removed, leading zeros removed (`.5` instead of `0.5`), negative sign used as separator (`10-20` instead of `10 -20`).
- **Precision-aware rounding**: numbers rounded to the configured decimal places.

```php
new ConvertPathDataPass(
    removeRedundantCommands: true, // merge consecutive same commands (default: true)
    precision: 3,                  // decimal places for path values (default: 3)
);
```

Best placed late in the pipeline, after transforms have been applied and shapes have been converted to paths.

## ConvertStyleToAttrsPass

Converts CSS properties in `style=""` attributes to individual SVG presentation attributes when this produces shorter output.

For example, `style="fill: red; stroke: blue"` becomes `fill="red" stroke="blue"`.

```php
new ConvertStyleToAttrsPass(
    onlyMatchShorthand: true, // only convert when result is shorter (default: true)
);
```

When `onlyMatchShorthand` is `false`, conversion happens even if the result is the same length.

## ConvertEllipseToCirclePass

Converts `<ellipse>` elements where `rx` equals `ry` to `<circle>` elements. A circle requires fewer attributes (`r` instead of `rx`/`ry`), producing smaller output.

```php
new ConvertEllipseToCirclePass(
    tolerance: 0.001, // tolerance for comparing rx and ry (default: 0.001)
);
```

Run before `ConvertShapeToPathPass` if both are used.

## See also

- [Optimization overview](../overview.md)
- [Cleanup passes](cleanup.md)
- [Removal passes](remove.md)
- [Merge and restructure passes](merge.md)
