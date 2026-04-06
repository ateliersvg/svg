---
order: 10
---
# Values

Atelier SVG provides typed value objects for the data types used in SVG attributes and CSS properties. All are readonly, implement `Stringable`, and provide static `parse()` factory methods.

## Color

`Atelier\Svg\Value\Color` supports named colors, hex (#RGB, #RRGGBB, #RRGGBBAA), rgb(), rgba(), hsl(), hsla(), and special values (none, transparent, currentColor).

```php
use Atelier\Svg\Value\Color;

$color = Color::parse('#3b82f6');
$color = Color::parse('rgb(59, 130, 246)');
$color = Color::parse('hsl(217, 91%, 60%)');
$color = Color::parse('royalblue');
$color = Color::fromRgb(59, 130, 246, alpha: 0.8);

$color->getRed();       // 0-255
$color->getGreen();     // 0-255
$color->getBlue();      // 0-255
$color->getAlpha();     // 0.0-1.0
$color->isTransparent(); // bool
$color->isOpaque();     // bool

$color->toHex();   // "#3b82f6"
$color->toRgb();   // "rgb(59, 130, 246)"
echo $color;       // prefers named color if matching, otherwise hex
```

## Length

`Atelier\Svg\Value\Length` represents SVG length/coordinate values with optional units: px, em, ex, pt, pc, cm, mm, in, %.

```php
use Atelier\Svg\Value\Length;

$len = Length::parse('10px');
$len = Length::parse('50%');
$len = Length::parse(42);       // unitless (numeric input)

$len->getValue();      // 10.0
$len->getUnit();       // "px" or null for unitless
$len->isUnitless();    // bool
$len->isPercentage();  // bool
echo $len;             // "10px"
```

## Angle

`Atelier\Svg\Value\Angle` stores angles internally in radians and parses deg, rad, grad. Unitless values are treated as degrees per the SVG spec.

```php
use Atelier\Svg\Value\Angle;

$angle = Angle::parse('45deg');
$angle = Angle::parse(45);          // treated as degrees
$angle = Angle::parse('1.57rad');
$angle = Angle::fromDegrees(90);
$angle = Angle::fromRadians(M_PI);

$angle->toDegrees();   // float
$angle->toRadians();   // float
$angle->toGradians();  // float
echo $angle;           // "45" (degrees, unit omitted for SVG)
```

## Viewbox

`Atelier\Svg\Value\Viewbox` represents the SVG `viewBox` attribute: four numbers defining the viewport position and dimensions.

```php
use Atelier\Svg\Value\Viewbox;

$vb = Viewbox::parse('0 0 800 600');
$vb = new Viewbox(minX: 0, minY: 0, width: 800, height: 600);

$vb->getMinX();         // 0
$vb->getWidth();        // 800
$vb->getHeight();       // 600
$vb->getMaxX();         // 800 (minX + width)
$vb->getCenterX();      // 400
$vb->getAspectRatio();  // 1.333...
echo $vb;               // "0 0 800 600"
```

Width and height must be non-negative; the constructor throws `InvalidArgumentException` otherwise.

## DashArray

`Atelier\Svg\Value\DashArray` represents the `stroke-dasharray` attribute: a list of dash and gap lengths.

## PointList

`Atelier\Svg\Value\PointList` represents the `points` attribute used by `<polygon>` and `<polyline>` elements: a list of x,y coordinate pairs.

## PreserveAspectRatio

`Atelier\Svg\Value\PreserveAspectRatio` represents the `preserveAspectRatio` attribute, combining an alignment value (e.g., `xMidYMid`) with a meet-or-slice strategy.

## IriReference

`Atelier\Svg\Value\IriReference` represents IRI/URI references used in attributes like `xlink:href`, `fill="url(#id)"`, and `clip-path="url(#id)"`.

## See also

- [Style system](overview.md): Style, StyleBuilder, themes
- [CSS/SVG transforms](transforms.md): TransformList and transform functions
- [Path data model](../path/overview.md): path-specific value types
