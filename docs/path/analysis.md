---
order: 30
---
# Analysis

Atelier SVG provides tools for measuring and inspecting paths: computing lengths, locating points along a path, calculating bounding boxes, and comparing paths.

## PathAnalyzer

`Atelier\Svg\Path\PathAnalyzer` accepts a `Data` object and computes geometric properties.

```php
use Atelier\Svg\Path\PathAnalyzer;
use Atelier\Svg\Path\PathParser;

$parser = new PathParser();
$data = $parser->parse('M 0,0 L 100,0 L 100,100 Z');

$analyzer = new PathAnalyzer($data);
```

### Length

Calculates the total path length. Uses adaptive subdivision for curves and arcs.

```php
$length = $analyzer->getLength(); // float
```

### Point at Length

Returns the `Point` at a given distance along the path, or `null` if the length exceeds the path.

```php
$point = $analyzer->getPointAtLength(50.0);
// Point with x, y coordinates at 50 units along the path
```

### Bounding Box

Computes the axis-aligned bounding box. For curves, it samples points along the curve for accuracy.

```php
$bbox = $analyzer->getBoundingBox();
// BoundingBox with minX, minY, maxX, maxY
```

### Point Containment

Tests whether a point lies inside a closed path using the even-odd ray casting rule.

```php
$inside = $analyzer->containsPoint(new Point(50, 50)); // bool
```

### Vertices and Center

```php
$vertices = $analyzer->getVertices(); // Point[]: sampled vertices
$center = $analyzer->getCenter();     // center of the bounding box
```

### Shortcuts from PathBuilder

PathBuilder exposes analyzer shortcuts directly:

```php
$builder = PathBuilder::startAt(0, 0)->lineTo(100, 100);
$builder->getLength();
$builder->getBoundingBox();
$builder->getPointAtLength(70.0);
```

## BoundingBox

`Atelier\Svg\Geometry\BoundingBox` is a readonly value object.

```php
use Atelier\Svg\Geometry\BoundingBox;
use Atelier\Svg\Geometry\Point;

$bbox = new BoundingBox(minX: 0, minY: 0, maxX: 100, maxY: 50);

$bbox->getWidth();    // 100
$bbox->getHeight();   // 50
$bbox->getCenter();   // Point(50, 25)
$bbox->getArea();     // 5000
$bbox->getPerimeter(); // 300
$bbox->contains(new Point(50, 25)); // true

// Anchor points: 'top-left', 'center', 'bottom-right', etc.
$bbox->getAnchor('top-center'); // Point(50, 0)

// Combine bounding boxes
$union = $bbox->union($otherBbox);
$inter = $bbox->intersect($otherBbox); // null if no overlap
$bbox->intersects($otherBbox);         // bool

$bbox->expand(10); // add 10px margin on all sides

// Create from points
$bbox = BoundingBox::fromPoints(new Point(0, 0), new Point(100, 50));
```

## BoundingBoxCalculator

`Atelier\Svg\Geometry\BoundingBoxCalculator` computes bounding boxes for SVG elements (rect, circle, ellipse, line, polygon, polyline, path, groups, SVG root).

```php
$calculator = $element->bbox(); // returns BoundingBoxCalculator

$local = $calculator->getLocal();  // without transforms
$transformed = $calculator->get(); // with element transforms
$screen = $calculator->getScreen(); // with all parent transforms
```

## PathDistance

`Atelier\Svg\Path\PathDistance` computes distance metrics between two paths by sampling points along each path.

```php
use Atelier\Svg\Path\PathDistance;

// Hausdorff distance (max nearest-point distance)
$d = PathDistance::hausdorff($path1, $path2, samples: 50);

// Discrete Frechet distance (minimum leash length)
$d = PathDistance::discreteFrechet($path1, $path2);

// Average distance between corresponding points
$d = PathDistance::averageDistance($path1, $path2);

// Maximum distance between corresponding points
$d = PathDistance::maxPointDistance($path1, $path2);
```

All methods accept an optional `$samples` parameter (default 50) controlling how many points are sampled along each path.

## See also

- [Path data model](overview.md): Data, segments, parser
- [Building paths](building.md): PathBuilder, ShapeFactory
- [Path simplification](simplification.md): reducing point count
