---
order: 20
description: "Optimize an entire directory of SVG files in a build step or CI pipeline using configurable optimization passes."
---
# Batch Optimize

Optimize an entire directory of SVGs in a CI pipeline or build step.

```php
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

$optimizer = new Optimizer(OptimizerPresets::aggressive());
$loader = new DomLoader();
$dumper = new CompactXmlDumper();

foreach (glob('assets/svg/*.svg') as $file) {
    $document = $loader->loadFromFile($file);
    $optimizer->optimize($document);
    $dumper->dumpToFile($document, $file);
}
```

## Choosing a preset

| Preset | Use when |
|--------|----------|
| `OptimizerPresets::default()` | General use: safe, good compression |
| `OptimizerPresets::aggressive()` | Maximum file size reduction, CI pipelines |
| `OptimizerPresets::safe()` | When you need to preserve structure exactly |
| `OptimizerPresets::accessible()` | Keeps `<title>`, `<desc>`, and ARIA attributes |

## Measuring results

```php
$before = strlen($dumper->dump($document));
$optimizer->optimize($document);
$after = strlen($dumper->dump($document));

printf("%.1f%% reduction\n", (1 - $after / $before) * 100);
```

## See also

- [Optimization overview](../optimization/overview.md): all passes and presets
- [Custom passes](../optimization/custom-pass.md): write your own optimization pass
