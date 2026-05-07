# Optimization

The optimization pipeline reduces SVG file size by applying a sequence of passes that clean, convert, merge, and simplify SVG elements. Each pass implements a single optimization strategy, and the order of passes matters.

## Quick start

```php
use Atelier\Svg\Svg;

// Use the default preset via the Svg facade
$svg = Svg::load('icon.svg')->optimize()->save('icon.min.svg');

// Or pick a preset explicitly
$svg = Svg::load('icon.svg')->optimizeAggressive()->save('icon.min.svg');
$svg = Svg::load('icon.svg')->optimizeSafe()->save('icon.min.svg');
$svg = Svg::load('icon.svg')->optimizeWeb()->save('icon.min.svg');
```

## The Optimizer class

`Atelier\Svg\Optimizer\Optimizer` orchestrates a pipeline of `OptimizerPassInterface` instances. Passes run in order; the output of one becomes the input of the next.

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\RemoveEmptyElementsPass;
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;

$optimizer = new Optimizer([
    new RemoveEmptyElementsPass(),
    new MergePathsPass(),
    new RoundValuesPass(2),
]);

$document = $optimizer->optimize($document);
```

You can also build the pipeline incrementally:

```php
$optimizer = new Optimizer();
$optimizer->addPass(new RemoveEmptyElementsPass());
$optimizer->addPass(new RoundValuesPass(2));
$optimizer->optimize($document);
```

The `Optimizer` also exposes static convenience methods for common operations:

| Method | Description |
|---|---|
| `Optimizer::forDocument($doc, 'default')` | Apply a named preset |
| `Optimizer::removeMetadata($doc)` | Strip comments, metadata, desc, title |
| `Optimizer::cleanupDefs($doc)` | Remove unused and duplicate defs |
| `Optimizer::roundValues($doc, 2)` | Round numeric values |
| `Optimizer::optimizeColors($doc)` | Convert colors to shortest form |
| `Optimizer::simplifyPaths($doc, 0.5)` | Simplify path data |
| `Optimizer::removeHidden($doc)` | Remove hidden and empty elements |
| `Optimizer::mergePaths($doc)` | Merge consecutive paths |
| `Optimizer::collapseGroups($doc)` | Collapse unnecessary groups |
| `Optimizer::cleanupIds($doc, false)` | Remove unused IDs |
| `Optimizer::removeDefaults($doc)` | Remove default attribute values |

## Presets

`Atelier\Svg\Optimizer\OptimizerPresets` provides four preconfigured pipelines, forming a clear gradient: `safe < default < web < aggressive`.

### `safe`: `OptimizerPresets::safe()`

Conservative. Preserves metadata, IDs, titles, and descriptions. Very low simplification tolerance (0.1). For version-controlled assets, design tools, and scripted SVGs.

### `default`: `OptimizerPresets::default()`

Balanced optimization. Removes metadata and descriptions but keeps titles. Readable IDs. The recommended starting point.

### `web`: `OptimizerPresets::web()`

Production delivery. Strips titles, descriptions, and dimensions. Converts shapes to paths, merges paths, minifies IDs. For `<img>`, inline SVG, and icon systems.

### `aggressive`: `OptimizerPresets::aggressive()`

Maximum reduction. Integer-only coordinates (precision 0), lossy simplification. For build pipelines where every byte counts.

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

$passes = OptimizerPresets::get('aggressive');
$optimizer = new Optimizer($passes);
$optimizer->optimize($document);
```

## PrecisionConfig

`Atelier\Svg\Optimizer\PrecisionConfig` centralizes numeric precision constants used across passes. Different contexts tolerate different precision levels:

| Context | Safe | Default | Web | Aggressive | Rationale |
|---|---|---|---|---|---|
| Coordinates | 3 | 2 | 1 | 0 | 0.01px is imperceptible at normal scales |
| Transforms | 4 | 3 | 2 | 2 | Errors compound through transform chains |
| Paths | 4 | 3 | 2 | 0 | Curves are sensitive to control point precision |
| Opacity | 3 | 2 | 2 | 2 | 0.01 steps sufficient for the 0-1 range |
| Cleanup | 4 | 3 | 2 | 0 | Safety margin above rounding precision |
| Angles | 2 | 1 | 1 | 1 | 0.1 degrees is rarely perceptible |

The `web` preset uses the same precision values as `aggressive` but preserves decimal
coordinates (precision 1-2), while `aggressive` rounds everything to integers (precision 0).

```php
use Atelier\Svg\Optimizer\PrecisionConfig;

// Get all precisions for a preset
$config = PrecisionConfig::forPreset('default');
// ['coordinate' => 2, 'dimension' => 2, 'transform' => 3, ...]

// Use constants directly
$pass = new RoundValuesPass(
    precision: PrecisionConfig::COORDINATE_DEFAULT,
    transformPrecision: PrecisionConfig::TRANSFORM_DEFAULT,
    pathPrecision: PrecisionConfig::PATH_DEFAULT,
);
```

## Analyzer

`Atelier\Svg\Optimizer\Analyzer` generates reports on document structure, size, styles, and optimization opportunities.

```php
use Atelier\Svg\Optimizer\Analyzer;

$report = Analyzer::analyze($document);
// $report['size']['bytes'], $report['size']['formatted'], $report['size']['compressed']
// $report['structure']['total_elements'], $report['structure']['max_depth']
// $report['styles']['inline_styles'], $report['styles']['unique_color_count']
// $report['optimization']['opportunities']

// Or print a formatted report
echo Analyzer::printReport($document);
```

Individual analysis methods are also available: `analyzeSize()`, `analyzeStructure()`, `analyzeStyles()`, `analyzeOptimization()`.

## See also

- [Cleanup passes](passes/cleanup.md)
- [Conversion passes](passes/convert.md)
- [Removal passes](passes/remove.md)
- [Merge and restructure passes](passes/merge.md)
- [Writing a custom pass](custom-pass.md)
