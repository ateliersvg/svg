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
| `ConvertPathDataPass` | Optimizes path `d` strings: whitespace, redundant commands, number formatting |
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

Optimizes SVG path `d` attribute strings. Normalizes whitespace, removes redundant commands, and formats numbers compactly.

```php
new ConvertPathDataPass(
    removeRedundantCommands: true, // remove consecutive same commands (default: true)
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
