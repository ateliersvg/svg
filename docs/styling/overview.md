# Styling

Atelier SVG provides a layered style system for reading, writing, and managing inline styles, computed styles, and themes across SVG documents.

## Style

`Atelier\Svg\Value\Style` represents inline CSS declarations (the `style` attribute). It parses semicolon-separated `property: value` pairs and provides a mutable API.

```php
use Atelier\Svg\Value\Style;

// Parse from a style attribute string
$style = Style::parse('fill: red; stroke: blue; opacity: 0.8');

$style->get('fill');       // "red"
$style->has('stroke');     // true
$style->set('fill', '#000');
$style->remove('opacity');
$style->getAll();          // ['fill' => '#000', 'stroke' => 'blue']

// Create from array
$style = Style::fromArray(['fill' => '#fff', 'stroke-width' => '2']);

// Merge (properties from $other override)
$style->merge($otherStyle);

$style->isEmpty();  // bool
$style->copy();     // deep copy
$style->clear();    // remove all
echo $style;        // "fill: #fff; stroke-width: 2"
```

## StyleBuilder

`Atelier\Svg\Value\Style\StyleBuilder` provides a fluent, element-bound API. Access it from any element via `$element->style()`.

```php
$element->style()
    ->fill('#3b82f6')
    ->stroke('#1e40af')
    ->strokeWidth(2)
    ->opacity(0.9)
    ->fontFamily('Arial')
    ->fontSize('14px')
    ->apply();  // writes the style attribute back to the element
```

Additional convenience methods: `fillOpacity()`, `strokeOpacity()`, `fontWeight()`, `display()`, `visibility()`.

You can also merge styles in bulk:

```php
$element->style()
    ->merge(['fill' => 'red', 'stroke' => 'blue'])
    ->apply();
```

## ComputedStyle

`Atelier\Svg\Style\ComputedStyle` resolves the effective style of an element by walking up the DOM tree for inheritable SVG presentation attributes (fill, stroke, font-family, opacity, etc.).

```php
use Atelier\Svg\Style\ComputedStyle;

$computed = ComputedStyle::of($element);
$computed->get('fill');        // resolved value, possibly inherited
$computed->all();              // all resolved properties

ComputedStyle::isInheritable('fill');            // true
ComputedStyle::isInheritable('x');               // false
ComputedStyle::getInheritableAttributes();       // full list
```

## StyleManager

`Atelier\Svg\Value\Style\StyleManager` operates at the document level. It provides utilities for applying themes, transforming colors, and converting between inline styles and presentation attributes.

```php
use Atelier\Svg\Value\Style\StyleManager;

$manager = new StyleManager($document);

// Apply a theme (selector => properties)
$manager->applyTheme([
    '.primary' => ['fill' => '#3b82f6'],
    'rect'     => ['rx' => '5'],
    '#logo'    => ['opacity' => '0.9'],
]);

// Color operations
$manager->transformColors(['#ff0000' => '#00ff00']);
$manager->applyDarkMode();
$manager->normalizeColors();
$manager->minifyColors();
$colors = $manager->getUsedColors(); // string[]

// Style conversion
$manager->inlineAllStyles();   // presentation attrs -> inline style
$manager->extractAllStyles();  // inline style -> presentation attrs
$styleMap = $manager->extractInlineStyles(); // extract to map by element ID
```

## ThemeManager

`Atelier\Svg\Value\Style\ThemeManager` handles theme creation and application with built-in light/dark presets.

```php
use Atelier\Svg\Value\Style\ThemeManager;

// Apply a theme to a document or element subtree
ThemeManager::applyTheme($document, ThemeManager::lightTheme());
ThemeManager::applyTheme($document, ThemeManager::darkTheme());

// Create a theme from a color palette
$theme = ThemeManager::createThemeFromPalette([
    'primary'   => '#3b82f6',
    'secondary' => '#64748b',
    'accent'    => '#f59e0b',
]);
// Generates .primary {fill}, .primary-stroke {stroke}, .primary-text {fill}

// Extract the current theme from a document
$theme = ThemeManager::extractTheme($document);
```

Themes use CSS-like selectors: `.class`, `#id`, `tagName`, and `*` (universal).

## See also

- [Value types](values.md): Color, Length, Angle, and other value objects
- [CSS/SVG transforms](transforms.md): TransformList and transform functions
- [Layout](layout.md): positioning and alignment
