---
order: 30
description: "Generate SVG charts and data visualizations programmatically in PHP. Compose bar charts, add accessible metadata, and combine layouts."
---
# Build Charts

Compose SVG documents programmatically: generate charts, combine them
into layouts, add accessible metadata.

## Bar chart

```php
use Atelier\Svg\Svg;
use Atelier\Svg\Element\Accessibility\Accessibility;

$data = [120, 85, 200, 150, 175];
$chart = Svg::create(400, 300);

foreach ($data as $i => $value) {
    $height = $value;
    $chart->rect($i * 70 + 30, 300 - $height, 50, $height, [
        'fill' => '#3b82f6',
    ]);
}

Accessibility::setTitle($chart->getDocument(), 'Monthly Revenue');
Accessibility::setDescription(
    $chart->getDocument(),
    'Bar chart showing revenue for 5 months',
);

$chart->save('chart.svg');
```

## Adding labels

```php
use Atelier\Svg\Element\Text\TextElement;

$labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
$document = $chart->getDocument();

foreach ($labels as $i => $label) {
    $text = TextElement::create($i * 70 + 55, 295, $label)
        ->setTextAnchor('middle')
        ->setFontSize('12')
        ->setFill('#666');
    $document->documentElement->appendChild($text);
}
```

## Accessibility

Always add a title and description to generated charts. Screen readers
use these to describe the visual content.

```php
Accessibility::setTitle($document, 'Quarterly Sales');
Accessibility::setDescription($document, 'Bar chart comparing Q1-Q4 sales figures');
Accessibility::improveAccessibility($document);
```

## See also

- [Accessibility](../elements/accessibility.md): full accessibility API
- [Layout](../styling/layout.md): positioning and alignment utilities
- [Text elements](../elements/text.md): text styling and tspan
- [Styling overview](../styling/overview.md): fill, stroke, and presentation attributes
- [Document exporting](../document/exporting.md): save and serialize documents
