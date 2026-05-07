---
order: 20
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

- `Atelier\Svg\Exception\RuntimeException` - file not found, unreadable, or exceeds the configured size limit (loader).
- `Atelier\Svg\Exception\ParseException` - invalid XML, missing root `<svg>` element, or any XML error when using `ParseProfile::STRICT` (parser).

Both implement `Atelier\Svg\Exception\SvgExceptionInterface` and extend `\RuntimeException`.

### Via the facade

```php
<?php
use Atelier\Svg\Svg;
use Atelier\Svg\Exception\ParseException;
use Atelier\Svg\Exception\RuntimeException;

try {
    $svg = Svg::load('icon.svg');
} catch (RuntimeException $e) {
    // File missing, unreadable, or too large
    echo 'Load error: ' . $e->getMessage();
} catch (ParseException $e) {
    // Malformed XML or not an SVG document
    echo 'Parse error: ' . $e->getMessage();
}
```

### Via DomParser directly

`ParseProfile::STRICT` throws on the first XML warning; `ParseProfile::LENIENT` records warnings and continues.

```php
<?php
use Atelier\Svg\Parser\DomParser;
use Atelier\Svg\Parser\ParseProfile;
use Atelier\Svg\Exception\ParseException;

$parser = new DomParser(ParseProfile::STRICT);

try {
    $document = $parser->parse($svgString);
} catch (ParseException $e) {
    echo 'Parse error: ' . $e->getMessage();
}
```

### File not found vs. parse failure

`RuntimeException` is thrown before parsing begins - the file could not be read. `ParseException` is thrown after the file is read but the content is not valid SVG XML. Catching both lets you distinguish input problems from infrastructure problems.

## See also

- [Document Overview](overview.md): What is a Document
- [Creating SVGs](creating.md): Building SVGs from scratch
- [Validation](validation.md): Validating parsed documents
