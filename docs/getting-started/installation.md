---
order: 10
---
# Installation

## Requirements

- PHP 8.3 or higher
- DOM extension (`ext-dom`)
- libxml extension (`ext-libxml`)

Both extensions are included in most PHP installations.

## Install with Composer

```bash
composer require atelier/svg
```

## Verify

```php
<?php

require_once 'vendor/autoload.php';

use Atelier\Svg\Svg;

$svg = Svg::create(200, 100)
    ->rect(0, 0, 200, 100, ['fill' => '#3b82f6'])
    ->circle(100, 50, 30, ['fill' => '#fff']);

echo $svg->toString();
```

If this prints SVG markup, the installation is working.

## Next steps

- [Quick Start](quick-start.md): create, load, and manipulate SVGs
- [Elements overview](../elements/overview.md): the element class hierarchy
- [Optimization](../optimization/overview.md): reduce SVG file size
