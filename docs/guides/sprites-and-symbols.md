---
order: 40
description: "Combine multiple SVG icons into a single sprite using <symbol> and <use> to reduce HTTP requests and keep icons consistent across your app."
---
# Sprite Sheets

Combine multiple SVG icons into a single sprite document using
`<symbol>` and `<use>`. This reduces HTTP requests and keeps icons
consistent across your application.

## Create a sprite from individual SVG files

Load each icon, wrap it in a symbol, then place all symbols in one
document.

```php
use Atelier\Svg\Document;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Element\Builder\SymbolBuilder;

$sprite = Document::create();
$loader = new DomLoader();

$icons = [
    'home'   => 'icons/home.svg',
    'user'   => 'icons/user.svg',
    'search' => 'icons/search.svg',
    'menu'   => 'icons/menu.svg',
];

foreach ($icons as $id => $path) {
    $icon = $loader->loadFromFile($path);
    $root = $icon->getRootElement();

    // Create a symbol with the icon's original viewBox
    $viewBox = $root->getAttribute('viewBox') ?? '0 0 24 24';
    $symbol = SymbolBuilder::createSymbol($sprite, $id, $viewBox);

    // Move the icon's children into the symbol
    foreach ($root->getChildren() as $child) {
        $symbol->appendChild($child);
    }
}

$dumper = new CompactXmlDumper();
$dumper->dumpToFile($sprite, 'sprite.svg');
```

The resulting file contains only `<defs>` with `<symbol>` elements.
Nothing renders until referenced with `<use>`.

## Use symbols from a sprite

Reference symbols by their ID. In HTML, point to the sprite file:

```html
<svg width="24" height="24">
    <use href="sprite.svg#home"></use>
</svg>
```

In PHP, build a document that references symbols from the same sprite:

```php
$use = SymbolBuilder::useSymbol($sprite, 'home', 10, 10, 24, 24);
$use = SymbolBuilder::useSymbol($sprite, 'user', 50, 10, 24, 24);
```

`useSymbol` creates a `<use>` element, sets its `href`, position, and
dimensions, then appends it to the document root.

## Manage symbols with SymbolLibrary

For larger icon sets, `SymbolLibrary` provides an in-memory registry.

```php
use Atelier\Svg\Element\Structural\SymbolLibrary;
use Atelier\Svg\Element\Builder\SymbolBuilder;

$library = new SymbolLibrary();

// Add symbols by ID: non-symbol elements are auto-wrapped
$library->add('home', $homeSymbol);
$library->add('arrow', $arrowPath);  // path gets wrapped in <symbol>

// Query the library
$library->has('home');    // true
$library->getIds();       // ['home', 'arrow']
$library->get('home');    // SymbolElement

// Import the entire library into a document
SymbolBuilder::importLibrary($document, $library);

// Merge two libraries
$library->merge($otherLibrary);
```

## Keep IDs clean

Sprite sheets rely on stable, unique IDs. When combining icons from
different sources, ID collisions cause rendering bugs.

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\CleanupIdsPass;

// Prefix each icon's internal IDs before adding to the sprite
$optimizer = new Optimizer([
    new CleanupIdsPass(prefix: 'home-'),
]);
$optimizer->optimize($homeDocument);
```

Run `CleanupIdsPass` per icon **before** merging into the sprite.
Internal IDs (gradients, clip-paths, filters) get prefixed, and all
`url()` references update automatically.

## Optimize the final sprite

After building the sprite, run the optimizer to reduce file size:

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

$optimizer = new Optimizer(OptimizerPresets::default());
$optimizer->optimize($sprite);

$dumper->dumpToFile($sprite, 'sprite.min.svg');
```

Use `OptimizerPresets::accessible()` if you need to preserve `<title>`
and `<desc>` elements inside symbols for screen readers.

## Batch process a directory

```php
$library = new SymbolLibrary();
$loader = new DomLoader();

foreach (glob('icons/*.svg') as $file) {
    $id = pathinfo($file, PATHINFO_FILENAME);
    $doc = $loader->loadFromFile($file);
    $root = $doc->getRootElement();

    $symbol = new SymbolElement();
    $symbol->setId($id);
    $viewBox = $root->getAttribute('viewBox');
    if ($viewBox) {
        $symbol->setViewbox($viewBox);
    }

    foreach ($root->getChildren() as $child) {
        $symbol->appendChild($child);
    }

    $library->add($id, $symbol);
}

// Build the sprite
$sprite = Document::create();
SymbolBuilder::importLibrary($sprite, $library);
```

## See also

- [Structural elements](../elements/structure.md): SymbolElement, UseElement, DefsElement API
- [Batch optimize](batch-optimize.md): optimize multiple SVGs in a pipeline
- [Accessibility](accessibility.md): add titles and descriptions to icons
