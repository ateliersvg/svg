---
order: 60
---
# Clipping

Clipping and masking restrict the visible region of elements.
Clipping uses geometry (hard edges), masking uses luminance or alpha
(soft edges).

## ClipPathElement

Defines a clipping region. Child shapes determine which areas are visible.

```php
use Atelier\Svg\Element\Clipping\ClipPathElement;
use Atelier\Svg\Element\Shape\CircleElement;

$clipPath = new ClipPathElement();
$clipPath->setId('circle-clip');
$clipPath->setClipPathUnits('userSpaceOnUse'); // or 'objectBoundingBox'

$circle = CircleElement::create(50, 50, 40);
$clipPath->appendChild($circle);
```

Apply a clip path to an element:

```php
$rect->setClipPath('circle-clip');
```

### clipPathUnits

- `userSpaceOnUse` (default): coordinates are in the current user
  coordinate system.
- `objectBoundingBox`: coordinates are fractions of the target
  element's bounding box (0 to 1).

## MaskElement

Defines a mask using luminance or alpha values. White areas are fully
visible, black areas are fully hidden, gray values produce transparency.

```php
use Atelier\Svg\Element\Clipping\MaskElement;
use Atelier\Svg\Element\Shape\RectElement;

$mask = new MaskElement();
$mask->setId('fade-mask');
$mask->setBounds(0, 0, 200, 200);  // shorthand for x, y, width, height
$mask->setMaskUnits('userSpaceOnUse');
$mask->setMaskContentUnits('userSpaceOnUse');

$fadeRect = RectElement::create(0, 0, 200, 200)
    ->setFill('url(#fade-gradient)');
$mask->appendChild($fadeRect);
```

Individual setters:

```php
$mask->setX(0)->setY(0);
$mask->setWidth(200)->setHeight(200);
```

Apply a mask to an element:

```php
$image->setMask('fade-mask');
```

### Unit Systems

`maskUnits` controls the coordinate system for `x`, `y`, `width`,
`height` on the mask element itself.

`maskContentUnits` controls the coordinate system for the mask's child
elements.

Both accept `userSpaceOnUse` or `objectBoundingBox`.

## Typical Usage

Place clip paths and masks inside `<defs>`:

```php
use Atelier\Svg\Element\Structural\DefsElement;

$defs = new DefsElement();
$defs->appendChild($clipPath);
$defs->appendChild($mask);
$svgRoot->prependChild($defs);
```

## See also

- [Overview](overview.md): element base classes
- [Shapes](shapes.md): shapes used inside clip paths
- [Gradients](gradients.md): gradients for mask luminance
- [Filters](filters.md): applying filter effects
