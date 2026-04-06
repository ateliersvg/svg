# Atelier SVG

A PHP library for SVG parsing, manipulation, optimization, and morphing.

## Getting Started

- [Installation](getting-started/installation.md): requirements and setup
- [Quick Start](getting-started/quick-start.md): create, load, and manipulate SVGs

## Documentation

### [Document](document/): Parse, create, export, validate, sanitize

Load SVG files from strings, files, or URLs. Create new documents programmatically. Export to XML. Validate structure and sanitize untrusted input.

### [Elements](elements/): Shapes, text, gradients, filters, animation, selectors

Work with SVG elements: rectangles, circles, paths, text, gradients, filters, clip paths, masks, and animations. Query elements with CSS selectors.

### [Paths](path/): Build, transform, analyze, simplify, geometry

Parse and build path data. Apply geometric transformations. Analyze path properties like length and bounding box. Simplify complex paths. Geometry primitives: points, matrices, bounding boxes.

### [Styling](styling/): Styles, values, transforms, layout

Read and write CSS properties, SVG presentation attributes, and transform matrices. Handle colors, lengths, and other SVG value types.

### [Optimization](optimization/): Optimize with 40+ passes

Reduce file size with a configurable pipeline of over 40 optimization passes. Remove metadata, simplify paths, collapse groups, and more.

### [Morphing](morphing/): Animate between shapes

Morph between two SVG paths with easing functions. Generate animation frames and export to SMIL, CSS keyframes, JavaScript, or sprite sheets.

## Guides

- [Sanitize user uploads](guides/sanitize-uploads.md): strip scripts, validate, optimize
- [Batch-process assets](guides/batch-optimize.md): optimize a directory in CI
- [Build charts](guides/build-charts.md): generate SVG charts with accessibility
- [Animate shapes](guides/animate-shapes.md): morph between paths, export animations
