# Morphing

Morph between two SVG paths to create smooth shape transitions. The morphing system handles path normalization, segment matching, and interpolation automatically.

## Quick Start

```php
use Atelier\Svg\Morphing\Morph;
use Atelier\Svg\Path\Data;

$start = Data::parse('M 0 0 L 100 0 L 100 100 L 0 100 Z');
$end   = Data::parse('M 50 0 L 100 50 L 50 100 L 0 50 Z');

// Get a single interpolated path at 50%
$mid = Morph::between($start, $end, 0.5);
```

## The Morph Facade

`Atelier\Svg\Morphing\Morph` provides three static entry points.

### Interpolate at a Point

```php
$path = Morph::between($start, $end, 0.3, 'ease-in-out');
```

The `$t` parameter ranges from `0.0` (start shape) to `1.0` (end shape). An optional easing function controls the interpolation curve.

### Generate Animation Frames

```php
$frames = Morph::frames($start, $end, 60, 'ease-in-out');
// Returns an array of 60 Data objects
```

### Builder Pattern

For more control, use the fluent builder via `Morph::create()`:

```php
$frames = Morph::create()
    ->from($start)
    ->to($end)
    ->withDuration(2000, 60)   // 2 seconds at 60fps = 120 frames
    ->withEasing('ease-out-cubic')
    ->generate();
```

The builder returns a `MorphingBuilder` instance with these methods:

| Method | Description |
|--------|-------------|
| `from(Data $path)` | Set the starting path |
| `to(Data $path)` | Set the ending path |
| `withFrames(int $count)` | Set the number of frames directly |
| `withDuration(int $ms, int $fps)` | Calculate frame count from duration and framerate |
| `withEasing(string\|Closure $easing)` | Set the easing function |
| `generate()` | Generate all frames as `Data[]` |
| `at(float $t)` | Get a single interpolated frame |

## ShapeMorpher

`Morph` delegates to `ShapeMorpher`, which orchestrates the three-stage pipeline (normalize, match, interpolate). You can use it directly for lower-level access:

```php
use Atelier\Svg\Morphing\ShapeMorpher;

$morpher = new ShapeMorpher();
$result  = $morpher->morph($start, $end, 0.5, 'ease-in');

// Inspect intermediate steps
$normalized = $morpher->normalize($start);
[$matchedA, $matchedB] = $morpher->match($start, $end);
```

## Easing Functions

The following named easing functions are available:

| Name | Effect |
|------|--------|
| `linear` | Constant speed |
| `ease-in` | Slow start (quadratic) |
| `ease-out` | Slow end (quadratic) |
| `ease-in-out` | Slow start and end (quadratic) |
| `ease-in-cubic` | Slow start (cubic) |
| `ease-out-cubic` | Slow end (cubic) |
| `ease-in-out-cubic` | Slow start and end (cubic) |
| `ease-out-elastic` | Bouncy overshoot |
| `ease-in-back` | Pulls back before moving forward |
| `ease-out-back` | Overshoots then settles |

You can also pass a custom `Closure` as the easing function:

```php
$frames = Morph::create()
    ->from($start)
    ->to($end)
    ->withFrames(60)
    ->withEasing(fn(float $t): float => $t * $t * $t)
    ->generate();
```

Or use `MorphingInterpolator::cubicBezierEasing()` to create a CSS-style cubic bezier:

```php
use Atelier\Svg\Morphing\MorphingInterpolator;

$easing = MorphingInterpolator::cubicBezierEasing(0.25, 0.1, 0.25, 1.0);
$path = Morph::between($start, $end, 0.5, $easing);
```

---

See also:

- [How It Works](how-it-works.md): the normalization, matching, and interpolation pipeline
- [Exporting](exporting.md): export animations to SMIL, CSS, JavaScript, and more
- [Animation elements](../elements/animation.md): SMIL animation elements and builder
