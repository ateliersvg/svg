---
order: 20
---
# Quick Start

## Create an SVG from scratch

```php
use Atelier\Svg\Svg;

$svg = Svg::create(300, 200)
    ->rect(0, 0, 300, 200, ['fill' => '#1e293b'])
    ->circle(150, 100, 60, ['fill' => '#3b82f6'])
    ->text(150, 180, 'Atelier SVG', ['text-anchor' => 'middle', 'fill' => '#fff'])
    ->optimize()
    ->save('output.svg');
```

`Svg::create()` returns a fluent builder. Each shape method appends an
element to the document and returns the builder for chaining.

## Load and modify

```php
use Atelier\Svg\Svg;

$svg = Svg::load('input.svg');

$svg->getDocument()
    ->querySelectorAll('circle')
    ->fill('#3b82f6')
    ->stroke('#000')
    ->strokeWidth(2);

$svg->optimize()->save('output.svg');
```

`querySelectorAll()` returns an `ElementCollection` that supports batch
operations. `fill()`, `stroke()`, and `strokeWidth()` apply to every
matched element.

## Work with individual elements

```php
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Structural\GroupElement;

$rect = RectElement::create(0, 0, 200, 100)
    ->setFill('#3b82f6')
    ->setStroke('#000')
    ->setStrokeWidth(2)
    ->setOpacity(0.8);

$circle = CircleElement::create(100, 50, 30)
    ->setFill('#fff');

$group = new GroupElement();
$group->appendChild($rect);
$group->appendChild($circle);
```

Shape elements have static `create()` factory methods. All setters
return `static` for chaining.

## Build and analyze paths

```php
use Atelier\Svg\Path\PathBuilder;
use Atelier\Svg\Path\PathAnalyzer;

$data = PathBuilder::startAt(10, 10)
    ->lineTo(100, 10)
    ->lineTo(100, 100)
    ->closePath()
    ->toData();

$analyzer = new PathAnalyzer($data);
$length = $analyzer->getLength();
$bbox = $analyzer->getBoundingBox();
```

## Optimize

```php
use Atelier\Svg\Svg;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

// One-liner
Svg::load('input.svg')->optimize()->save('output.svg');

// With a specific preset
$optimizer = new Optimizer(OptimizerPresets::aggressive());
$document = Svg::load('input.svg')->getDocument();
$optimizer->optimize($document);
```

Four presets are available: `default()`, `aggressive()`, `safe()`, and
`accessible()`.

## Next steps

- [Document handling](../document/overview.md): parsing, creating, exporting
- [Elements](../elements/overview.md): shapes, text, gradients, filters
- [Paths](../path/overview.md): building, analysis, transforms
- [Guides](../guides/sanitize-uploads.md): real-world recipes
