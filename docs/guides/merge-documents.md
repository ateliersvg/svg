---
order: 70
description: "Combine multiple SVG documents into one using merge strategies, or import individual elements between documents."
---
# Merge SVG Documents

Combine multiple SVG files into one document, or import elements from one document into another.

## Merge multiple files

`Document::merge()` takes an array of documents and returns a new merged document. The default strategy appends all children from each source into a single `<svg>` root.

```php
<?php

use Atelier\Svg\Document;
use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Dumper\CompactXmlDumper;

$loader = new DomLoader();

$base   = $loader->loadFromFile('chart-base.svg');
$labels = $loader->loadFromFile('chart-labels.svg');
$legend = $loader->loadFromFile('chart-legend.svg');

$merged = Document::merge([$base, $labels, $legend]);

$dumper = new CompactXmlDumper();
$dumper->dumpToFile($merged, 'chart-complete.svg');
```

## Layout strategies

Pass a `strategy` option to control how documents are arranged.

| Strategy | Effect |
|----------|--------|
| `MergeStrategy::APPEND` | All children stacked in the same coordinate space (default) |
| `MergeStrategy::SIDE_BY_SIDE` | Documents placed left-to-right, separated by optional `spacing` |
| `MergeStrategy::STACKED` | Documents placed top-to-bottom, separated by optional `spacing` |
| `MergeStrategy::SYMBOLS` | Each document becomes a `<symbol>` inside `<defs>` |
| `MergeStrategy::GRID` | Documents placed in a grid with configurable `columns` |

```php
<?php

use Atelier\Svg\Document;
use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Loader\DomLoader;

$loader = new DomLoader();
$docs = array_map(
    fn ($file) => $loader->loadFromFile($file),
    glob('icons/*.svg'),
);

// Lay out icons in a 4-column grid with 8px spacing
$sheet = Document::merge($docs, [
    'strategy' => MergeStrategy::GRID,
    'columns'  => 4,
    'spacing'  => 8.0,
]);
```

## Build a sprite sheet

`MergeStrategy::SYMBOLS` wraps each document in a `<symbol>` element. Pass `symbol_ids` to assign stable IDs.

```php
<?php

use Atelier\Svg\Document;
use Atelier\Svg\Document\MergeStrategy;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Dumper\CompactXmlDumper;

$loader = new DomLoader();

$files = [
    'home'   => 'icons/home.svg',
    'user'   => 'icons/user.svg',
    'search' => 'icons/search.svg',
];

$docs = [];
$ids  = [];
foreach ($files as $id => $file) {
    $docs[] = $loader->loadFromFile($file);
    $ids[]  = $id;
}

$sprite = Document::merge($docs, [
    'strategy'   => MergeStrategy::SYMBOLS,
    'symbol_ids' => $ids,
]);

(new CompactXmlDumper())->dumpToFile($sprite, 'sprite.svg');
```

Reference a symbol in HTML:

```html
<svg width="24" height="24"><use href="sprite.svg#home"></use></svg>
```

## Import individual elements

`Document::importElement()` clones an element from one document and returns an imported copy ready to append anywhere in the target document.

```php
<?php

use Atelier\Svg\Document;
use Atelier\Svg\Loader\DomLoader;

$loader = new DomLoader();
$target = $loader->loadFromFile('canvas.svg');
$source = $loader->loadFromFile('overlay.svg');

// Find and import a single element by selector
$arrow = $source->querySelector('#arrow');
if ($arrow !== null) {
    $imported = $target->importElement($arrow, deep: true);
    $target->getRootElement()->appendChild($imported);
}
```

To import several elements at once:

```php
<?php

$elements = $source->querySelectorAll('.badge')->toArray();
$imported = $target->importElements($elements, deep: true);

foreach ($imported as $el) {
    $target->getRootElement()->appendChild($el);
}
```

## Append a whole document

`Document::append()` imports all top-level children from another document into the current one. This modifies the receiver in place.

```php
<?php

$base->append($overlay);
```

## ID conflicts

When sources share IDs (gradients, clip-paths, filters), merging without handling them causes elements to silently reference the wrong definition.

**Automatic resolution** - the default. Conflicting IDs are renamed with a generated suffix:

```php
<?php

$merged = Document::merge([$docA, $docB]);
// IDs are resolved automatically
```

**Explicit prefix** - gives each document's IDs a deterministic namespace:

```php
<?php

$merged = Document::merge([$docA, $docB], [
    'prefix_ids' => true,   // adds "doc0-", "doc1-", etc.
]);

// Or supply a fixed string prefix for importElement:
$imported = $target->importElement($element, deep: true, options: [
    'prefix_ids' => 'overlay-',
]);
```

## viewBox differences

`Document::merge()` does not reconcile different viewBoxes. Under `APPEND`, all content lands in the same coordinate space - elements sized for a `0 0 24 24` viewBox will overlap elements sized for `0 0 100 100`. Use `SIDE_BY_SIDE` or `STACKED` to let the merge code wrap each document in a translated `<g>` that preserves its original positioning, or apply a `transform` manually after importing.

## See also

- [Sprite sheets](sprites-and-symbols.md): full workflow for SVG icon sprites
- [Batch optimize](batch-optimize.md): optimize merged output in a pipeline
