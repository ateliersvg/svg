---
order: 10
---
# Cleanup

Cleanup passes normalize, trim, and sanitize SVG attributes and elements without altering the visual result. They are safe to run in any order and are typically placed early in the pipeline.

## Summary

| Pass | Description |
|---|---|
| `CleanupAttributesPass` | Trims whitespace, normalizes class lists and numeric values in attributes |
| `CleanupIdsPass` | Removes unused IDs, optionally minifies them, supports prefix and preserve patterns |
| `CleanupNumericValuesPass` | Rounds numbers, removes trailing/leading zeros, formats compact values |
| `CleanupEnableBackgroundPass` | Removes the legacy `enable-background` attribute |
| `RemoveCommentsPass` | Removes XML comments (stripped at loader level) |
| `RemoveMetadataPass` | Removes `<metadata>`, optionally `<desc>` and `<title>` |
| `RemoveDescPass` | Removes `<desc>` elements |
| `RemoveTitlePass` | Removes `<title>` elements |
| `RemoveDoctypePass` | Removes DOCTYPE declarations |
| `RemoveXMLProcInstPass` | Removes `<?xml ...?>` processing instructions |
| `RemoveEditorsNSDataPass` | Removes editor-specific namespaces and metadata (Illustrator, Inkscape, Sketch, etc.) |
| `RemoveEmptyAttrsPass` | Removes attributes with empty or whitespace-only values |
| `RemoveEmptyElementsPass` | Removes container elements with no children and no preserving attributes |
| `RemoveEmptyGroupsPass` | Removes empty `<g>` elements, optionally unwraps attribute-less groups |
| `RemoveRedundantSvgAttributesPass` | Removes `version` and `xml:space="preserve"` from the root SVG |
| `RemoveUnusedNSPass` | Removes `xmlns:*` declarations that are not referenced |

All classes live in the `Atelier\Svg\Optimizer\Pass` namespace.

## CleanupAttributesPass

Normalizes attribute values: trims whitespace, deduplicates spaces in class lists, normalizes whitespace in `points` and path `d` attributes.

```php
new CleanupAttributesPass();
```

No constructor options.

## CleanupIdsPass

Removes IDs not referenced anywhere in the document. Optionally minifies remaining IDs to short names (`a`, `b`, ..., `aa`, `ab`, ...) and adds a prefix.

```php
new CleanupIdsPass(
    remove: true,       // remove unused IDs (default: true)
    minify: false,      // minify IDs to short names (default: false)
    prefix: '',         // prefix to prepend to all IDs (default: '')
    preserve: [],       // ID patterns to preserve (default: [])
);
```

## CleanupNumericValuesPass

Rounds numeric attribute values, removes trailing zeros (`10.00` becomes `10`), and optionally removes leading zeros (`0.5` becomes `.5`).

```php
new CleanupNumericValuesPass(
    precision: 3,            // decimal places (default: 3)
    removeLeadingZero: true, // 0.5 -> .5 (default: true)
);
```

## CleanupEnableBackgroundPass

Removes the `enable-background` attribute, a legacy from Adobe Illustrator exports.

```php
new CleanupEnableBackgroundPass();
```

## RemoveCommentsPass

Removes XML comments. Currently a no-op because comments are stripped at the loader level.

```php
new RemoveCommentsPass();
```

## RemoveMetadataPass

Removes `<metadata>` elements. Optionally removes `<desc>` and `<title>`.

```php
new RemoveMetadataPass(
    removeDesc: true,   // remove <desc> (default: true)
    removeTitle: false,  // remove <title> (default: false)
);
```

## RemoveDescPass

Removes all `<desc>` elements. May impact accessibility.

```php
new RemoveDescPass();
```

## RemoveTitlePass

Removes all `<title>` elements. May impact accessibility and tooltips.

```php
new RemoveTitlePass();
```

## RemoveDoctypePass

Removes DOCTYPE declarations. Modern SVG does not require them.

```php
new RemoveDoctypePass();
```

## RemoveXMLProcInstPass

Removes the `<?xml ...?>` processing instruction. Not needed when SVGs are embedded in HTML.

```php
new RemoveXMLProcInstPass();
```

## RemoveEditorsNSDataPass

Removes editor-specific namespace declarations and attributes. Handles Illustrator, Inkscape, Sketch, CorelDRAW, Microsoft Visio, and others. Can reduce file size by 20-50% on editor exports.

```php
new RemoveEditorsNSDataPass();
```

## RemoveEmptyAttrsPass

Removes attributes with empty or whitespace-only values. Preserves certain attributes where an empty value is meaningful (e.g. `alt=""` for accessibility).

```php
new RemoveEmptyAttrsPass(
    preserveAttrs: null, // attributes to keep even when empty (default: ['alt', 'role', 'aria-label'])
);
```

## RemoveEmptyElementsPass

Removes container elements (`g`, `text`, `tspan`, `defs`, `style`, `script`) that have no children and no preserving attributes (id, class, event handlers).

```php
new RemoveEmptyElementsPass(
    checkableElements: null,    // element types to check (default: ['g','text','tspan','defs','style','script'])
    preservingAttributes: null, // attributes that prevent removal (default: id, class, event handlers)
);
```

## RemoveEmptyGroupsPass

Removes empty `<g>` elements. When `unwrapAttributeLessGroups` is enabled, groups with children but no meaningful attributes are unwrapped (children promoted to parent). Propagatable presentation attributes (fill, stroke, opacity, etc.) are merged into children.

```php
new RemoveEmptyGroupsPass(
    preservingAttributes: null,       // attributes that prevent removal (default: id, class, event handlers)
    unwrapAttributeLessGroups: true,   // unwrap groups with children but no attributes (default: true)
);
```

## RemoveRedundantSvgAttributesPass

Removes `version` and `xml:space="preserve"` from the root `<svg>` element.

```php
new RemoveRedundantSvgAttributesPass(
    removeVersionAttribute: true,  // remove version (default: true)
    removeXmlSpacePreserve: true,  // remove xml:space="preserve" (default: true)
);
```

## RemoveUnusedNSPass

Scans the document for used namespace prefixes and removes `xmlns:*` declarations that are never referenced. Essential namespaces (`svg`, `xlink`) are kept by default.

```php
new RemoveUnusedNSPass(
    keepEssential: true, // always keep svg, xlink namespaces (default: true)
);
```

## See also

- [Optimization overview](../overview.md)
- [Conversion passes](convert.md)
- [Removal passes](remove.md)
- [Merge and restructure passes](merge.md)
