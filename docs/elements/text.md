---
order: 30
---
# Text

The text subsystem provides elements and utilities for rendering,
positioning, and measuring SVG text.

## TextElement

Represents the `<text>` element. Supports positioning, spacing, rotation,
and text content.

```php
use Atelier\Svg\Element\Text\TextElement;

$text = TextElement::create(50, 100, 'Hello, SVG');
$text->setFontFamily('Arial');
$text->setFontSize(16);
$text->setTextAnchor('middle');
```

Additional typography methods:

```php
$text->setLetterSpacing('2px');
$text->setWordSpacing('5px');
$text->setTextDecoration('underline');
$text->setBaselineShift('super');
$text->setTextLength(200);
$text->setLengthAdjust('spacingAndGlyphs');
$text->setDx(5);
$text->setDy(10);
$text->setRotate('0 15 30');
```

## TspanElement

Used inside `<text>` (or another `<tspan>`) to style or reposition a
substring of text. Shares the same positioning and typography methods as
`TextElement`.

```php
use Atelier\Svg\Element\Text\TspanElement;

$tspan = new TspanElement();
$tspan->setTextContent('bold part');
$tspan->setFontWeight('bold');
$tspan->setPosition(50, 120);

$text->appendChild($tspan);
```

## TextPathElement

Renders text along a `<path>` element.

```php
use Atelier\Svg\Element\Text\TextPathElement;

$textPath = new TextPathElement();
$textPath->setHref('#curve');
$textPath->setStartOffset('50%');
$textPath->setMethod('align');     // 'align' or 'stretch'
$textPath->setSpacing('auto');     // 'auto' or 'exact'
$textPath->setTextContent('Along the curve');

$text->appendChild($textPath);
```

## TspanBuilder

A builder utility for adding multiple `<tspan>` children to a `<text>`
element with automatic horizontal positioning and gap control.

```php
use Atelier\Svg\Element\Builder\TspanBuilder;
use Atelier\Svg\Element\Text\TextElement;

$text = TextElement::create(10, 50);

$builder = new TspanBuilder($text);
$builder->setDefaultGap(5);
$builder->add('Hello', 0, ['fill' => '#000'])
        ->add('World', 10, ['fill' => '#f00']);
```

Place spans at absolute positions:

```php
$builder->addAt('Label', 200, 50, ['font-weight' => 'bold']);
```

Distribute spans evenly across a width, or stack vertically:

```php
$builder->distributeEvenly(['A', 'B', 'C'], 300);
$builder->stackVertically(['Line 1', 'Line 2'], 20);
```

## TextMeasurement

Estimates text dimensions using character-width tables. These are
approximations: accurate rendering requires actual font metrics.

```php
use Atelier\Svg\Element\Text\TextMeasurement;

// Measure a text element
$metrics = TextMeasurement::measure($text, 'Arial', 16);
$metrics->width;       // float
$metrics->height;      // float
$metrics->baseline;    // float
$metrics->boundingBox; // BoundingBox

// Standalone width calculation
$w = TextMeasurement::calculateTextWidth('Hello', 'Arial', 16);

// Break text into lines fitting a max width
$lines = TextMeasurement::breakLines($content, 200, 'Arial', 16);

// Find optimal font size to fit text in a box
$size = TextMeasurement::calculateFitSize($content, 200, 100);

// Auto-fit a text element to a bounding box
TextMeasurement::fitToBox($text, 200, 100);

// Check if text fits without wrapping
TextMeasurement::fitsInWidth('Short', 200, 'Arial', 16); // bool
```

## See also

- [Overview](overview.md): base element classes
- [Accessibility](accessibility.md): making text accessible
- [Shapes](shapes.md): paths for textPath
