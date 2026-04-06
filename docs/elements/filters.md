---
order: 50
---
# Filters

SVG filters apply graphical effects (blur, shadows, color shifts) to
elements. A `<filter>` element contains one or more filter primitives
that are chained together.

## How Filters Work

1. Define a `<filter>` element with primitives inside `<defs>`.
2. Reference it from any element with `filter="url(#id)"`.
3. Primitives use `in` to read input and `result` to name output.
4. Special inputs: `SourceGraphic`, `SourceAlpha`, `BackgroundImage`.

## FilterElement

The container for filter primitives.

```php
use Atelier\Svg\Element\Filter\FilterElement;

$filter = new FilterElement();
$filter->setId('my-filter');
$filter->setX('-10%')->setY('-10%');
$filter->setWidth('120%')->setHeight('120%');
$filter->setFilterUnits('objectBoundingBox');
$filter->setPrimitiveUnits('userSpaceOnUse');
```

## FilterBuilder

The `FilterBuilder` provides a fluent API for constructing filters and
adding them to the document's `<defs>`.

### Fluent API

```php
use Atelier\Svg\Element\Builder\FilterBuilder;

$filter = FilterBuilder::create($doc, 'custom-effect')
    ->gaussianBlur(3, 'SourceAlpha', 'blur')
    ->offset(2, 2, 'blur', 'shifted')
    ->flood('#000000', 0.3, 'color')
    ->composite('in', 'color', 'shifted', 'shadow')
    ->blend('normal', 'SourceGraphic', 'shadow')
    ->addToDefs()
    ->getFilter();
```

Available primitive methods on the builder:

| Method         | Primitive          | Key parameters               |
|----------------|--------------------|------------------------------|
| `gaussianBlur` | `feGaussianBlur`   | stdDeviation, in, result     |
| `offset`       | `feOffset`         | dx, dy, in, result           |
| `colorMatrix`  | `feColorMatrix`    | type, values, in, result     |
| `blend`        | `feBlend`          | mode, in, in2, result        |
| `composite`    | `feComposite`      | operator, in, in2, result    |
| `flood`        | `feFlood`          | color, opacity, result       |

### Shortcut Methods

Common effects as one-liners:

```php
// Simple blur
FilterBuilder::createBlur($doc, 'blur-5', 5);

// Drop shadow (dx, dy, blur, color, opacity)
FilterBuilder::createDropShadow($doc, 'shadow', 2, 2, 4, '#000', 0.3);

// Glow effect (color, strength, opacity)
FilterBuilder::createGlow($doc, 'glow', '#3b82f6', 2, 0.8);

// Desaturate / grayscale (0 = none, 1 = full grayscale)
FilterBuilder::createDesaturate($doc, 'gray', 1.0);
```

All shortcuts add the filter to `<defs>` automatically.

### Applying a Filter

```php
$rect->applyFilter('my-filter');
// equivalent to:
$rect->setAttribute('filter', 'url(#my-filter)');

// Remove
$rect->removeFilter();

// Check
$rect->getFilterId(); // 'my-filter' or null
```

## Filter Primitives

All filter primitives extend `AbstractFilterPrimitiveElement`, which
provides shared methods: `setIn`, `setResult`, `setX`, `setY`,
`setWidth`, `setHeight`.

```php
// Common to all primitives:
$primitive->setIn('SourceGraphic');
$primitive->setResult('output1');
```

### FeGaussianBlurElement

Applies a Gaussian blur.

```php
use Atelier\Svg\Element\Filter\FeGaussianBlurElement;

$blur = new FeGaussianBlurElement();
$blur->setStdDeviation(5);       // single value or "x y" pair
$blur->setEdgeMode('duplicate');  // 'duplicate', 'wrap', 'none'
```

### FeOffsetElement

Offsets the input image.

```php
use Atelier\Svg\Element\Filter\FeOffsetElement;

$offset = new FeOffsetElement();
$offset->setDx(5);
$offset->setDy(5);
```

### FeBlendElement

Blends two input images.

```php
use Atelier\Svg\Element\Filter\FeBlendElement;

$blend = new FeBlendElement();
$blend->setMode('multiply');  // 'normal', 'multiply', 'screen', 'darken', 'lighten'
$blend->setIn('SourceGraphic');
$blend->setIn2('BackgroundImage');
```

### FeColorMatrixElement

Applies a color transformation matrix.

```php
use Atelier\Svg\Element\Filter\FeColorMatrixElement;

$cm = new FeColorMatrixElement();
$cm->setType('saturate');    // 'matrix', 'saturate', 'hueRotate', 'luminanceToAlpha'
$cm->setValues('0.5');
```

### FeCompositeElement

Combines images using Porter-Duff compositing.

```php
use Atelier\Svg\Element\Filter\FeCompositeElement;

$comp = new FeCompositeElement();
$comp->setOperator('in');  // 'over', 'in', 'out', 'atop', 'xor', 'arithmetic'
$comp->setIn('SourceGraphic');
$comp->setIn2('BackgroundImage');
```

### FeFloodElement

Fills the filter region with a solid color.

```php
use Atelier\Svg\Element\Filter\FeFloodElement;

$flood = new FeFloodElement();
$flood->setFloodColor('#ff0000');
$flood->setFloodOpacity(0.5);
```

### FeMergeElement / FeMergeNodeElement

Composites multiple layers.

```php
use Atelier\Svg\Element\Filter\FeMergeElement;
use Atelier\Svg\Element\Filter\FeMergeNodeElement;

$merge = new FeMergeElement();
$node1 = new FeMergeNodeElement();
$node1->setIn('blur');
$node2 = new FeMergeNodeElement();
$node2->setIn('SourceGraphic');
$merge->appendChild($node1);
$merge->appendChild($node2);
```

### FeDisplacementMapElement

Displaces pixels using values from a second image.

```php
use Atelier\Svg\Element\Filter\FeDisplacementMapElement;

$displace = new FeDisplacementMapElement();
$displace->setIn('SourceGraphic');
$displace->setIn2('displacement');
```

### FeMorphologyElement

Erodes or dilates the input.

```php
use Atelier\Svg\Element\Filter\FeMorphologyElement;

$morph = new FeMorphologyElement();
$morph->setOperator('dilate');  // 'erode' or 'dilate'
```

### FeTurbulenceElement

Generates Perlin turbulence or fractal noise.

```php
use Atelier\Svg\Element\Filter\FeTurbulenceElement;

$turb = new FeTurbulenceElement();
// Attributes set via setAttribute for type, baseFrequency, numOctaves, seed
```

### FeImageElement

Fetches an external image for use in the filter.

```php
use Atelier\Svg\Element\Filter\FeImageElement;

$img = new FeImageElement();
$img->setAttribute('href', 'texture.png');
```

### FeTileElement

Tiles the input to fill the filter region.

```php
use Atelier\Svg\Element\Filter\FeTileElement;

$tile = new FeTileElement();
$tile->setIn('pattern');
```

### FeConvolveMatrixElement

Applies a convolution matrix for effects like sharpening or embossing.

```php
use Atelier\Svg\Element\Filter\FeConvolveMatrixElement;

$conv = new FeConvolveMatrixElement();
// Set kernelMatrix, order, etc. via setAttribute
```

### Lighting Primitives

Simulate lighting with light sources:

- `FeDiffuseLightingElement`: diffuse lighting
- `FeSpecularLightingElement`: specular lighting

Light sources (placed inside lighting elements):

- `FeDistantLightElement`: distant (directional) light
- `FePointLightElement`: point light
- `FeSpotLightElement`: spot light

### FeComponentTransferElement

Per-channel transfer functions using child elements:

- `FeFuncRElement`: red channel
- `FeFuncGElement`: green channel
- `FeFuncBElement`: blue channel
- `FeFuncAElement`: alpha channel

## See also

- [Shapes](shapes.md): elements to apply filters to
- [Overview](overview.md): element base classes
