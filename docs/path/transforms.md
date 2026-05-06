---
order: 50
---
# Transforms

Atelier SVG supports applying geometric transformations directly to path data (baking transforms into coordinates) and building transform attribute values for SVG elements.

## PathTransformer

`Atelier\Svg\Path\PathTransformer` applies a `Matrix` to every segment in a `Data` object, producing a new `Data` with transformed coordinates.

```php
use Atelier\Svg\Path\PathTransformer;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Geometry\Transformation;

$parser = new PathParser();
$data = $parser->parse('M 0,0 L 100,0 L 100,100 Z');

$matrix = Transformation::scale(2.0);
$transformer = new PathTransformer();
$scaled = $transformer->transform($data, $matrix);

echo $scaled; // coordinates are doubled
```

All segment types are handled, including control points on curves. H/V segments are promoted to full `LineTo` segments after transformation since scaling or rotation breaks their axis alignment. The transformer maintains a current-point cursor that tracks position through the path, including ClosePath segments that reset the cursor to the subpath start.

## Transformation

`Atelier\Svg\Geometry\Transformation` is a static factory for common transformation matrices.

```php
use Atelier\Svg\Geometry\Transformation;

$m = Transformation::identity();                     // no-op matrix
$m = Transformation::translate(10, 20);              // move
$m = Transformation::scale(2.0);                     // uniform scale
$m = Transformation::scale(2.0, 0.5);               // non-uniform scale
$m = Transformation::rotate(45);                     // degrees, around origin
$m = Transformation::rotate(45, 50, 50);             // degrees, around (50,50)
$m = Transformation::skewX(15);                      // horizontal skew
$m = Transformation::skewY(15);                      // vertical skew
```

## Matrix

`Atelier\Svg\Geometry\Matrix` is a readonly value object representing a 2D affine transformation matrix with six components (a, b, c, d, e, f).

```php
use Atelier\Svg\Geometry\Matrix;
use Atelier\Svg\Geometry\Point;

$m = new Matrix(a: 2, b: 0, c: 0, d: 2, e: 10, f: 20);

// Transform a point
$p = $m->transform(new Point(5, 5)); // Point(20, 30)

// Compose matrices
$combined = $m->multiply($other);

// Inverse
$inv = $m->inverse(); // throws RuntimeException if singular

// Inspect
$m->determinant();
$m->isIdentity();
$m->isUniformScale();
$m->hasShear();
$m->decompose(); // ['translateX', 'translateY', 'scaleX', 'scaleY', 'rotation', 'skewX']

// Transform a bounding box (all four corners)
$bbox = $m->transformBBox($boundingBox);

echo $m; // "matrix(2, 0, 0, 2, 10, 20)"
```



## TransformBuilder

`Atelier\Svg\Geometry\TransformBuilder` provides a fluent API for building and managing the `transform` attribute on SVG elements. It reads existing transforms from the element and writes them back on `apply()`.

```php
$element->transform()
    ->translate(50, 100)
    ->rotate(45)
    ->scale(1.5)
    ->apply();

// Convenience methods
$element->transform()->flipHorizontal(axisX: 50)->apply();
$element->transform()->flipVertical()->apply();
$element->transform()->skewX(15)->apply();

// Inspect current transforms
$element->transform()->toMatrix();       // combined Matrix
$element->transform()->getTranslation(); // [x, y]
$element->transform()->getScale();       // [sx, sy]
$element->transform()->getRotation();    // degrees
$element->transform()->decompose();      // full decomposition

// Replace individual components
$element->transform()->setTranslation(10, 20)->apply();
$element->transform()->setRotation(90, cx: 50, cy: 50)->apply();
$element->transform()->setScale(2)->apply();

// Reset
$element->transform()->clear()->apply();
```

## See also

- [Path data model](overview.md): Data, segments, parser
- [Path analysis](analysis.md): bounding boxes and length
- [CSS/SVG transforms](../styling/transforms.md): TransformList value objects
