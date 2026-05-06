---
order: 90
description: "Distribute markers, dots, or labels evenly along an SVG path using the path's length and getPointAtLength() - useful for trails, route maps, and animation guides."
---

# Place Markers Along a Path

Sometimes you have a curve and you need to put _things_ on it: dots along a hike trail, frame markers on an animation timeline, station pins on a route, or a dashed-by-hand guide line. The trick is to ask the path how long it is, then sample points at regular intervals.

## The basics

`Path::parse()` accepts any SVG path data string. From there, `getLength()` returns the total length and `getPointAtLength($l)` returns a `Point` (with public `x` / `y`) at distance `$l` along the curve.

```php
<?php

use Atelier\Svg\Path\Path;

$trail = Path::parse('M20,80 C 80,20 220,20 280,80');

$length = $trail->getLength();          // 281.82…
$mid    = $trail->getPointAtLength($length / 2); // ~Point(150, 35)
```

Length is in user units - the same units your `viewBox` uses. There's no DPI involved.

## Drop evenly-spaced markers

Pick a count, divide the length, sample at each step. Closing the loop with `<= $count` ensures both endpoints are hit.

```php
<?php

use Atelier\Svg\Path\Path;
use Atelier\Svg\Svg;

$d      = 'M20,80 C 80,20 220,20 280,80';
$trail  = Path::parse($d);
$length = $trail->getLength();

$svg = Svg::create(300, 100)
    ->path($d, ['stroke' => '#888', 'fill' => 'none', 'stroke-width' => '1']);

$count = 7;
for ($i = 0; $i <= $count; $i++) {
    $point = $trail->getPointAtLength($length * $i / $count);
    $svg->circle($point->x, $point->y, 3, ['fill' => '#3b82f6']);
}

$svg->save('trail.svg');
```

Note that the path data is passed _twice_: once to `Path::parse()` for measuring, once to `Svg::path()` so the curve is actually drawn. The path geometry and its rendered element are independent - `Path` is a measurement tool, not a DOM element.

## Step every N units

If you want a dot every 20 units regardless of total length, drive the loop by distance:

```php
<?php

$step = 20;
for ($l = 0; $l <= $length; $l += $step) {
    $point = $trail->getPointAtLength($l);
    $svg->circle($point->x, $point->y, 2, ['fill' => '#888']);
}
```

For a 282-unit trail with `$step = 20`, you'll get 15 markers - the last one slightly before the end. If reaching the endpoint exactly matters, add `$trail->getPointAtLength($length)` after the loop.

## Place differently-sized markers (start, mid, end)

Mix custom rendering at known positions:

```php
<?php

$start = $trail->getPointAtLength(0);
$end   = $trail->getPointAtLength($length);
$mid   = $trail->getPointAtLength($length / 2);

$svg->circle($start->x, $start->y, 5, ['fill' => '#10b981']);          // origin
$svg->circle($end->x,   $end->y,   5, ['fill' => '#ef4444']);          // destination
$svg->circle($mid->x,   $mid->y,   4, ['fill' => 'none',
                                       'stroke' => '#f59e0b',
                                       'stroke-width' => '1.5']);      // checkpoint
```

## Distribute by content, not distance

Sometimes you have N things to lay out (waypoints, frames, labels) and the spacing should adapt. Use the count-based loop and pull labels from a parallel array:

```php
<?php

$labels = ['Start', 'A', 'B', 'C', 'Finish'];
$count  = count($labels) - 1;

foreach ($labels as $i => $label) {
    $p = $trail->getPointAtLength($length * $i / $count);
    $svg->circle($p->x, $p->y, 3, ['fill' => '#3b82f6']);
    $svg->text($p->x, $p->y - 8, $label, [
        'text-anchor' => 'middle',
        'font-size'   => '10',
        'fill'        => '#444',
    ]);
}
```

## Works for any path, not just curves

`Path::parse()` understands the full SVG path mini-language: `M`, `L`, `C`, `Q`, `A`, `Z`, and their relative variants. You can also build a `Path` from primitives:

```php
<?php

$ring   = Path::circle(150, 50, 40);   // closed circle as a path
$square = Path::rectangle(40, 20, 60, 60);
$star   = Path::star(150, 50, 30, 15, 5);

// Same API works on all of them:
$length = $ring->getLength();          // 226.27 - cubic-Bezier approximation of a circle
$top    = $ring->getPointAtLength(0);  // (110, 50) - leftmost point of the ring
```

So you can place petals around a circle, anchor labels on the corners of a star, or seed particles along a polygon outline using the same loop.

## Quick reference

| | Why |
|---|---|
| `Path::parse($d)` | Parse a `d` attribute string into a measurable path |
| `Path::circle($cx, $cy, $r)` and friends | Build measurable paths from primitives without serializing |
| `$path->getLength()` | Total length in user units (same as `viewBox`) |
| `$path->getPointAtLength($l)` | Point on the curve at a given distance - returns `null` past the end |
| Loop `$i = 0; $i <= $count; $i++` | Evenly-spaced markers including both endpoints |
| Loop `$l = 0; $l <= $length; $l += $step` | Fixed-distance markers (last one may fall short of the end) |

## See also

- [Path overview](../path/overview.md) - full Path API
- [Animate shapes](animate-shapes.md) - use measured points as animation frames
- [Build charts](build-charts.md) - distribute data points along axes
