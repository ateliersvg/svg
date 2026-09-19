# Changelog

Public API and behaviour changes only. Versions follow Semantic Versioning.

## Unreleased

### Added

- `SimplifyPathPass` relative tolerance, a ceiling expressed as a fraction of the drawing span
- `SimplifyPathPass::resolveTolerance()`, the tolerance a given document resolves to

### Changed

- Optimizer presets bound their simplification tolerance by the drawing span

### Fixed

- Minified arc flags in SVG path data
- Adjacent SVG path numbers without separators
- Presets flattening shapes drawn on a small grid
- Paths written with relative commands mangled by the simplifier
- Elliptical arcs wrong under rotation, skew, non-uniform scale or reflection
- Path data drifting along a subpath when the optimizer rounded relative coordinates
- Optimizer presets inflating a document by copying a group's inherited attributes onto every child

## 1.0.0 - 2026-05-20

### Added

- Fluent facade for creating, loading and exporting SVG
- SVG 1.1 elements: shapes, text, groups, symbols, markers, defs, use
- CSS-like selectors and element collections with batch operations
- Path builder covering every SVG path command
- Path analysis: length, bounding box, point containment, distance metrics
- Filter primitives through `FilterBuilder`
- Linear and radial gradients through `GradientBuilder`
- Patterns, clipping paths and masks
- SMIL animation through `AnimationBuilder`
- Optimization passes with four presets: default, aggressive, safe, accessible
- `ConvertPathDataPass`, per-segment absolute and relative comparison with L-to-H/V shorthand
- `ConvertPathDataPass` curve optimizations: C-to-Q, C-to-S, Q-to-T, compact arc flags
- `MergeStylesPass` minifies CSS with a single `<style>` element and drops `type="text/css"`
- `PathUtils::toAbsolute()` and `toRelative()` across all ten path segment types
- Sanitization with three profiles: strict, default, permissive
- Document validation with configurable profiles
- Accessibility checking and auto-improvement
- Shape morphing with easing, exported to SMIL, CSS or JS
- Document merging: append, side-by-side, stacked, grid, symbols
- Transform parsing and manipulation
