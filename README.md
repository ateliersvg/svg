<h1 align="center">Atelier SVG</h1>

<p align="center">Parse, build, query, optimize, sanitize and morph SVG, in pure PHP.</p>

<p align="center">
  <img alt="PHP Version" src="https://img.shields.io/badge/PHP-8.3%2B-5a8dee?labelColor=14141c">
  <img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/ateliersvg/svg/ci.yml?branch=main&label=Tests&labelColor=14141c&color=5a8dee">
  <img alt="PHPUnit" src="https://img.shields.io/badge/PHPUnit-12-5a8dee?labelColor=14141c">
  <img alt="PHPStan" src="https://img.shields.io/badge/PHPStan-max-5a8dee?labelColor=14141c">
  <img alt="Stable" src="https://img.shields.io/github/v/release/ateliersvg/svg?label=Stable&labelColor=14141c&color=5a8dee">
  <img alt="License" src="https://img.shields.io/github/license/ateliersvg/svg?label=License&labelColor=14141c&color=5a8dee">
</p>

An SVG document as typed PHP objects rather than a string you edit with regular expressions.
Load a file, query it with CSS selectors, change it, and write it back. Or build one from
nothing.

```php
echo Svg::create(120, 120)->circle(60, 60, 50)->fill('#5a8dee')->toString();
```

Every element is a class, every attribute is validated, and nothing depends on an extension or
an external binary. Backed by an extensive test suite and PHPStan at its highest level.

**[Parse and build](#parse-build-and-export) · [Query](#query-and-edit) ·
[Elements](#every-element-typed) · [Paths](#paths) · [Sanitize](#sanitize-untrusted-input) ·
[Optimize](#optimize) · [Accessibility](#accessibility) · [Morph](#morph-and-animate) ·
[Documentation](#documentation)**

## Installation

```bash
composer require atelier/svg
```

Requires PHP 8.3 or later. No extensions, no image library, no external binary.

## Quick start

```php
use Atelier\Svg\Svg;

$svg = Svg::load('logo.svg')
    ->sanitize()
    ->optimizeWeb();

$svg->save('logo.min.svg');
```

Loading from markup instead of a path is `Svg::fromString($markup)`, and `toString()` returns
the document without writing a file. See [Quick start](docs/quick-start.md).

## Parse, build and export

`Svg` is the fluent facade. It loads an existing document, creates an empty one, and hands back
markup, a pretty-printed string, a file, or a data URI.

```php
$svg = Svg::create(200, 100)
    ->rect(10, 10, 80, 80)
    ->circle(150, 50, 40)
    ->fill('#5a8dee');

$svg->toDataUri();   // data:image/svg+xml;... ready for a CSS background
```

Parsing has profiles: the default accepts what a browser accepts, the strict one refuses what is
merely loadable. See [Document](docs/document/overview.md).

## Query and edit

The whole tree is objects, and CSS selectors find your way through it.

```php
$document = Svg::load('chart.svg')->getDocument();

foreach ($document->querySelectorAll('g[id^="series-"] path') as $path) {
    $path->setAttribute('stroke-width', '2');
}
```

Collections are typed and chainable rather than plain arrays. See
[Selectors](docs/elements/selectors.md) and [Collections](docs/elements/collections.md).

## Every element, typed

Shapes, text, gradients, filters, clipping and masking, structure, animation: each SVG element
is a class with its own attributes rather than a generic node. See
[Elements](docs/elements/overview.md).

## Paths

The `d` attribute becomes a list of typed segments, which is what makes measuring and rewriting
a curve possible at all.

```php
use Atelier\Svg\Path\Path;

$path = Path::parse('M20,80 C 80,20 220,20 280,80');

$path->getLength();
$path->getPointAtLength(120);   // a Point, for placing a marker along the curve
$path->getBoundingBox();
```

Building, analysis, geometry, simplification, and baking transforms into coordinates all live in
[Paths](docs/path/overview.md).

## Styling and transforms

Inline styles, presentation attributes, transform matrices, and the bounding boxes that layout
depends on. See [Styling](docs/styling/overview.md).

## Sanitize untrusted input

Accepting an SVG upload means accepting arbitrary markup. One call strips what makes it
dangerous: `<script>`, `on*` handlers, `javascript:` URLs, `<foreignObject>`, and external
references.

```php
$safe = Svg::fromString($upload)->sanitize()->toString();
```

Profiles range from permissive to strict, and validation is separate for when you need to know
what is wrong rather than remove it. See [Sanitization](docs/document/sanitization.md).

## Optimize

Fifty passes, grouped into cleanup, conversion, removal, and restructuring. Four presets choose
for you, and `optimizeWith()` takes a pipeline you assembled yourself.

```php
Svg::load('icon.svg')->optimizeWeb()->save('icon.min.svg');
```

`optimizeSafe()` preserves ids and metadata, `optimizeAggressive()` goes for the smallest file.
Writing your own pass is a documented interface, not a fork. See
[Optimization](docs/optimization/overview.md).

## Accessibility

A generated SVG is invisible to a screen reader until it is told what it shows.

```php
use Atelier\Svg\Element\Accessibility\Accessibility;

Accessibility::setTitle($document, 'Quarterly revenue');
Accessibility::setDescription($document, 'Bar chart comparing Q1 to Q4');
```

Titles, descriptions, ARIA roles and labels, focus order, and an audit that reports what is
missing. See [Accessibility](docs/elements/accessibility.md).

## Morph and animate

Interpolate between two shapes, whatever their segment counts, and export the result as SMIL,
CSS keyframes, JavaScript, or a sprite sheet.

```php
use Atelier\Svg\Morphing\Morph;
use Atelier\Svg\Path\PathParser;

$parser = new PathParser();
$frames = Morph::frames(
    $parser->parse('M 0 0 L 100 0 L 100 100 L 0 100 Z'),
    $parser->parse('M 50 0 L 100 50 L 50 100 L 0 50 Z'),
    60,
    'ease-in-out',
);
```

See [Morphing](docs/morphing/overview.md).

## Documentation

- [Installation](docs/installation.md): requirements and setup.
- [Quick start](docs/quick-start.md): load, create, and manipulate a document.
- [Document](docs/document/overview.md): parse, create, validate, sanitize, export.
- [Elements](docs/elements/overview.md): every element as a typed object.
- [Paths](docs/path/overview.md): build, measure, simplify, transform.
- [Styling](docs/styling/overview.md): styles, transforms, layout boxes.
- [Optimization](docs/optimization/overview.md): the passes, the presets, writing your own.
- [Morphing](docs/morphing/overview.md): interpolation and animation export.
- [Guides](docs/guides/overview.md): sanitizing uploads, icon sprites, charts, batch processing.

The full documentation is published at [ateliersvg.com/svg](https://ateliersvg.com/svg/).

## Contributing

Contributions are welcome. Visit the [project on GitHub](https://github.com/ateliersvg/svg) to
[report a bug](https://github.com/ateliersvg/svg/issues/new),
[suggest a feature](https://github.com/ateliersvg/svg/issues/new), or
[open a pull request](https://github.com/ateliersvg/svg/pulls).

Before submitting code, run:

```bash
composer qa   # PHP-CS-Fixer, PHPStan at level max, and PHPUnit
```

Changes to public behaviour need a test and a documentation update.

## Support

Bug reports, security disclosures, and contribution guidelines are collected at
[ateliersvg.com/support](https://ateliersvg.com/support/).

Sharing the package or [starring it on GitHub](https://github.com/ateliersvg/svg) helps more
than you would think.

## License

Atelier SVG is released under the [MIT License](LICENSE).
