# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- SVG creation, loading, and exporting with fluent facade API
- Full SVG 1.1 element support: shapes, text, groups, symbols, markers, defs, use
- CSS-like selectors and element collections with batch operations
- Path builder with all SVG path commands (lines, curves, arcs)
- Path analysis: length, bounding box, point containment, distance metrics
- 26+ filter primitives with fluent FilterBuilder
- Linear and radial gradients with GradientBuilder
- Patterns, clipping paths, and masks
- SMIL animation support with AnimationBuilder
- 40+ optimization passes with 4 presets (default, aggressive, safe, accessible)
- `ConvertPathDataPass` uses parsed path infrastructure with per-segment abs/rel comparison and L-to-H/V shorthand
- `ConvertPathDataPass` curve optimizations: C-to-Q (cubic-to-quadratic), C-to-S (smooth cubic), Q-to-T (smooth quadratic), compact arc flags
- `MergeStylesPass` now minifies CSS even with a single `<style>` element and removes obsolete `type="text/css"` attributes
- `PathUtils::toAbsolute()` and `toRelative()` handle all 10 SVG path segment types
- SVG sanitization with 3 security profiles (strict, default, permissive)
- Document validation with configurable profiles
- Accessibility checking and auto-improvement
- Shape morphing with easing functions and animation export (SMIL, CSS, JS)
- Document merging with multiple strategies (append, side-by-side, stacked, grid, symbols)
- Transform parsing and manipulation
