---
order: 10
---
# Parsing

Atelier SVG provides two approaches for loading SVG content: the `DomLoader` (high-level) and the `DomParser` (low-level with parse profiles).

## Loading with the Svg Facade

The simplest way to load SVGs:

```php
use Atelier\Svg\Svg;

// From a file
$svg = Svg::load('path/to/icon.svg');

// From a string
$svg = Svg::fromString('<svg width="100" height="100"><circle cx="50" cy="50" r="40"/></svg>');
```

Both methods use `DomLoader` internally and return an `Svg` instance.

## DomLoader

`DomLoader` implements `LoaderInterface` and converts SVG content into a `Document` using PHP's DOM extension.

```php
use Atelier\Svg\Loader\DomLoader;

$loader = new DomLoader();

// Load from a string
$document = $loader->loadFromString($svgContent);

// Load from a file (throws RuntimeException if unreadable)
$document = $loader->loadFromFile('path/to/image.svg');
```

You can pass a custom loader to the facade:

```php
$svg = Svg::load('icon.svg', $customLoader);
```

## DomParser

`DomParser` is the lower-level parser that converts raw SVG XML into the library's element tree. It supports parse profiles and security protections.

```php
use Atelier\Svg\Parser\DomParser;
use Atelier\Svg\Parser\ParseProfile;

$parser = new DomParser(ParseProfile::STRICT);
$document = $parser->parse($svgString);

// Or parse from a file directly
$document = $parser->parseFile('path/to/icon.svg');
```

### Security

The parser includes built-in protections:

- **XXE prevention**: External entity loading is disabled (`LIBXML_NONET`).
- **Entity expansion attacks**: The parser does not use `LIBXML_NOENT`, preventing "Billion Laughs" attacks.
- **Input size limit**: Defaults to 10 MB. Configurable via the constructor:

```php
$parser = new DomParser(
    profile: ParseProfile::LENIENT,
    maxInputSize: 5 * 1024 * 1024 // 5 MB
);
```

## Parse Profiles

The `ParseProfile` enum controls how the parser handles errors and edge cases.

| Profile | Behavior |
|---------|----------|
| `ParseProfile::STRICT` | Throws on any XML parse error. Best for development and spec validation. |
| `ParseProfile::LENIENT` | Records warnings but preserves input. Best for real-world SVGs. Default. |

```php
use Atelier\Svg\Parser\DomParser;
use Atelier\Svg\Parser\ParseProfile;

// Strict: will throw ParseException on any XML error
$parser = new DomParser(ParseProfile::STRICT);

// Lenient: tolerates minor issues (default)
$parser = new DomParser(ParseProfile::LENIENT);

// Change profile after construction
$parser->setProfile(ParseProfile::STRICT);
```

## Supported Elements

The parser recognizes all standard SVG elements: shapes (`rect`, `circle`, `ellipse`, `line`, `polyline`, `polygon`, `path`), structural elements (`g`, `defs`, `symbol`, `use`), text (`text`, `tspan`, `textPath`), gradients, filters, clipping/masking, and animation elements. Unknown elements are silently skipped in lenient mode.

## Error Handling

Both loader and parser throw specific exceptions:

- `Atelier\Svg\Exception\RuntimeException`: File not found or unreadable (loader).
- `Atelier\Svg\Exception\ParseException`: Invalid XML or missing root `<svg>` element (parser).

```php
use Atelier\Svg\Exception\ParseException;

try {
    $document = $parser->parse($invalidSvg);
} catch (ParseException $e) {
    echo 'Parse error: ' . $e->getMessage();
}
```

## See also

- [Document Overview](overview.md): What is a Document
- [Creating SVGs](creating.md): Building SVGs from scratch
- [Validation](validation.md): Validating parsed documents
