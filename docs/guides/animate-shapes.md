---
order: 50
description: "Morph between two SVG shapes and export self-contained animated SVGs with configurable easing, frame count, and timing."
---
# Animate Shapes

Morph between two SVG shapes and export as a self-contained animated SVG.

## From two SVG files

```php
use Atelier\Svg\Svg;
use Atelier\Svg\Path\Path;
use Atelier\Svg\Morphing\Morph;
use Atelier\Svg\Morphing\AnimationExporter;

$star = Svg::load('star.svg')->getDocument()->querySelector('path');
$circle = Svg::load('circle.svg')->getDocument()->querySelector('path');

$frames = Morph::frames(
    Path::parse($star->getAttribute('d'))->getData(),
    Path::parse($circle->getAttribute('d'))->getData(),
    60,
    'ease-in-out',
);

$animated = AnimationExporter::toAnimatedSVG($frames, [
    'duration' => 2,
    'repeatCount' => 'indefinite',
]);
```

## From path strings

```php
use Atelier\Svg\Path\Data;
use Atelier\Svg\Morphing\Morph;

$start = Data::parse('M 0 0 L 100 0 L 100 100 L 0 100 Z');
$end = Data::parse('M 50 0 L 100 50 L 50 100 L 0 50 Z');

// Single interpolated frame
$mid = Morph::between($start, $end, 0.5);

// All frames for animation
$frames = Morph::frames($start, $end, 60, 'ease-in-out');
```

## Export formats

```php
use Atelier\Svg\Morphing\AnimationExporter;

// Self-contained SVG with SMIL animation
$doc = AnimationExporter::toAnimatedSVG($frames, ['duration' => 3]);

// CSS @keyframes
$css = AnimationExporter::toCSSKeyframes($frames, 'my-morph');

// JavaScript array for manual animation
$js = AnimationExporter::toJavaScript($frames, 'morphFrames');

// Web Animations API
$waapi = AnimationExporter::toWebAnimationsAPI($frames, ['duration' => 2]);

// JSON for interchange
$json = AnimationExporter::toJSON($frames, ['name' => 'star-to-circle']);

// Individual SVG files for sprite sheets
AnimationExporter::toSpriteSheet($frames, 'output/frames/');
```

## Using the builder

```php
$frames = Morph::create()
    ->from($start)
    ->to($end)
    ->withDuration(2000, 60)  // 2s at 60fps
    ->withEasing('ease-in-out')
    ->generate();
```

## See also

- [Morphing overview](../morphing/overview.md): easing functions, interpolation details
- [Animation elements](../elements/animation.md): SMIL animation builder
- [Animation export](../morphing/exporting.md): export format reference
