---
order: 90
---
# Collections

`Atelier\Svg\Element\ElementCollection` provides a fluent, chainable
interface for batch operations on multiple elements.

## Creating Collections

```php
use Atelier\Svg\Element\ElementCollection;

// From an array of elements
$collection = new ElementCollection([$rect, $circle, $line]);

// From a CSS selector query
$collection = $document->querySelectorAll('circle');
$collection = $document->querySelectorAll('.highlight');
```

## Filtering

All filtering methods return a new collection, leaving the original unchanged.

```php
// By tag name
$circles = $collection->ofType('circle');

// By CSS class
$highlighted = $collection->withClass('active');

// By attribute presence
$withId = $collection->withAttribute('id');

// By attribute value with operators
$wide = $collection->where('width', '>', 100);
$red = $collection->where('fill', '=', 'red');
$named = $collection->where('id', '!=', null);

// Custom filter
$large = $collection->filter(fn ($el) => (float) $el->getAttribute('r') > 50);

// Inverse filter
$small = $collection->reject(fn ($el) => (float) $el->getAttribute('r') > 50);
```

Supported operators for `where()`: `=`, `!=`, `>`, `<`, `>=`, `<=`, `contains`.

## Batch Attribute Operations

```php
$collection->setAttribute('fill', '#3b82f6');
$collection->attr('stroke', '#000');       // alias for setAttribute
$collection->removeAttribute('opacity');
```

## Batch Style Shortcuts

Convenience methods that call `setAttribute` internally.

```php
$collection
    ->fill('#3b82f6')
    ->stroke('#000')
    ->strokeWidth(2)
    ->opacity(0.8)
    ->fillOpacity(0.5)
    ->strokeOpacity(0.7)
    ->transform('rotate(45)')
    ->display('inline')
    ->visibility('visible')
    ->cursor('pointer')
    ->pointerEvents('none');
```

All return the collection for chaining.

## Batch Class Operations

```php
$collection->addClass('selected');
$collection->removeClass('old');
$collection->toggleClass('active');
```

## Iteration and Mapping

```php
// Execute a callback on each element
$collection->each(fn ($el, $i) => $el->setId("item-{$i}"));

// Map to an array of values
$ids = $collection->map(fn ($el) => $el->getId());

// Extract a single attribute from all elements
$fills = $collection->pluck('fill');

// Reduce to a single value
$totalWidth = $collection->reduce(
    fn ($sum, $el) => $sum + (float) $el->getAttribute('width'),
    0,
);
```

## Access

```php
$collection->first();    // ?ElementInterface
$collection->last();     // ?ElementInterface
$collection->get(2);     // ?ElementInterface
$collection->count();    // int
$collection->isEmpty();  // bool
$collection->toArray();  // ElementInterface[]

// Iteration
foreach ($collection as $element) {
    // ...
}
```

## DOM Operations

```php
// Remove all matched elements from their parents
$collection->remove();

// Clone elements
$shallow = $collection->clone();
$deep = $collection->cloneDeep();
```

## See also

- [Elements overview](overview.md): element class hierarchy
- [Selectors](selectors.md): CSS selector queries that return collections
- [Shapes](shapes.md): shape elements
