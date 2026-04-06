---
order: 30
---
# Exporting

Atelier SVG provides multiple output formats: compact XML, pretty-printed XML, file output, and data URIs. All serialization goes through dumper classes that implement `DumperInterface`.

## Quick Export via the Facade

The `Svg` facade offers the most direct output methods:

```php
use Atelier\Svg\Svg;

$svg = Svg::load('input.svg');

// Compact string output (single line, minimal whitespace)
$output = $svg->toString();

// Pretty-printed string output (indented, readable)
$output = $svg->toPrettyString();

// Save to file (compact format)
$svg->save('output.svg');

// Save to file (pretty-printed)
$svg->savePretty('output.svg');

// Implicit string conversion (compact)
echo $svg;
```

All output methods return `$this` for chaining (except `toString()` and `toPrettyString()`):

```php
Svg::load('input.svg')
    ->optimize()
    ->save('optimized.svg')
    ->savePretty('optimized-pretty.svg');
```

## Data URIs

Convert SVGs to data URIs for embedding in CSS or HTML:

```php
$svg = Svg::load('icon.svg');

// Base64-encoded (default)
$uri = $svg->toDataUri();
// "data:image/svg+xml;base64,PHN2Zy..."

// URL-encoded (smaller for simple SVGs)
$uri = $svg->toDataUri(base64: false);
// "data:image/svg+xml,%3Csvg..."
```

Data URIs automatically strip the XML declaration for a more compact result.

## Dumper Classes

Under the hood, all serialization uses dumper classes. You can use them directly for full control.

### CompactXmlDumper

Produces minimal output with no extra whitespace. Best for production use.

```php
use Atelier\Svg\Dumper\CompactXmlDumper;

$dumper = new CompactXmlDumper();
$xml = $dumper->dump($document);
```

### PrettyXmlDumper

Produces indented, human-readable output. Best for debugging and version control.

```php
use Atelier\Svg\Dumper\PrettyXmlDumper;

$dumper = new PrettyXmlDumper();
$xml = $dumper->dump($document);
```

### Common Dumper Options

Both dumpers extend `XmlDumper` and share the same configuration:

```php
// Exclude the XML declaration (<?xml version="1.0"?>)
$dumper = (new CompactXmlDumper())->includeXmlDeclaration(false);
$xml = $dumper->dump($document);

// Write directly to a file
$dumper->dumpToFile($document, 'output.svg');
```

### Using a Custom Dumper with the Facade

Pass any `DumperInterface` implementation to `save()`:

```php
use Atelier\Svg\Dumper\PrettyXmlDumper;

$svg->save('output.svg', new PrettyXmlDumper());
```

## DumperInterface

To implement a custom dumper (for example, JSON output or a template format), implement `DumperInterface`:

```php
use Atelier\Svg\Document;
use Atelier\Svg\Dumper\DumperInterface;

class MyCustomDumper implements DumperInterface
{
    public function dump(Document $document): string
    {
        // Your serialization logic
    }
}
```

## See also

- [Document Overview](overview.md): Core concepts
- [Creating SVGs](creating.md): Building SVGs to export
- [Parsing SVGs](parsing.md): Loading SVGs before re-exporting
