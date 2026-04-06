---
order: 25
---
# Geometry

Atelier SVG includes a set of geometry primitives used throughout the
library for coordinates, transformations, and spatial calculations.

## Point

`Atelier\Svg\Geometry\Point` is a readonly value object representing a 2D
coordinate.

```php
use Atelier\Svg\Geometry\Point;

$a = new Point(10, 20);
$b = new Point(30, 40);

$a->x;              // 10.0
$a->y;              // 20.0

$c = $a->add($b);        // Point(40, 60)
$d = $b->subtract($a);   // Point(20, 20)

$a->distanceTo($b);      // 28.284...
$a->equals($b);          // false
$a->equals(new Point(10, 20)); // true

echo $a; // "10,20"
```

Point is immutable. `add()` and `subtract()` return new instances.

The optional `$epsilon` parameter on `equals()` controls floating-point
tolerance (default `0.0001`).

## BoundingBox

`Atelier\Svg\Geometry\BoundingBox` represents an axis-aligned rectangle.
See [Path analysis](analysis.md) for full API documentation and
usage with `PathAnalyzer`.

```php
use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Point;

$bbox = new BoundingBox(minX: 0, minY: 0, maxX: 100, maxY: 50);

$bbox->getWidth();     // 100
$bbox->getHeight();    // 50
$bbox->getCenter();    // Point(50, 25)
$bbox->getArea();      // 5000
$bbox->contains(new Point(10, 10)); // true

// Anchor points
$bbox->getAnchor('top-center');    // Point(50, 0)
$bbox->getAnchor('bottom-right');  // Point(100, 50)

// Set operations
$union = $bbox->union($other);
$inter = $bbox->intersect($other); // null if no overlap

// Create from points
$bbox = BoundingBox::fromPoints(new Point(0, 0), new Point(100, 50));
```

### BoundingBoxCalculator

Computes bounding boxes for SVG elements with transform support.

```php
$calculator = $element->bbox();

$local = $calculator->getLocal();   // without transforms
$transformed = $calculator->get();  // with element transforms
$screen = $calculator->getScreen(); // with all ancestor transforms
```

Supports rect, circle, ellipse, line, polygon, polyline, path, groups,
and SVG root elements.

## Matrix

`Atelier\Svg\Geometry\Matrix` is a readonly 2D affine transformation matrix
(6 components: a, b, c, d, e, f). See [Path transforms](transforms.md)
for full documentation.

```php
use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;

$m = new Matrix(a: 2, b: 0, c: 0, d: 2, e: 10, f: 20);

$m->transform(new Point(5, 5));  // Point(20, 30)
$m->multiply($other);            // compose matrices
$m->inverse();                   // inverse (throws if singular)
$m->determinant();               // float
$m->isIdentity();                // bool
$m->decompose();                 // ['translateX', 'scaleX', 'rotation', ...]

echo $m; // "matrix(2, 0, 0, 2, 10, 20)"
```

## Transformation

`Atelier\Svg\Geometry\Transformation` is a static factory for common
matrices. See [Path transforms](transforms.md) for full documentation.

```php
use Atelier\Svg\Geometry\Transformation;

Transformation::identity();
Transformation::translate(10, 20);
Transformation::scale(2.0);
Transformation::scale(2.0, 0.5);
Transformation::rotate(45);
Transformation::rotate(45, 50, 50);
Transformation::skewX(15);
Transformation::skewY(15);
```

## TransformBuilder

`Atelier\Svg\Geometry\TransformBuilder` provides a fluent API for building
element transforms. See [Path transforms](transforms.md) for full
documentation.

```php
$element->transform()
    ->translate(50, 100)
    ->rotate(45)
    ->scale(1.5)
    ->apply();
```

## See also

- [Path analysis](analysis.md): BoundingBox and PathAnalyzer details
- [Path transforms](transforms.md): Matrix, Transformation, TransformBuilder
- [CSS/SVG transforms](../styling/transforms.md): TransformList value objects
- [Layout](../styling/layout.md): BoundingBox for layout calculations
