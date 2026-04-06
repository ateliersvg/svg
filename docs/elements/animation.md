---
order: 70
---
# Animation

Atelier SVG supports SMIL animation through `AnimateElement` and
`AnimateTransformElement`, plus a fluent `AnimationBuilder` for common
animation patterns.

## AnimateElement

Maps to the SVG `<animate>` element. Animates a single attribute over time.

```php
use Atelier\Svg\Element\Animation\AnimateElement;

$animate = new AnimateElement();
$animate->setAttributeName('opacity')
    ->setFrom(0)
    ->setTo(1)
    ->setDur('1s')
    ->setFill('freeze');

$element->appendChild($animate);
```

### Methods

| Method | Description |
|--------|-------------|
| `setAttributeName(string)` | Attribute to animate |
| `setFrom(string\|int\|float)` | Start value |
| `setTo(string\|int\|float)` | End value |
| `setValues(string)` | Semicolon-separated intermediate values |
| `setDur(string)` | Duration (`'1s'`, `'500ms'`) |
| `setRepeatCount(string\|int)` | Number of repetitions or `'indefinite'` |
| `setFill(string)` | `'freeze'` (hold last value) or `'remove'` (revert) |
| `setBegin(string)` | Start trigger (`'0s'`, `'click'`, `'mouseover'`) |
| `setCalcMode(string)` | `'discrete'`, `'linear'`, `'paced'`, or `'spline'` |
| `setAdditive(string)` | `'sum'` (add to base) or `'replace'` |

All setters return `static` for chaining.

## AnimateTransformElement

Maps to `<animateTransform>`. Animates the `transform` attribute.

```php
use Atelier\Svg\Element\Animation\AnimateTransformElement;

$anim = new AnimateTransformElement();
$anim->setType('rotate')
    ->setFrom('0 50 50')
    ->setTo('360 50 50')
    ->setDur('2s')
    ->setRepeatCount('indefinite');

$element->appendChild($anim);
```

### Methods

| Method | Description |
|--------|-------------|
| `setType(string)` | Transform type: `'translate'`, `'scale'`, `'rotate'`, `'skewX'`, `'skewY'` |
| `setAttributeName(string)` | Usually `'transform'` |
| `setFrom(string\|int\|float)` | Start value |
| `setTo(string\|int\|float)` | End value |
| `setDur(string)` | Duration |
| `setRepeatCount(string\|int)` | Repetitions or `'indefinite'` |
| `setFill(string)` | `'freeze'` or `'remove'` |
| `setAdditive(string)` | `'sum'` or `'replace'` |

Note: `setFill()` on animation elements controls timing behavior, not the
SVG `fill` paint attribute. This overrides `AbstractElement::setFill()`.

## AnimationBuilder

`Atelier\Svg\Element\Builder\AnimationBuilder` provides preset animations
and a fluent builder for custom ones.

### Preset Animations

```php
use Atelier\Svg\Element\Builder\AnimationBuilder;

AnimationBuilder::fadeIn($element, '1s');
AnimationBuilder::fadeOut($element, '500ms');
AnimationBuilder::rotate($element, 0, 360, '2s');
AnimationBuilder::scale($element, 1, 1.5, '1s');
```

Each preset creates the animation element, appends it to the target
element, and returns the animation element.

### Fluent Builder

For custom animations, use the `animate()` or `animateTransform()` factory.

```php
// Animate an attribute
AnimationBuilder::animate($element, 'cx')
    ->from(50)
    ->to(150)
    ->duration('2s')
    ->repeatCount('indefinite')
    ->fillMode('freeze')
    ->apply();

// Animate a transform
AnimationBuilder::animateTransform($element, 'rotate')
    ->from('0 100 100')
    ->to('360 100 100')
    ->duration('3s')
    ->additive()
    ->apply();
```

### Builder Methods

| Method | Description |
|--------|-------------|
| `from(string\|int\|float)` | Start value |
| `to(string\|int\|float)` | End value |
| `values(string)` | Intermediate values (attribute animation only) |
| `duration(string\|int)` | Duration (accepts `'1s'`, `'500ms'`, or numeric seconds) |
| `repeatCount(string\|int)` | Repetitions |
| `fillMode(string)` | `'freeze'` or `'remove'` |
| `begin(string)` | Start trigger (attribute animation only) |
| `calcMode(string)` | Calculation mode (attribute animation only) |
| `additive()` | Make animation additive |
| `apply()` | Append animation to element, return the element |
| `getAnimation()` | Get the underlying animation element |

### CSS Animations

For CSS-based animation instead of SMIL:

```php
AnimationBuilder::addCssAnimation($element, 'pulse', [
    '0%' => ['transform' => 'scale(1)'],
    '50%' => ['transform' => 'scale(1.2)'],
    '100%' => ['transform' => 'scale(1)'],
], duration: '1s', timing: 'ease-in-out', iterationCount: 'infinite');
```

This injects a `<style>` element with `@keyframes` and applies the
`animation` property to the target element.

## See also

- [Morphing](../morphing/overview.md): interpolating between path shapes
- [Animation export](../morphing/exporting.md): exporting morph frames to SMIL, CSS, JS
- [Transforms](../styling/transforms.md): transform value types
