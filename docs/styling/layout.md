---
order: 30
---
# Layout

Atelier SVG provides layout utilities for positioning, aligning, and arranging SVG elements within containers, as well as managing responsive viewports.

## LayoutBuilder

`Atelier\Svg\Layout\LayoutBuilder` operates on a container element (group, SVG root) and positions its children using bounding box calculations.

```php
$group->layout()->positionAt($element, x: 50, y: 100);
$group->layout()->center($element);
```

### Positioning

Place an element at exact coordinates. The `$anchor` parameter controls which point of the element lands at the target position.

```php
$group->layout()->positionAt($element, 50, 100, anchor: 'center');
```

Supported anchors: `top-left`, `top-center`, `top-right`, `center-left`, `center`, `center-right`, `bottom-left`, `bottom-center`, `bottom-right` (plus short forms: `tl`, `tc`, `tr`, `cl`, `c`, `cr`, `bl`, `bc`, `br`).

### Alignment

Align an element to a container edge with optional offset.

```php
$group->layout()->align($element, 'left', offset: 10);
$group->layout()->align($element, 'center');
```

Valid directions: `left`, `right`, `top`, `bottom`, `center`.

### Stacking

Arrange elements sequentially along an axis.

```php
$group->layout()->stack($elements, direction: 'vertical', gap: 10, align: 'center');
$group->layout()->stack($elements, direction: 'horizontal', gap: 5, align: 'top');
```

### Distribution

Distribute elements evenly within the container's bounds.

```php
$group->layout()->distribute($elements, direction: 'horizontal', gap: 10, align: 'center');
```

### Grid

Arrange elements in a grid layout.

```php
$group->layout()->grid(
    $elements,
    columns: 3,
    gapX: 10,
    gapY: 10,
    alignH: 'center',
    alignV: 'center',
);
```

All LayoutBuilder methods return `$this` for chaining.

## LayoutManager

`Atelier\Svg\Layout\LayoutManager` provides static utilities for document-level viewport management.

### Responsive SVG

Remove fixed dimensions and ensure a viewBox is set so the SVG scales with its container.

```php
use Atelier\Svg\Layout\LayoutManager;

LayoutManager::makeResponsive($document);
```

### ViewBox Management

```php
LayoutManager::setViewBox($document, 0, 0, 800, 600);
$vb = LayoutManager::getViewBox($document); // [minX, minY, width, height]
LayoutManager::setPreserveAspectRatio($document, 'xMidYMid meet');
```

### Intrinsic Size

Set width/height attributes alongside a matching viewBox for elements that need both intrinsic sizing and responsive scaling.

```php
LayoutManager::setIntrinsicSize($document, 400, 300);
```

### Content Fitting

Automatically adjust the viewBox to fit the actual content, with optional padding.

```php
LayoutManager::fitViewBoxToContent($document, padding: 10);
LayoutManager::cropToContent($document);
```

### Aspect Ratio

Adjust the viewBox dimensions to match a target aspect ratio.

```php
LayoutManager::setAspectRatio($document, 16 / 9);
```

## BoundingBox for Layout

The `BoundingBox` class supports anchor points and set operations useful for layout calculations.

```php
$bbox = $element->bbox()->get();

// Anchor points for positioning
$topCenter = $bbox->getAnchor('top-center');
$center = $bbox->getCenter();

// Combine multiple element bounds
$combined = $bbox1->union($bbox2);
$overlap = $bbox1->intersect($bbox2); // null if none

// Add margin
$padded = $bbox->expand(10);
```

## See also

- [Style system](overview.md): styling elements after layout
- [Path analysis](../path/analysis.md): BoundingBox and BoundingBoxCalculator details

