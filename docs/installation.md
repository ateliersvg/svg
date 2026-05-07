---
order: 10
title: Installation
description: PHP 8.3+, two standard extensions, one Composer command.
---

# Installation

## Requirements

PHP 8.3 or higher. No Node, no build step, no external binaries.

The `ext-dom` and `ext-xml` extensions are required; both ship with PHP and are enabled by default in all standard distributions.

## Install

```bash
composer require atelier/svg
```

## Confirm it works

```php
<?php

use Atelier\Svg\Svg;

$svg = Svg::create(200, 100)
    ->rect(0, 0, 200, 100, ['fill' => '#0f172a'])
    ->circle(100, 50, 30, ['fill' => '#6366f1']);

echo $svg; // prints compact SVG markup
```

If SVG markup appears in the output, you're good. Next: [Quick Start](/quick-start/).
