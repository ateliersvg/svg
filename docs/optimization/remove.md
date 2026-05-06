---
order: 20
---
# Removal

Removal passes strip elements, attributes, or definitions that are unused, redundant, or invisible. They reduce file size without changing the rendered output.

## Summary

| Pass | Description |
|---|---|
| `RemoveDefaultAttributesPass` | Removes attributes that match SVG specification defaults |
| `RemoveUnknownsAndDefaultsPass` | Removes attributes with default SVG values |
| `RemoveHiddenElementsPass` | Removes elements with `display:none`, `visibility:hidden`, or `opacity:0` |
| `RemoveUselessStrokeAndFillPass` | Removes stroke/fill attributes that have no effect |
| `RemoveUnusedDefsPass` | Removes definitions inside `<defs>` that are never referenced |
| `RemoveUnusedClassesPass` | Removes CSS rules for classes not used by any element |
| `RemoveElementsByTagNamePass` | Removes elements matching given tag names |
| `RemoveDuplicateDefsPass` | Deduplicates identical gradient/pattern definitions |
| `RemoveNonInheritableGroupAttrsPass` | Removes attributes on `<g>` that do not inherit to children |
| `RemoveDimensionsPass` | Removes `width`/`height` from root `<svg>` (when `viewBox` is present) |

All classes live in the `Atelier\Svg\Optimizer\Pass` namespace.

## RemoveDefaultAttributesPass

Removes attributes that have their SVG specification default values. Maintains a per-element-type map of defaults (e.g. `fill="black"`, `stroke="none"`, `opacity="1"`).

```php
new RemoveDefaultAttributesPass();
```

No constructor options. Covers defaults for `rect`, `circle`, `ellipse`, `line`, `path`, `text`, and common global attributes.

## RemoveUnknownsAndDefaultsPass

Removes attributes with known SVG default values. Complements `RemoveDefaultAttributesPass` with a broader attribute-level default map.

```php
new RemoveUnknownsAndDefaultsPass(
    removeDefaults: true, // remove attributes with default values (default: true)
);
```

## RemoveHiddenElementsPass

Removes elements that are not visible. Elements with an `id` attribute are preserved by default, since they may be shown dynamically via CSS or JavaScript.

```php
new RemoveHiddenElementsPass(
    removeDisplayNone: true,       // remove display="none" elements (default: true)
    removeVisibilityHidden: true,  // remove visibility="hidden" elements (default: true)
    removeOpacityZero: false,      // remove opacity="0" elements (default: false)
    preserveWithId: true,          // keep elements that have an id (default: true)
);
```

Setting `removeOpacityZero` to `true` is only safe when the SVG has no animations that toggle opacity.

## RemoveUselessStrokeAndFillPass

Removes stroke and fill attributes that have no visual effect:
- Removes stroke-related attributes when `stroke-width` is `0`
- Removes `fill` and `fill-opacity` on elements that do not support fill (`line`, `polyline`)

```php
new RemoveUselessStrokeAndFillPass();
```

No constructor options.

## RemoveUnusedDefsPass

Scans the document for ID references (`href`, `url(#...)`, etc.) and removes any element inside `<defs>` whose ID is never referenced.

```php
new RemoveUnusedDefsPass();
```

No constructor options. Handles references in `href`, `xlink:href`, `fill`, `stroke`, `clip-path`, `mask`, `filter`, `marker-*`, `style` attributes, and `<style>` element content.

## RemoveUnusedClassesPass

Finds all CSS classes defined in `<style>` elements, checks which are actually used in `class` attributes, and removes unused CSS rules.

```php
new RemoveUnusedClassesPass(
    removeEmptyStyles: true, // remove <style> elements that become empty (default: true)
);
```

## RemoveElementsByTagNamePass

A flexible pass that removes elements matching specified tag names. Provides factory methods for common cases.

```php
// Generic usage
new RemoveElementsByTagNamePass(['desc', 'title', 'metadata']);

// Factory methods
RemoveElementsByTagNamePass::removeDesc();
RemoveElementsByTagNamePass::removeTitle();
RemoveElementsByTagNamePass::removeMetadata();
RemoveElementsByTagNamePass::removeAllDescriptive(); // desc + title + metadata
```

Constructor parameters:

```php
new RemoveElementsByTagNamePass(
    tagNames: ['desc'],  // tag names to remove
    name: null,          // custom pass name (auto-generated if null)
);
```

## RemoveDuplicateDefsPass

Finds identical definitions inside `<defs>` (same tag, same attributes except `id`, same children) and removes duplicates. All references to the duplicate are updated to point to the kept definition.

```php
new RemoveDuplicateDefsPass();
```

No constructor options.

## RemoveNonInheritableGroupAttrsPass

Removes attributes on `<g>` elements that do not inherit to children (e.g. `x`, `y`, `width`, `height`, `d`, `points`, `viewBox`). These attributes have no effect on group elements.

```php
new RemoveNonInheritableGroupAttrsPass();
```

No constructor options.

## RemoveDimensionsPass

Removes `width` and `height` attributes from the root `<svg>` element, but only when a `viewBox` is present to preserve the aspect ratio. Makes the SVG responsive and scalable to its container.

```php
new RemoveDimensionsPass();
```

No constructor options. Used in the `web` and `aggressive` presets.

## See also

- [Optimization overview](../overview.md)
- [Cleanup passes](cleanup.md)
- [Conversion passes](convert.md)
- [Merge and restructure passes](merge.md)
