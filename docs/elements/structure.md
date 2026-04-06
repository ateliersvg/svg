---
order: 10
---
# Structure

Structural elements organize and reuse content in an SVG document.

## GroupElement

The `<g>` element groups children so transforms, styles, and attributes
apply collectively.

```php
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Shape\RectElement;

$group = new GroupElement();
$group->setFill('#3b82f6')
      ->setTranslation(10, 10);

$rect = RectElement::create(0, 0, 100, 50);
$group->appendChild($rect);
```

## DefsElement

The `<defs>` element stores elements (gradients, filters, symbols) that are
not rendered directly. Referenced objects must be placed here.

```php
use Atelier\Svg\Element\Structural\DefsElement;

$defs = new DefsElement();
$defs->appendChild($gradient);
$defs->appendChild($filter);
$svgRoot->prependChild($defs);
```

## SymbolElement

Defines a template with its own `viewBox` and `preserveAspectRatio`.
Symbols are never rendered; they are instantiated via `<use>`.

```php
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Value\Viewbox;

$symbol = new SymbolElement();
$symbol->setId('icon-star');
$symbol->setViewbox('0 0 24 24');
$symbol->setPreserveAspectRatio('xMidYMid meet');
$symbol->setWidth(24)->setHeight(24);
$symbol->appendChild($starPath);
```

Getters return typed value objects:

```php
$symbol->getViewbox();              // Viewbox|null
$symbol->getPreserveAspectRatio();  // PreserveAspectRatio|null
```

## UseElement

Instantiates a defined element (typically a symbol) at a given position.

```php
use Atelier\Svg\Element\Structural\UseElement;

$use = new UseElement();
$use->setHref('#icon-star');
$use->setX(50)->setY(50);
$use->setWidth(48)->setHeight(48);

$use->getHref();  // '#icon-star' (checks both href and xlink:href)
```

## ViewElement

Defines a named view with a specific `viewBox` and
`preserveAspectRatio`, used for fragment identifiers.

```php
use Atelier\Svg\Element\Structural\ViewElement;

$view = new ViewElement();
$view->setId('detail-view');
$view->setViewbox('100 100 200 200');
$view->setPreserveAspectRatio('xMinYMin slice');
```

## ForeignObjectElement

Embeds non-SVG content (HTML, MathML) inside the SVG coordinate system.

```php
use Atelier\Svg\Element\Structural\ForeignObjectElement;

$foreign = new ForeignObjectElement();
$foreign->setX(10)->setY(10);
$foreign->setWidth(200)->setHeight(100);
```

## SymbolLibrary

A utility for managing collections of symbols by ID.

```php
use Atelier\Svg\Element\Structural\SymbolLibrary;
use Atelier\Svg\Element\Structural\SymbolElement;

$library = new SymbolLibrary();

// Add a symbol (or any element: non-symbols are auto-wrapped)
$library->add('icon-star', $starSymbol);
$library->add('icon-heart', $heartPath);  // wrapped in SymbolElement

$library->has('icon-star');       // true
$library->get('icon-star');       // SymbolElement|null
$library->getIds();               // ['icon-star', 'icon-heart']
$library->getSymbols();           // ['icon-star' => SymbolElement, ...]

$library->remove('icon-star');
$library->merge($otherLibrary);
$library->clear();
```

## See also

- [Overview](overview.md): base element classes
- [Shapes](shapes.md): shape elements to place in groups
- [Selectors](selectors.md): querying structural trees
