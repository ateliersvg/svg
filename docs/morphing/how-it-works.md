---
order: 10
---
# How It Works

Morphing two SVG paths requires three stages: normalization, segment matching, and interpolation. Each stage is handled by a dedicated class.

```
Path A ──> PathNormalizer ──> PathMatcher ──> MorphingInterpolator ──> Result
Path B ──> PathNormalizer ──/                                          (Data)
```

## Stage 1: PathNormalizer

`Atelier\Svg\Morphing\PathNormalizer` converts any SVG path into a canonical form using only three command types:

- **M** (MoveTo)
- **C** (Cubic Bezier CurveTo)
- **Z** (ClosePath)

### What it does

1. **Relative to absolute**: All relative commands (lowercase letters) become absolute coordinates.
2. **Shorthand expansion**: `S` (smooth curve) is expanded to `C` by reflecting the previous control point. `T` (smooth quadratic) is similarly expanded.
3. **Quadratic to cubic**: `Q` commands are converted to `C` using the standard 2/3 control point formula.
4. **Lines to curves**: `L`, `H`, and `V` commands become cubic beziers with control points placed at 1/3 and 2/3 along the line.
5. **Arcs to curves**: `A` commands are approximated as cubic bezier curves.

After normalization, every drawing segment is a cubic bezier curve. This makes interpolation straightforward: just lerp the control points.

```php
use Atelier\Svg\Morphing\PathNormalizer;
use Atelier\Svg\Path\Data;

$normalizer = new PathNormalizer();
$path = Data::parse('M 0 0 L 100 0 Q 100 100 0 100 Z');
$normalized = $normalizer->normalize($path);
// Result contains only M, C, and Z segments
```

## Stage 2: PathMatcher

`Atelier\Svg\Morphing\PathMatcher` ensures both paths have the same number and type of segments. Without this, interpolation would fail.

### Matching strategy

When one path has fewer segments than the other, PathMatcher subdivides the shorter path's cubic bezier curves using **De Casteljau's algorithm**:

1. Count segments in both paths.
2. If counts match, return both paths unchanged.
3. Otherwise, identify cubic bezier curves in the shorter path.
4. Distribute subdivisions evenly across those curves.
5. Split each selected curve into smaller subcurves that together trace the same shape.

The subdivision preserves the original geometry: splitting a bezier at parameter `t` produces two bezier curves that together are identical to the original.

```php
use Atelier\Svg\Morphing\PathMatcher;

$matcher = new PathMatcher();
[$matchedA, $matchedB] = $matcher->match($normalizedA, $normalizedB);
// Both now have the same segment count
```

### De Casteljau subdivision

To split a cubic bezier defined by points P0, P1, P2, P3 at parameter `t`:

1. Compute midpoints: P01 = lerp(P0, P1, t), P12 = lerp(P1, P2, t), P23 = lerp(P2, P3, t)
2. Compute second-level midpoints: P012 = lerp(P01, P12, t), P123 = lerp(P12, P23, t)
3. Compute split point: P0123 = lerp(P012, P123, t)
4. Left curve: (P0, P01, P012, P0123)
5. Right curve: (P0123, P123, P23, P3)

## Stage 3: MorphingInterpolator

`Atelier\Svg\Morphing\MorphingInterpolator` performs the actual interpolation between matched paths.

### Interpolation

For each pair of corresponding segments, the interpolator linearly interpolates (lerp) all coordinate values:

- MoveTo: interpolate the target point
- CurveTo: interpolate both control points and the target point
- ClosePath: pass through unchanged

The parameter `t` ranges from 0.0 (start path) to 1.0 (end path). An optional easing function transforms `t` before interpolation.

```php
use Atelier\Svg\Morphing\MorphingInterpolator;

$interpolator = new MorphingInterpolator();
$result = $interpolator->interpolate($matchedA, $matchedB, 0.5);
```

### Frame generation

To generate multiple frames at once:

```php
$frames = $interpolator->generateFrames($matchedA, $matchedB, 60);
// Returns 60 Data objects evenly spaced from t=0 to t=1
```

### Custom easing

Beyond the named presets, you can create a CSS-style cubic bezier easing:

```php
$easing = MorphingInterpolator::cubicBezierEasing(0.42, 0.0, 0.58, 1.0);
$result = $interpolator->interpolate($matchedA, $matchedB, 0.5, $easing);
```

---

## Constraints and edge cases

### Compatible paths

Two paths are compatible if, after normalization, the PathMatcher can bring them to the same segment count. This is always possible when both paths consist of a single subpath (one `M` command).

### Multiple subpaths

A path with multiple `M` commands creates multiple subpaths. The matcher treats each normalized segment independently, so a path like `M 0 0 L 50 0 M 100 0 L 150 0`, which produces two subpaths, will have a different segment structure than a single-subpath path of similar total length. Morphing across different subpath counts produces unexpected visual results.

### Open vs. closed paths

A path ending in `Z` and one that does not will both normalize to `M`/`C` segments. The matcher can equalize their curve counts, but the `Z` segment is passed through unchanged. Morphing an open path against a closed path will result in the closing segment appearing or disappearing abruptly rather than animating.

### What the interpolator rejects

`MorphingInterpolator::interpolate()` throws `InvalidArgumentException` in two cases:

1. The paths have different segment counts, call `PathMatcher::match()` first.
2. Corresponding segments are different types, this should not happen after normalization, but if you pass raw (non-normalized) paths directly to the interpolator, it can occur.

### Working example

```php
<?php

use Atelier\Svg\Path\Data;
use Atelier\Svg\Morphing\ShapeMorpher;

$morpher = new ShapeMorpher();

// Both are single-subpath shapes, compatible
$triangle = Data::parse('M 50 10 L 90 90 L 10 90 Z');
$square   = Data::parse('M 10 10 L 90 10 L 90 90 L 10 90 Z');

$mid = $morpher->morph($triangle, $square, 0.5);
// The matcher subdivides the triangle's three curves to match
// the square's four, then interpolates coordinate by coordinate.
```

### Incompatible case to avoid

```php
<?php

use Atelier\Svg\Path\Data;
use Atelier\Svg\Morphing\ShapeMorpher;

$morpher = new ShapeMorpher();

// Two separate subpaths vs. one subpath
$twoSubpaths = Data::parse('M 0 0 L 40 0 M 60 0 L 100 0');
$onePath     = Data::parse('M 0 50 L 100 50');

// The matcher equalizes segment counts, but the two M commands in
// $twoSubpaths mean the visual result jumps rather than morphs
// smoothly. Flatten multi-subpath shapes into one before morphing.
$mid = $morpher->morph($twoSubpaths, $onePath, 0.5);
```

---

See also:

- [Overview](overview.md): the Morph facade and basic usage
- [Exporting](exporting.md): export animations to various formats
