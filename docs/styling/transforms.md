---
order: 20
---
# Transforms

Atelier SVG models the SVG `transform` attribute as a `TransformList` containing typed transform function objects.

## TransformList

`Atelier\Svg\Value\TransformList` parses and serializes the `transform` attribute value. It supports all standard SVG transform functions.

```php
use Atelier\Svg\Value\TransformList;

$list = TransformList::parse('translate(10, 20) rotate(45) scale(2)');

$list->getTransforms(); // array of Transform objects
$list->count();         // 3
$list->isEmpty();       // false
echo $list;             // "translate(10, 20) rotate(45) scale(2)"

// Create from an array of Transform objects
$list = TransformList::fromArray($transforms);
```

## Transform Functions

Each transform function is a separate class implementing the `Atelier\Svg\Value\Transform` interface. All reside in the `Atelier\Svg\Value\Transform` namespace.

### TranslateTransform

```php
use Atelier\Svg\Value\Transform\TranslateTransform;
use Atelier\Svg\Value\Length;

$t = new TranslateTransform(Length::parse(10), Length::parse(20));
$t->getTx(); // Length
$t->getTy(); // Length
echo $t;     // "translate(10, 20)"
```

### RotateTransform

```php
use Atelier\Svg\Value\Transform\RotateTransform;
use Atelier\Svg\Value\Angle;
use Atelier\Svg\Value\Length;

// Rotate around origin
$r = new RotateTransform(Angle::parse(45));

// Rotate around a center point
$r = new RotateTransform(
    Angle::parse(45),
    Length::parse(50),  // cx
    Length::parse(50),  // cy
);

$r->getAngle(); // Angle
$r->getCx();    // ?Length
$r->getCy();    // ?Length
```

### ScaleTransform

```php
use Atelier\Svg\Value\Transform\ScaleTransform;

$s = new ScaleTransform(sx: 2.0, sy: 1.5);
$s->getSx(); // float
$s->getSy(); // float
echo $s;     // "scale(2, 1.5)"
```

### SkewXTransform / SkewYTransform

```php
use Atelier\Svg\Value\Transform\SkewXTransform;
use Atelier\Svg\Value\Transform\SkewYTransform;
use Atelier\Svg\Value\Angle;

$sx = new SkewXTransform(Angle::parse(15));
$sy = new SkewYTransform(Angle::parse(10));
$sx->getAngle(); // Angle
```

### MatrixTransform

```php
use Atelier\Svg\Value\Transform\MatrixTransform;

$m = new MatrixTransform(a: 1, b: 0, c: 0, d: 1, e: 10, f: 20);
$m->getA(); // through getF()
echo $m;    // "matrix(1, 0, 0, 1, 10, 20)"
```

## Working with Elements

The `TransformBuilder` (accessed via `$element->transform()`) uses these transform objects internally and provides a higher-level fluent API for manipulating an element's transform attribute.

```php
// Parse existing transforms from an element
$list = TransformList::parse($element->getAttribute('transform'));
foreach ($list->getTransforms() as $transform) {
    // inspect individual transforms
}

// Build and apply new transforms via TransformBuilder
$element->transform()
    ->translate(10, 20)
    ->rotate(45, 50, 50)
    ->scale(2)
    ->apply();
```

## Relationship to Geometry\Matrix

The `TransformBuilder::toMatrix()` method converts the full chain of transform functions into a single `Geometry\Matrix`. This lets you work with the composed transformation as a single affine matrix for calculations like point transformation or bounding box computation.

```php
$matrix = $element->transform()->toMatrix();
$point = $matrix->transform(new Point(10, 20));
```

## See also

- [Path transforms](../path/transforms.md): baking transforms into path coordinates
- [Style system](overview.md): Style, StyleBuilder, themes
- [Value types](values.md): Length, Angle, Color
