---
order: 20
title: Quick Start
description: "The three entry points (create, load, fromString) and what you can do from there."
---

# Quick Start

Everything goes through the `Svg` facade. Three ways in:

```php
use Atelier\Svg\Svg;

Svg::create(800, 600)      // new document
Svg::load('icon.svg')      // from file
Svg::fromString($markup)   // from string
```

All three return the same `Svg` object with the same fluent API.

## Create

Build a document from scratch with `Svg::create()`. Methods chain:

```php
$svg = Svg::create(400, 300)
    ->rect(0, 0, 400, 300, ['fill' => '#0f172a'])
    ->circle(200, 150, 80, ['fill' => '#6366f1'])
    ->text(200, 240, 'Hello', ['text-anchor' => 'middle', 'fill' => '#fff', 'font-size' => '18'])
    ->save('output.svg');
```

Available shape methods: `rect`, `circle`, `ellipse`, `line`, `path`, `polygon`, `polyline`, `text`, `image`. Each takes coordinates then an optional attributes array.

## Load and query

Load an existing file, then query elements with CSS selectors:

```php
$svg = Svg::load('icons/arrow.svg');

// Single element
$svg->querySelector('path')?->setAttribute('fill', 'currentColor');

// All matching elements: returns ElementCollection
$svg->querySelectorAll('[fill="#000000"]')
    ->fill('currentColor');

$svg->save('icons/arrow.svg');
```

`querySelectorAll()` returns an `ElementCollection`. It supports `fill()`, `stroke()`, `strokeWidth()`, `opacity()`, `setAttribute()`, `addClass()`, `each()`, and more.

## Optimize

Every `Svg` instance has optimize methods built in:

```php
// Default preset: balanced
Svg::load('input.svg')->optimize()->save('output.svg');

// For web delivery: strips metadata, merges paths
Svg::load('input.svg')->optimizeWeb()->save('output.svg');

// Maximum compression
Svg::load('input.svg')->optimizeAggressive()->save('output.svg');
```

See [Optimization](/optimization/) for custom pipelines and all presets.

## Sanitize user uploads

Before storing or rendering SVGs from untrusted sources:

```php
$clean = Svg::fromString($userUpload)
    ->sanitize()   // removes scripts, event handlers, javascript: URLs
    ->toString();
```

See [Document: Sanitization](/document/) for strict and permissive profiles.

## Output

```php
$svg->toString();        // compact string
$svg->toPrettyString();  // indented string
$svg->save('out.svg');   // compact file
$svg->savePretty('out.svg'); // indented file
$svg->toDataUri();       // data:image/svg+xml;base64,...

echo $svg;               // same as toString()
```

## Common patterns

### Embed an image

Embed a raster image inside an SVG canvas. See [Elements: Shapes](/elements/shapes/).

```php
<?php
Svg::create(400, 300)
    ->image('photo.jpg', 0, 0, 400, 300)
    ->save('output.svg');
```

### Organize with defs and groups

`defs()` opens a `<defs>` block for reusable assets. `group()` returns the underlying `Builder` for nesting. See [Elements: Structure](/elements/structure/).

```php
<?php
$builder = Svg::create(200, 200)->defs()->group();
$builder->circle(100, 100, 50)->attr('id', 'dot')->end();
```

### Linear and radial gradients

Define gradients inside `<defs>`, then reference them by ID. See [Elements: Gradients](/elements/gradients/).

```php
<?php
Svg::create(200, 200)
    ->defs()
    ->linearGradient('grad', 0, 0, 1, 0)
    ->radialGradient('glow', 100, 100, 80)
    ->save('output.svg');
```

## Error handling

`Svg::load()` and `Svg::fromString()` throw two exception types:

- `Atelier\Svg\Exception\RuntimeException`: file not found, unreadable, or write failure
- `Atelier\Svg\Exception\ParseException`: invalid XML or missing root `<svg>` element

Both extend `\RuntimeException` and implement `Atelier\Svg\Exception\SvgExceptionInterface`.

```php
<?php
use Atelier\Svg\Svg;
use Atelier\Svg\Exception\ParseException;
use Atelier\Svg\Exception\RuntimeException;

try {
    $svg = Svg::load('upload.svg');
} catch (RuntimeException $e) {
    // File missing or unreadable
    echo 'File error: ' . $e->getMessage();
} catch (ParseException $e) {
    // Not valid SVG XML
    echo 'Parse error: ' . $e->getMessage();
}
```

For stricter parse control: such as failing on malformed but technically loadable files: use `DomParser` directly with `ParseProfile::STRICT`. See [Document: Parsing](/document/parsing/).
