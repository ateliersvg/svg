---
order: 40
---
# Simplification

Atelier SVG includes algorithms for reducing the number of points in a path while preserving its visual shape. This is useful for optimizing file size and improving rendering performance.

## SimplifierInterface

All simplifiers implement `Atelier\Svg\Path\Simplifier\SimplifierInterface`:

```php
public function simplify(Data $pathData, float $tolerance): Data;
```

The method returns a new `Data` object: the original is not modified. The `$tolerance` parameter controls how aggressively points are removed. A tolerance of zero returns the original path unchanged. Negative values throw `InvalidArgumentException`.

Non-line segments (curves, arcs, close commands) are preserved as-is. Only sequences of `MoveTo` + `LineTo` segments (polylines) are simplified.

## Simplifier (Ramer-Douglas-Peucker)

`Atelier\Svg\Path\Simplifier\Simplifier` uses the Ramer-Douglas-Peucker algorithm. It recursively finds the point farthest from the line between the start and end, keeping it only if its perpendicular distance exceeds the tolerance.

The tolerance represents the maximum allowed perpendicular distance (in SVG user units) from a removed point to the simplified line.

```php
use Atelier\Svg\Path\Simplifier\Simplifier;
use Atelier\Svg\Path\PathParser;

$parser = new PathParser();
$data = $parser->parse('M 0,0 L 1,0.1 L 2,0 L 3,0.1 L 4,0');

$simplifier = new Simplifier();
$simplified = $simplifier->simplify($data, tolerance: 0.2);

// Points within 0.2 units of the straight line are removed
echo $simplified; // "M 0,0 L 4,0"
```

Best suited for paths where preserving endpoints is important.

## VisvalingamWhyattSimplifier

`Atelier\Svg\Path\Simplifier\VisvalingamWhyattSimplifier` uses the Visvalingam-Whyatt algorithm. It iteratively removes the point that forms the smallest triangle with its neighbors, stopping when all remaining triangles exceed the area tolerance.

The tolerance represents the minimum triangle area (in square SVG user units) that a point must contribute to be retained.

```php
use Atelier\Svg\Path\Simplifier\VisvalingamWhyattSimplifier;

$simplifier = new VisvalingamWhyattSimplifier();
$simplified = $simplifier->simplify($data, tolerance: 5.0);
```

Tends to produce more visually pleasing results than RDP for cartographic and organic shapes.

## CollinearPointRemover

`Atelier\Svg\Path\Simplifier\CollinearPointRemover` removes points that are nearly collinear with their neighbors. For each interior point, it computes the perpendicular distance to the line formed by the previous and next points. Points within tolerance are dropped.

```php
use Atelier\Svg\Path\Simplifier\CollinearPointRemover;

$simplifier = new CollinearPointRemover();
$simplified = $simplifier->simplify($data, tolerance: 0.5);
```

This is the fastest of the three algorithms. Use it as a first pass to strip obviously redundant points before applying a more sophisticated simplifier.

## Choosing a Tolerance

| Tolerance | Effect |
|---|---|
| 0 | No simplification (original path returned) |
| 0.1 - 1.0 | Light cleanup, nearly imperceptible |
| 1.0 - 5.0 | Moderate reduction, slight smoothing |
| 5.0+ | Aggressive reduction, visible simplification |

The right value depends on the scale of your coordinates and the level of detail required.

## See also

- [Path data model](overview.md): Data, segments, parser
- [Path analysis](analysis.md): measure paths before and after simplification
- [Optimization](../optimization/overview.md): broader optimization strategies
