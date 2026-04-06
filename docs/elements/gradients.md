---
order: 40
---
# Gradients

SVG gradients define smooth color transitions that can fill or stroke
any element. Atelier SVG supports both linear and radial gradients.

## LinearGradientElement

A linear gradient transitions colors along a line defined by start and
end points.

```php
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;

$gradient = new LinearGradientElement();
$gradient->setId('grad1');
$gradient->setDirection(0, 0, 100, 0);  // left to right
$gradient->setGradientUnits('objectBoundingBox');
$gradient->setSpreadMethod('pad');       // 'pad', 'reflect', 'repeat'

$stop1 = new StopElement();
$stop1->setOffset(0)->setStopColor('#3b82f6');

$stop2 = new StopElement();
$stop2->setOffset(1)->setStopColor('#8b5cf6')->setStopOpacity(0.8);

$gradient->appendChild($stop1);
$gradient->appendChild($stop2);
```

Individual coordinate setters:

```php
$gradient->setX1('0%')->setY1('0%');
$gradient->setX2('100%')->setY2('0%');
$gradient->setGradientTransform('rotate(45)');
```

## RadialGradientElement

A radial gradient transitions colors outward from a center point in a
circular pattern. An optional focal point controls where the gradient
appears brightest.

```php
use Atelier\Svg\Element\Gradient\RadialGradientElement;
use Atelier\Svg\Element\Gradient\StopElement;

$gradient = new RadialGradientElement();
$gradient->setId('radial1');
$gradient->setCenter(50, 50);      // shorthand for setCx + setCy
$gradient->setR(50);
$gradient->setFocalPoint(30, 30);  // shorthand for setFx + setFy
$gradient->setFr(5);               // focal radius (SVG 2)
$gradient->setGradientUnits('objectBoundingBox');
$gradient->setSpreadMethod('pad');

$stop1 = new StopElement();
$stop1->setOffset(0)->setStopColor('#ffffff');
$gradient->appendChild($stop1);

$stop2 = new StopElement();
$stop2->setOffset(1)->setStopColor('#3b82f6');
$gradient->appendChild($stop2);
```

Individual coordinate methods:

```php
$gradient->setCx('50%')->setCy('50%');
$gradient->setFx('25%')->setFy('25%');
$gradient->setGradientTransform('scale(1.2)');
```

### Focal Point

The focal point (`fx`, `fy`) shifts the apparent center of the gradient.
When the focal point differs from the center, the gradient appears
off-center, creating a highlight or spotlight effect.

```php
$gradient->setCenter(50, 50);
$gradient->setFocalPoint(30, 30);  // highlight shifted upper-left
```

If omitted, the focal point defaults to the center.

## StopElement

Defines a color position within the gradient.

```php
use Atelier\Svg\Element\Gradient\StopElement;

$stop = new StopElement();
$stop->setOffset('50%');        // 0-1 or percentage
$stop->setStopColor('#10b981');
$stop->setStopOpacity(0.5);

$stop->getOffset();       // string|null
$stop->getStopColor();    // string|null
$stop->getStopOpacity();  // string|null
```

## GradientBuilder

The `GradientBuilder` provides a fluent API for creating gradients and
automatically placing them in the document's `<defs>` section.

### Linear Gradients

```php
use Atelier\Svg\Element\Builder\GradientBuilder;

$gradient = GradientBuilder::createLinear($doc, 'my-gradient')
    ->from(0, 0)
    ->to(100, 0)
    ->units('objectBoundingBox')
    ->addStop(0, '#3b82f6')
    ->addStop(50, '#8b5cf6', 0.8)   // offset, color, opacity
    ->addStop(100, '#ec4899')
    ->spreadMethod('pad')
    ->transform('rotate(45)')
    ->addToDefs()
    ->getGradient();
```

### Radial Gradients

```php
$gradient = GradientBuilder::createRadial($doc, 'r-grad')
    ->center(50, 50)
    ->radius(50)
    ->focal(30, 30)
    ->units('objectBoundingBox')
    ->addStop(0, '#ffffff')
    ->addStop(100, '#3b82f6')
    ->addToDefs()
    ->getGradient();
```

### Shortcut Methods

Create common gradient patterns in one call:

```php
// Horizontal (left to right)
GradientBuilder::horizontal($doc, 'h-grad', '#3b82f6', '#8b5cf6');

// Vertical (top to bottom)
GradientBuilder::vertical($doc, 'v-grad', '#3b82f6', '#8b5cf6');

// Diagonal (top-left to bottom-right)
GradientBuilder::diagonal($doc, 'd-grad', '#3b82f6', '#8b5cf6');

// Centered radial gradient
GradientBuilder::radial($doc, 'glow', '#ffffff', '#3b82f6');
```

All shortcut methods add the gradient to `<defs>` automatically.

### Applying a Gradient

```php
$rect->setFill('url(#my-gradient)');
// or
$rect->setFillPaintServer('my-gradient');
```

## See also

- [Shapes](shapes.md): elements to apply gradients to
- [Overview](overview.md): element base classes
