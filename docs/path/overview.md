# Paths

The path system in Atelier SVG provides a structured representation of SVG path data (`d` attribute). Instead of working with raw strings, paths are modeled as a collection of typed segment objects.

## Core Classes

### Data

`Atelier\Svg\Path\Data` holds an ordered list of segments and implements `Stringable`.

```php
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Geometry\Point;

$data = new Data([
    new MoveTo('M', new Point(10, 20)),
    new LineTo('L', new Point(50, 60)),
]);

$data->count();      // 2
$data->isEmpty();    // false
$data->toString();   // "M 10,20 L 50,60"
echo $data;          // same: implements Stringable

$data->reverse();    // returns a new Data with reversed direction
$data->subpath(0, 1); // returns a Data with segments 0 through 1
```

### SegmentInterface

All segments implement `Atelier\Svg\Path\Segment\SegmentInterface`:

| Method | Description |
|---|---|
| `getCommand(): string` | SVG command letter (`M`, `l`, `C`, ...) |
| `getTargetPoint(): ?Point` | End point of the segment (null for ClosePath) |
| `isRelative(): bool` | Whether the command uses relative coordinates |
| `commandArgumentsToString(): string` | Serialized arguments for the `d` attribute |

### Segment Types

| Class | Commands | Parameters |
|---|---|---|
| `MoveTo` | M / m | target point |
| `LineTo` | L / l | target point |
| `HorizontalLineTo` | H / h | x value |
| `VerticalLineTo` | V / v | y value |
| `CurveTo` | C / c | two control points + target point |
| `SmoothCurveTo` | S / s | one control point + target point |
| `QuadraticCurveTo` | Q / q | one control point + target point |
| `SmoothQuadraticCurveTo` | T / t | target point |
| `ArcTo` | A / a | rx, ry, rotation, flags, target point |
| `ClosePath` | Z / z | (none) |

### PathParser

`Atelier\Svg\Path\PathParser` converts a path data string into a `Data` object.

```php
use Atelier\Svg\Path\PathParser;

$parser = new PathParser();
$data = $parser->parse('M 10,10 C 20,20 40,20 50,10 Z');

foreach ($data->getSegments() as $segment) {
    echo $segment->getCommand(); // M, C, Z
}
```

### Serializer

`Atelier\Svg\Path\Serializer` converts structured command arrays back to a path string. It accepts arrays or objects with `type` and `coords` fields.

```php
use Atelier\Svg\Path\Serializer;

$d = Serializer::serialize([
    ['type' => 'M', 'coords' => [10, 20]],
    ['type' => 'L', 'coords' => [50, 60]],
    ['type' => 'Z'],
], precision: 2);

// "M10 20 L50 60 Z"
```

The `precision` parameter (default 6) controls decimal places in coordinate output. Trailing zeros are stripped automatically.

## See also

- [Building paths](building.md): PathBuilder, ShapeFactory
- [Path analysis](analysis.md): length, bounding box, point-at-length
- [Path transforms](transforms.md): applying matrix transforms to path data
- [Geometry](geometry.md): Point, BoundingBox, Matrix primitives
