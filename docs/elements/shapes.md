---
order: 20
---
# Shapes

Shape elements produce visible geometry. All shapes extend
`AbstractContainerElement` and provide typed getters/setters for their
SVG attributes. Getters return `Length` objects (or `null`) for
dimensional values.

## RectElement

```php
use Atelier\Svg\Element\Shape\RectElement;

$rect = RectElement::create(10, 10, 200, 100)
    ->setRx(8)->setRy(8);           // rounded corners

$rect->getWidth();  // Length|null
```

## CircleElement

```php
use Atelier\Svg\Element\Shape\CircleElement;

$circle = CircleElement::create(100, 100, 50);

$circle->getR();   // Length|null
```

## EllipseElement

```php
use Atelier\Svg\Element\Shape\EllipseElement;

$ellipse = EllipseElement::create(100, 50, 80, 40);
```

## LineElement

```php
use Atelier\Svg\Element\Shape\LineElement;

$line = LineElement::create(0, 0, 200, 100);
```

## PolygonElement

A closed shape from connected line segments. The last point connects back
to the first.

```php
use Atelier\Svg\Element\Shape\PolygonElement;

$polygon = PolygonElement::create('0,0 100,0 100,100 0,100');

// Or from an array of coordinate pairs
$polygon->setPointsFromArray([
    [0, 0], [100, 0], [100, 100], [0, 100],
]);

$polygon->getPointsAsArray(); // [[0, 0], [100, 0], ...]
```

## PolylineElement

Same as polygon but the shape is not closed.

```php
use Atelier\Svg\Element\Shape\PolylineElement;

$polyline = PolylineElement::create('0,0 50,80 100,0');
$polyline->setPointsFromArray([[0, 0], [50, 80], [100, 0]]);
```

## PathElement

The most powerful shape element. Defined by path data commands.

```php
use Atelier\Svg\Element\PathElement;

$path = new PathElement();
$path->setD('M10 10 L90 10 L90 90 Z');

// Or use the structured Data object
$data = $path->getData();              // Data|null (parsed segments)
$path->setData($data);                 // set from Data object
$path->getPathData();                  // raw 'd' string

// setD() is an alias for setPathData(): both set the 'd' attribute
```

The `Data` object from the `Path` namespace provides parsed segment access.
See the path documentation for details on `PathParser`, `PathBuilder`, and
segment types.

## Common Operations

All shapes inherit the full `AbstractElement` API:

```php
$rect->setFill('#3b82f6')
     ->setStroke('#1e40af')
     ->setStrokeWidth(2)
     ->setStrokeLinecap('round')
     ->setStrokeLinejoin('round')
     ->setStrokeDasharray('5,10')
     ->setFillRule('evenodd')
     ->setOpacity(0.9)
     ->addClass('shape')
     ->setTranslation(50, 50);
```

## See also

- [Overview](overview.md): base element classes and attributes
- [Structure](structure.md): grouping shapes
- [Styling overview](../../styling/overview.md): fill, stroke, and presentation attributes
- [Gradients](gradients.md): filling shapes with gradients
- [Filters](filters.md): applying filter effects
