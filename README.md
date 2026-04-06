# Atelier SVG

[![CI](https://github.com/smnandre/atelier-svg/actions/workflows/ci.yml/badge.svg)](https://github.com/smnandre/atelier-svg/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg)](https://php.net)

Designed precisely. Built to scale.

A PHP library for SVG manipulation, optimization, and morphing. Parse, build, style, transform, validate, sanitize, and animate SVG graphics with a type-safe, fluent API.

**[Quick Start](#quick-start) | [Features](#features) | [Use Cases](#use-cases) | [Documentation](#documentation)**

---

## Installation

```bash
composer require atelier/svg
```

Requires PHP 8.3+.

---

## Quick Start

```php
use Atelier\Svg\Svg;

Svg::create(300, 200)
    ->rect(0, 0, 300, 200, ['fill' => '#1e293b'])
    ->circle(150, 100, 60, ['fill' => '#3b82f6'])
    ->text(150, 180, 'Atelier SVG', ['text-anchor' => 'middle', 'fill' => '#fff'])
    ->optimize()
    ->save('output.svg');
```

Load, query, modify, save:

```php
$svg = Svg::load('input.svg');

$svg->getDocument()
    ->querySelectorAll('circle')
    ->fill('#3b82f6')
    ->stroke('#000')
    ->strokeWidth(2);

$svg->optimize()->save('output.svg');
```

---

## Features

### Elements

Full SVG 1.1 element support: shapes, text, groups, symbols, markers, gradients, filters, clipping, masking, and animation: all as typed PHP classes.

```php
$text = TextElement::create(10, 30, 'Hello');

$builder = new TspanBuilder($text);
$builder->add('Bold', 0, ['font-weight' => 'bold'])
    ->add('and italic', 10, ['font-style' => 'italic']);
```

```php
$symbol = SymbolBuilder::createSymbol($document, 'icon-star', '0 0 24 24');
SymbolBuilder::useSymbol($document, 'icon-star', 10, 10);

$marker = MarkerBuilder::arrow($document, 'arrow-end', '#000', 10);
```

```php
AnimationBuilder::fadeIn($element, '1s');
AnimationBuilder::rotate($element, 0, 360, '2s');
```

[Elements documentation](./docs/elements/overview.md): Shapes, text, structural elements, selectors, accessibility

### Filters & Effects

26+ filter primitives, linear and radial gradients, patterns, clipping, and masking: with fluent builders.

```php
FilterBuilder::createDropShadow($document, 'shadow', 2, 2, 4, '#000', 0.3);

FilterBuilder::create($document, 'glow')
    ->gaussianBlur(3, 'SourceAlpha', 'blur')
    ->flood('#3b82f6', 0.8, 'color')
    ->composite('in', 'color', 'blur', 'glow')
    ->blend('normal', 'SourceGraphic', 'glow')
    ->addToDefs();
```

```php
GradientBuilder::horizontal($document, 'sunset', '#ff6b6b', '#feca57');

GradientBuilder::createLinear($document, 'custom')
    ->from(0, 0)->to(100, 100)
    ->addStop(0, '#3b82f6')
    ->addStop(50, '#8b5cf6', 0.8)
    ->addStop(100, '#ec4899')
    ->addToDefs();
```

[Filters documentation](./docs/elements/filters/overview.md): [Gradients](./docs/elements/gradients/linear.md): [Clipping & Masking](./docs/elements/clipping.md)

### Paths

Type-safe path building, geometric analysis, distance metrics, and simplification.

```php
$data = PathBuilder::startAt(10, 10)
    ->lineTo(50, 50)
    ->curveTo(250, 50, 300, 50, 350, 100)
    ->arcTo(50, 50, 0, false, true, 500, 100)
    ->closePath()
    ->toData();

$analyzer = new PathAnalyzer($data);
$length = $analyzer->getLength();
$bbox = $analyzer->getBoundingBox();
$inside = $analyzer->containsPoint(new Point(25, 25));
```

[Path documentation](./docs/path/overview.md): Building, analysis, transforms, simplification

### Optimization

A configurable pipeline with 40+ passes, inspired by SVGO. Four presets, or build your own.

```php
Svg::load('input.svg')->optimize()->save('output.svg');
```

**Before:**

```xml
<svg width="100.00000" height="100.00000" viewBox="0.00 0.00 100.00 100.00">
  <rect x="10.00000" y="20.00000" width="80.00000" height="60.00000"
        fill="black" stroke="none" opacity="1.0" />
</svg>
```

**After:**

```xml
<svg width="100" height="100" viewBox="0 0 100 100">
  <rect x="10" y="20" width="80" height="60" fill="#000"/>
</svg>
```

```php
$optimizer = new Optimizer(OptimizerPresets::default());      // Balanced
$optimizer = new Optimizer(OptimizerPresets::aggressive());   // Maximum reduction
$optimizer = new Optimizer(OptimizerPresets::safe());         // Conservative
$optimizer = new Optimizer(OptimizerPresets::accessible());   // Keeps a11y metadata
```

[Optimization documentation](./docs/optimization/overview.md): Passes, presets, custom pipelines

### Security & Validation

Sanitize untrusted SVGs, validate against the spec, check accessibility.

```php
Sanitizer::strict()->sanitize($document);   // Remove scripts, event handlers, JS URLs
Sanitizer::default()->sanitize($document);  // Balanced security
```

```php
$validator = new Validator(ValidationProfile::strict());
$result = $validator->validate($document);

$broken = DocumentValidator::findBrokenReferences($document);
DocumentValidator::autoFix($document);
```

```php
$issues = Accessibility::checkAccessibility($document);
Accessibility::setTitle($document, 'Sales Chart Q1 2025');
Accessibility::improveAccessibility($document);
```

[Validation documentation](./docs/document/validation.md): [Sanitization](./docs/document/sanitization.md): [Accessibility](./docs/elements/accessibility.md)

### Morphing

Interpolate between SVG shapes with easing. Export to SMIL, CSS keyframes, or JavaScript.

```php
$midPath = Morph::between($startPath, $endPath, 0.5);

$frames = Morph::create()
    ->from($startPath)
    ->to($endPath)
    ->withDuration(2000, 60)
    ->withEasing('ease-in-out')
    ->generate();

$doc = AnimationExporter::toAnimatedSVG($frames, ['duration' => 3]);
$css = AnimationExporter::toCSSKeyframes($frames, 'my-morph');
```

[Morphing documentation](./docs/morphing/overview.md): Interpolation, easing, exporting

---

## Use Cases

### Sanitize user-uploaded SVGs

Accept SVGs from users without risking XSS. Strip scripts and dangerous content, validate structure, optimize, and serve.

```php
$svg = Svg::load($uploadedFile);
$document = $svg->getDocument();

Sanitizer::strict()->sanitize($document);
DocumentValidator::autoFix($document);

$svg->optimize()->save($outputPath);
```

### Generate icon sprite sheets

Consolidate individual icon files into a single SVG sprite for fewer HTTP requests.

```php
$icons = array_map(fn ($file) => Svg::load($file)->getDocument(), glob('icons/*.svg'));

$sprite = Document::merge($icons, ['strategy' => MergeStrategy::SYMBOLS]);
```

```html
<svg><use href="sprite.svg#icon-home"/></svg>
```

### Batch-process SVG assets

Optimize an entire directory of SVGs in a CI pipeline or build step.

```php
$optimizer = new Optimizer(OptimizerPresets::aggressive());

foreach (glob('assets/svg/*.svg') as $file) {
    $document = (new DomLoader())->loadFromFile($file);
    $optimizer->optimize($document);
    (new CompactXmlDumper())->dumpToFile($document, $file);
}
```

### Build charts and dashboards

Compose SVG documents programmatically: generate charts, combine them into layouts, add accessible metadata.

```php
$chart = Svg::create(400, 300);

foreach ($data as $i => $value) {
    $height = $value * 2;
    $chart->rect($i * 50 + 10, 300 - $height, 40, $height, ['fill' => '#3b82f6']);
}

Accessibility::setTitle($chart->getDocument(), 'Monthly Revenue');
Accessibility::setDescription($chart->getDocument(), 'Bar chart showing revenue by month');

$chart->save('chart.svg');
```

### Animate shape transitions

Morph between two SVG shapes and export as a self-contained animated SVG.

```php
$star = Svg::load('star.svg')->getDocument()->querySelector('path');
$circle = Svg::load('circle.svg')->getDocument()->querySelector('path');

$frames = Morph::frames(
    Path::parse($star->getAttribute('d'))->getData(),
    Path::parse($circle->getAttribute('d'))->getData(),
    60,
    'ease-in-out',
);

$animated = AnimationExporter::toAnimatedSVG($frames, [
    'duration' => 2,
    'repeatCount' => 'indefinite',
]);
```

---

## Documentation

- [Getting Started](./docs/getting-started/installation.md): Installation and quick start
- [Document Handling](./docs/document/overview.md): Creating, loading, exporting, sanitization, validation
- [Elements](./docs/elements/overview.md): Shapes, text, animation, selectors, gradients, filters, clipping
- [Path Operations](./docs/path/overview.md): Building, analysis, transforms, geometry, simplification
- [Styling](./docs/styling/overview.md): Layout, transforms, and values
- [Optimization](./docs/optimization/overview.md): Passes and presets
- [Morphing](./docs/morphing/overview.md): Interpolation, easing, exporting
- [Guides](./docs/guides/sanitize-uploads.md): Sanitization, batch processing, charts, animation

---

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](.github/CONTRIBUTING.md) for guidelines.

## License

Atelier SVG is open-source software licensed under the [MIT License](LICENSE).
