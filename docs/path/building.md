---
order: 10
---
# Building

Atelier SVG provides three complementary APIs for constructing paths programmatically: `PathBuilder` for standalone path construction, `FluentPathBuilder` for inline use with the document Builder, and `ShapeFactory` for common geometric shapes.


## PathBuilder

`Atelier\Svg\Path\PathBuilder` offers a chainable API that tracks the current point and validates command ordering (e.g., you cannot `lineTo` before a `moveTo`).

```php
use Atelier\Svg\Path\PathBuilder;

// Static factory methods
$builder = PathBuilder::new();
$builder = PathBuilder::startAt(10, 20);

// Chain drawing commands
$data = PathBuilder::startAt(0, 0)
    ->lineTo(100, 0)
    ->lineTo(100, 100)
    ->lineTo(0, 100)
    ->closePath()
    ->toData();

echo $data; // "M 0,0 L 100,0 L 100,100 L 0,100 Z"
```

### Available Commands

All drawing commands accept a `bool $relative = false` parameter to switch between absolute and relative coordinates.

| Method | SVG Command |
|---|---|
| `moveTo(x, y)` | M / m |
| `lineTo(x, y)` | L / l |
| `horizontalLineTo(x)` | H / h |
| `verticalLineTo(y)` | V / v |
| `curveTo(x1, y1, x2, y2, x, y)` | C / c |
| `smoothCurveTo(x2, y2, x, y)` | S / s |
| `quadraticCurveTo(x1, y1, x, y)` | Q / q |
| `smoothQuadraticCurveTo(x, y)` | T / t |
| `arcTo(rx, ry, rotation, largeArc, sweep, x, y)` | A / a |
| `closePath()` / `close()` | Z |


### Output Methods

```php
$builder->toData();       // returns Path\Data object
$builder->getPathData();  // returns the path string
$builder->toPath();       // returns a Path object
$builder->analyze();      // returns a PathAnalyzer
$builder->getLength();    // shortcut to path length
$builder->getBoundingBox(); // shortcut to bounding box
```


## FluentPathBuilder

`Atelier\Svg\Element\FluentPathBuilder` wraps `PathBuilder` and integrates with the document `Builder`. Call `end()` to finalize the path and return to the parent builder.

```php
$builder->path()
    ->moveTo(10, 80)
    ->curveTo(40, 10, 65, 10, 95, 80)
    ->close();  // closePath() + end()
```

## ShapeFactory

`Atelier\Svg\Path\ShapeFactory` generates `PathBuilder` instances for common shapes.

```php
use Atelier\Svg\Path\ShapeFactory;

// Rectangle (with optional rounded corners)
$rect = ShapeFactory::rectangle(0, 0, 100, 50);
$rounded = ShapeFactory::rectangle(0, 0, 100, 50, rx: 8, ry: 8);

// Circle and ellipse
$circle = ShapeFactory::circle(cx: 50, cy: 50, r: 40);
$ellipse = ShapeFactory::ellipse(cx: 50, cy: 50, rx: 40, ry: 25);

// Regular polygon (sides >= 3)
$hexagon = ShapeFactory::polygon(50, 50, radius: 40, sides: 6);
$rotated = ShapeFactory::polygon(50, 50, 40, 5, rotation: 36);

// Star (points >= 3)
$star = ShapeFactory::star(50, 50, outerRadius: 40, innerRadius: 20, points: 5);

// Line
$line = ShapeFactory::line(0, 0, 100, 100);

// Polyline from coordinate pairs
$polyline = ShapeFactory::polyline([[0, 0], [50, 25], [100, 0]]);

// Closed polygon from coordinate pairs (>= 3 points)
$polygon = ShapeFactory::polygonFromPoints([[0, 0], [100, 0], [50, 80]]);
```

Each factory method returns a `PathBuilder`, so you can chain further commands or extract the data:

```php
$d = ShapeFactory::circle(50, 50, 40)->getPathData();
```


## See also

- [Path data model](overview.md): Data, segments, parser
- [Path analysis](analysis.md): measure lengths and bounding boxes
- [Path transforms](transforms.md): apply geometric transformations
