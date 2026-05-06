---
order: 20
---
# Exporting

`Atelier\Svg\Morphing\AnimationExporter` converts morph frames into ready-to-use animation formats. All methods are static and accept an array of `Data` frames.

## SMIL Animated SVG

Generate a self-contained SVG with a `<animate>` element:

```php
use Atelier\Svg\Morphing\AnimationExporter;
use Atelier\Svg\Morphing\Morph;
use Atelier\Svg\Path\Data;

$start  = Data::parse('M 0 0 L 100 0 L 100 100 L 0 100 Z');
$end    = Data::parse('M 50 0 L 100 50 L 50 100 L 0 50 Z');
$frames = Morph::frames($start, $end, 30, 'ease-in-out');

$doc = AnimationExporter::toAnimatedSVG($frames, [
    'viewBox'     => '0 0 100 100',
    'width'       => '200',
    'height'      => '200',
    'duration'    => '2',           // seconds
    'repeatCount' => 'indefinite',
    'fill'        => '#3b82f6',
    'stroke'      => '#1e40af',
    'strokeWidth' => '2',
    'calcMode'    => 'spline',
    'keySplines'  => '0.42 0 0.58 1',
]);
```

Returns a `Document` instance. Use a dumper to get the SVG string.

## CSS Keyframes

```php
$css = AnimationExporter::toCSSKeyframes($frames, 'morph-animation');
```

Generates a `@keyframes` block using the CSS `d: path(...)` property. Each frame maps to a percentage from 0% to 100%. The output includes a commented usage example.

## JavaScript Array

```php
$js = AnimationExporter::toJavaScript($frames, 'morphFrames');
```

Produces a `const morphFrames = [...]` array of path data strings, with a commented `setInterval` usage example for manual frame-by-frame animation.

## Web Animations API

```php
$js = AnimationExporter::toWebAnimationsAPI($frames, [
    'duration'   => 3,             // seconds (converted to ms internally)
    'easing'     => 'ease-in-out',
    'iterations' => 'Infinity',
]);
```

Generates keyframes and options for the Web Animations API (`element.animate()`). The output includes the keyframe array, options object, and a commented usage example.

## JSON

```php
$json = AnimationExporter::toJSON($frames, [
    'name'   => 'square-to-diamond',
    'author' => 'Atelier SVG',
]);
```

Exports a structured JSON document containing:

- `version`: format version (`"1.0"`)
- `frameCount`: number of frames
- `metadata`: the metadata array you provide
- `frames`: array of path data strings

## Sprite Sheet

```php
AnimationExporter::toSpriteSheet($frames, '/path/to/output', [
    'viewBox' => '0 0 100 100',
    'width'   => '200',
    'height'  => '200',
    'fill'    => '#3b82f6',
    'stroke'  => 'none',
]);
```

Writes individual SVG files to the specified directory, named `frame-0000.svg`, `frame-0001.svg`, and so on. Creates the directory if it does not exist.

## Debug Visualization

```php
$doc = AnimationExporter::createDebugVisualization($frames, cols: 5);
```

Generates a grid SVG showing all frames side by side, useful for inspecting the morph progression. Each frame is labeled and rendered in a 200x200 cell. The `$cols` parameter controls how many frames appear per row.

---

See also:

- [Overview](overview.md): the Morph facade and basic usage
- [How It Works](how-it-works.md): the normalization, matching, and interpolation pipeline
- [Animate shapes guide](../guides/animate-shapes.md): practical animation workflows
- [Animation elements](../elements/animation.md): SMIL animation element API
