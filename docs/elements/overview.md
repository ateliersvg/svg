# Elements

Atelier SVG represents every SVG node as a PHP object. The element tree is
built from two base classes, two interfaces, an attribute constants class,
and a collection helper.

## Class Hierarchy

```
ElementInterface
  AbstractElement              (attributes, id, classes, transforms, styles)
    AbstractContainerElement   (children: append, prepend, remove, clone)
      SvgElement, GroupElement, RectElement, TextElement, ...
    UseElement, StopElement, ViewElement, ...

ContainerElementInterface extends ElementInterface
  AbstractContainerElement
```

`AbstractElement` is the leaf base class. `AbstractContainerElement` adds
child management and is used by every element that can contain others.

## AbstractElement

All elements share a fluent API for attributes, classes, transforms, styles,
accessibility, and filters.

```php
use Atelier\Svg\Element\Shape\RectElement;

$rect = RectElement::create(0, 0, 100, 50)
    ->setId('box')
    ->setFill('#3b82f6')
    ->addClass('highlight')
    ->setOpacity(0.8)
    ->setTranslation(10, 20);

$rect->getAttribute('fill');    // '#3b82f6'
$rect->hasClass('highlight');   // true
$rect->getTagName();            // 'rect'
```

Key method groups on `AbstractElement`:

| Group        | Methods                                                         |
|--------------|-----------------------------------------------------------------|
| Attributes   | `getAttribute`, `setAttribute`, `removeAttribute`, `getAttributes` |
| Identity     | `setId`, `getId`                                                |
| Classes      | `addClass`, `removeClass`, `hasClass`, `toggleClass`, `getClasses` |
| Transforms   | `transform()`, `setTranslation`, `setRotation`, `setScale`     |
| Styles       | `style()`, `setStyle`, `getStyleProperty`, `setStyles`          |
| Filters      | `applyFilter`, `removeFilter`, `getFilterId`                    |
| Paint        | `setFillPaintServer`, `setStrokePaintServer`, `setOpacity`, `setFillRule` |
| Stroke       | `setStrokeLinecap`, `setStrokeLinejoin`, `setStrokeDasharray`, `setStrokeDashoffset`, `setStrokeMiterlimit` |
| Accessibility| `addTitle`, `addDescription`, `setAriaLabel`, `setAriaRole`     |
| Tree         | `getParent`, `setParent`, `clone`                               |

## AbstractContainerElement

Adds child management on top of `AbstractElement`.

```php
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Shape\CircleElement;

$group = new GroupElement();
$circle = CircleElement::create(50, 50, 30);

$group->appendChild($circle);
$group->prependChild($circle);
$group->removeChild($circle);
$group->getChildren();       // ElementInterface[]
$group->hasChildren();       // bool
$group->getChildCount();     // int
$group->clearChildren();
$group->cloneDeep();         // deep clone with all children
```

## Attributes Constants

`Atelier\Svg\Element\Attributes` provides named constants and helper methods
for common SVG attribute names.

```php
use Atelier\Svg\Element\Attributes;

$rect->setAttribute(Attributes::FILL, 'red');
$rect->setAttribute(Attributes::STROKE_WIDTH, 2);

Attributes::isPresentationAttribute('fill');   // true
Attributes::isGeometricAttribute('cx');        // true
Attributes::normalize('strokeWidth');          // 'stroke-width'
```

## ElementCollection

`ElementCollection` provides a fluent, chainable interface for batch
operations on multiple elements, similar to jQuery.

```php
use Atelier\Svg\Element\ElementCollection;

$collection = new ElementCollection($elements);

// Filtering
$rects = $collection->ofType('rect');
$highlighted = $collection->withClass('active');
$wide = $collection->where('width', '>', 100);
$withId = $collection->withAttribute('id');

// Batch operations
$collection->fill('#000')
           ->addClass('dark')
           ->opacity(0.5);

// Iteration and mapping
$ids = $collection->pluck('id');
$collection->each(fn ($el) => $el->addClass('processed'));

// Access
$collection->first();
$collection->last();
$collection->get(2);
$collection->count();
$collection->isEmpty();
```

## See also

- [Shapes](shapes.md): shape elements (rect, circle, path, ...)
- [Structure](structure.md): grouping and reuse elements
- [Animation](animation.md): SMIL animation elements and builder
- [Collections](collections.md): batch operations on multiple elements
- [Selectors](selectors.md): querying the element tree
