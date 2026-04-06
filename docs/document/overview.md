# Document

Atelier SVG is a PHP 8.3+ library for parsing, creating, manipulating, and exporting SVG documents. This section covers the core document model and the main entry points.

## Core Concepts

The library has two main entry points:

- **`Document`**: The internal representation of an SVG document. It holds the root `SvgElement`, provides CSS-like query selectors, element import/merge capabilities, validation, and optimization methods.
- **`Svg`**: A facade that wraps a `Document` and provides a simplified, fluent API for common workflows. Most users should start here.

## The Svg Facade

`Svg` is the recommended entry point. It exposes static factory methods for loading and creating SVGs, and instance methods for manipulation and output.

```php
use Atelier\Svg\Svg;

// Load from file
$svg = Svg::load('icon.svg');

// Load from string
$svg = Svg::fromString('<svg width="100" height="100"><circle cx="50" cy="50" r="40"/></svg>');

// Create from scratch
$svg = Svg::create(800, 600);

// Wrap an existing Document
$svg = Svg::fromDocument($document);
```

The facade supports fluent chaining for common workflows:

```php
Svg::load('input.svg')
    ->optimize()
    ->sanitize()
    ->save('output.svg');
```

## The Document Class

For advanced use cases, work with `Document` directly. You can always access it from the facade:

```php
$document = $svg->getDocument();
```

`Document` provides:

- **Query selectors**: `querySelector()`, `querySelectorAll()`, `findByTag()`, `findByClass()`
- **Element management**: `getElementById()`, `importElement()`, `groupElements()`, `ungroup()`
- **Merging**: `Document::merge()` combines multiple documents (append, side-by-side, stacked, symbols, grid)
- **Validation**: `validate()`, `isValid()`, `findBrokenReferences()`
- **Optimization**: `optimize()`, `cleanupDefs()`, `roundValues()`
- **Accessibility**: `setTitle()`, `setDescription()`, `checkAccessibility()`

```php
use Atelier\Svg\Document;

$document = Document::create(400, 300);
$rect = $document->rect(10, 10, 100, 50, ['fill' => '#3b82f6']);

$circles = $document->querySelectorAll('circle');
```

## What You Can Do

| Task | Entry Point |
|------|-------------|
| Load an SVG file | `Svg::load()` or `DomLoader` |
| Create an SVG from scratch | `Svg::create()` or `Document::create()` |
| Query elements | `querySelector()`, `querySelectorAll()` |
| Optimize file size | `$svg->optimize()` |
| Sanitize for security | `$svg->sanitize()` |
| Export to string or file | `$svg->save()`, `$svg->toString()` |
| Export as data URI | `$svg->toDataUri()` |
| Validate structure | `$document->validate()` |
| Merge multiple SVGs | `Document::merge()` |
| Morph between shapes | `Svg::morph()`, `Svg::morphFrames()` |

## See also

- [Parsing SVGs](parsing.md): Loading SVG documents from files and strings
- [Creating SVGs](creating.md): Building SVGs programmatically
- [Exporting SVGs](exporting.md): Saving and serializing SVG output
- [Validation](validation.md): Validating SVG documents
- [Sanitization](sanitization.md): Securing SVGs against XSS
