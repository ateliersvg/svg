---
order: 10
---
# Creating

Atelier SVG offers several ways to create SVG documents from scratch: the `Svg` facade with its fluent builder, the `Document` class with direct element creation, the `Builder` class for complex nesting, and the `DocumentBuilder` for document-level construction.

## Svg::create()

The simplest approach. Returns an `Svg` instance with a fluent API for adding shapes:

```php
use Atelier\Svg\Svg;

$svg = Svg::create(800, 600)
    ->rect(10, 10, 200, 100, ['fill' => '#3b82f6'])
    ->circle(400, 300, 50, ['fill' => '#ef4444', 'stroke' => '#000'])
    ->path('M10 80 Q 95 10 180 80', ['stroke' => '#000', 'fill' => 'none']);

// Save to file
$svg->save('output.svg');
```

Default dimensions are 300x150 (matching the browser default for SVG elements). For clarity, always specify dimensions explicitly.

### Available Shape Methods

| Method | Parameters |
|--------|-----------|
| `rect()` | `$x, $y, $width, $height, $attributes` |
| `circle()` | `$cx, $cy, $r, $attributes` |
| `path()` | `$d, $attributes` |
| `group()` | Returns a `Builder` for nested content |

The `$attributes` array accepts any SVG attribute as key-value pairs.

## Document::create()

For more control, use `Document` directly. Shape methods return the created element, allowing further manipulation:

```php
use Atelier\Svg\Document;

$document = Document::create(400, 300);

$rect = $document->rect(10, 10, 100, 50, ['fill' => '#3b82f6']);
$rect->setAttribute('rx', '5');

$circle = $document->circle(200, 150, 40, ['fill' => '#22c55e']);
```

## The Builder Class

For complex SVGs with deep nesting, use `Builder` directly. It maintains a stack of container elements and supports a chainable interface:

```php
use Atelier\Svg\Element\Builder;

$builder = new Builder();
$document = $builder
    ->svg(800, 600)
        ->rect(10, 10, 100, 100)->fill('#ff0000')->end()
        ->circle(200, 200, 50)->fill('#00ff00')->stroke('#000')->strokeWidth(2)->end()
        ->g()
            ->rect(300, 100, 50, 50)->fill('#0000ff')->end()
            ->rect(360, 100, 50, 50)->fill('#ffff00')->end()
        ->end()
    ->getDocument();
```

Key points:

- Call `->end()` to close each element and return to the parent container.
- `->g()` opens a `<g>` group element.
- `->getDocument()` returns the final `Document` instance.

## DocumentBuilder

`DocumentBuilder` provides factory methods for creating documents from various sources:

```php
use Atelier\Svg\Document\DocumentBuilder;

$builder = new DocumentBuilder();

// Empty document with dimensions
$document = $builder->createDocument(800, 600);

// From an SVG string
$document = $builder->fromString('<svg width="100" height="100">...</svg>');

// From a file
$document = $builder->fromFile('path/to/icon.svg');

// Validate before parsing
if ($builder->validate($svgString)) {
    $document = $builder->fromString($svgString);
}
```

## Accessing the Document from the Facade

When using `Svg::create()`, you can always drop down to the underlying `Document` for advanced operations:

```php
$svg = Svg::create(400, 300);
$document = $svg->getDocument();

// Use Document methods
$document->setTitle('My Chart');
$document->setDescription('A bar chart showing quarterly results');
```

## See also

- [Document Overview](overview.md): Core concepts
- [Exporting SVGs](exporting.md): Saving your created SVGs
- [Parsing SVGs](parsing.md): Loading existing SVGs instead of creating new ones
