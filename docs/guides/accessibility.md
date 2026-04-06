---
order: 60
description: "Audit and fix SVG accessibility: add titles, descriptions, and ARIA roles so SVGs work correctly with screen readers and assistive technology."
---
# Accessibility

SVGs embedded in web pages need text alternatives, correct roles, and
keyboard support to work with screen readers and assistive technology.
Atelier SVG provides tools to audit, fix, and maintain accessibility.

## Add a title and description

Every SVG that conveys meaning should have a `<title>` and `<desc>` as
direct children of the root `<svg>` element.

```php
use Atelier\Svg\Document;
use Atelier\Svg\Element\Accessibility\Accessibility;

$doc = Document::fromFile('chart.svg');

Accessibility::setTitle($doc, 'Q3 Revenue by Region');
Accessibility::setDescription($doc, 'Bar chart comparing revenue across four regions');
```

If a title or description already exists, it gets updated. These
elements are the primary way screen readers identify an SVG.

## Label individual elements

Elements inside the SVG can carry their own titles and descriptions.
Every element inherits these methods from `AbstractElement`:

```php
$rect->addTitle('Europe: $4.2M');
$rect->addDescription('Tallest bar in the chart');
```

For elements that cannot hold child nodes (like `<use>`), `addTitle`
falls back to setting `aria-label`:

```php
$use->addTitle('Home icon');
// equivalent to: $use->setAriaLabel('Home icon');
```

## Set ARIA attributes

Assign roles and labels to communicate structure to assistive technology:

```php
// Mark the SVG as a meaningful image
$svgRoot->setAriaRole('img');
$svgRoot->setAriaLabel('Company logo');

// Mark decorative SVGs as presentation
$decorativeSvg->setAriaRole('presentation');
```

Common roles for SVGs:

| Role | When to use |
|------|-------------|
| `img` | SVG conveys information (icons, charts, diagrams) |
| `presentation` | Purely decorative, should be ignored by screen readers |
| `group` | SVG contains interactive regions |

## Make interactive elements focusable

Links, buttons, and clickable regions inside an SVG need keyboard
access:

```php
$link->setFocusable(true);
$link->setTabIndex(0);
```

Without `focusable` and `tabindex`, keyboard users cannot reach
interactive SVG elements.

## Audit a document

Check for common accessibility issues before shipping:

```php
$issues = Accessibility::checkAccessibility($doc);

foreach ($issues as $issue) {
    printf("[%s] %s\n", $issue['severity'], $issue['message']);
    // [error] Missing <title> on document root
    // [warning] <image> element without text alternative
}
```

The audit checks for:

- Missing `<title>` on the root `<svg>` element
- `<image>` and `<use>` elements without text alternatives
- `<image>` and `<use>` elements without a `role` attribute
- Interactive elements (links, onclick handlers) without keyboard access

## Auto-fix issues

Let the library fix common problems automatically:

```php
Accessibility::improveAccessibility($doc, [
    'add_missing_titles'  => true,
    'add_role_attributes' => true,
    'ensure_focusable'    => true,
]);
```

All options default to `true`. The method adds a generic `<title>` if
none exists, sets `role="img"` on images lacking a role, and adds
`tabindex="0"` to interactive elements missing keyboard access.

Run the audit after auto-fix to verify no issues remain:

```php
Accessibility::improveAccessibility($doc);
$remaining = Accessibility::checkAccessibility($doc);
assert($remaining === []);
```

## Preserve accessibility during optimization

The default optimizer preset strips `<title>` and `<desc>` elements
to reduce file size. Use the accessible preset instead:

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

$optimizer = new Optimizer(OptimizerPresets::accessible());
$optimizer->optimize($doc);
```

This preset skips `RemoveTitlePass` and `RemoveDescPass`, keeps
metadata, and preserves readable IDs. It still applies structural
and value optimizations.

| Preset | Titles | Descriptions | ARIA attrs |
|--------|--------|-------------|------------|
| `default()` | Removed | Removed | Kept |
| `aggressive()` | Removed | Removed | Kept |
| `safe()` | Kept | Kept | Kept |
| `accessible()` | Kept | Kept | Kept |

## CI pipeline example

Combine auditing with optimization in a build step:

```php
$loader = new DomLoader();
$optimizer = new Optimizer(OptimizerPresets::accessible());

foreach (glob('assets/svg/*.svg') as $file) {
    $doc = $loader->loadFromFile($file);

    // Audit before optimizing
    $issues = Accessibility::checkAccessibility($doc);
    if ($issues !== []) {
        fprintf(STDERR, "%s has %d accessibility issues\n", $file, count($issues));
        // Optionally auto-fix
        Accessibility::improveAccessibility($doc);
    }

    $optimizer->optimize($doc);
    (new CompactXmlDumper())->dumpToFile($doc, $file);
}
```

This catches missing titles and descriptions at build time rather than
in production.

## See also

- [Accessibility API](../elements/accessibility.md): full class reference
- [Build charts](build-charts.md): accessible chart generation
- [Sprites and symbols](sprites-and-symbols.md): accessible icon sprites
- [Batch optimize](batch-optimize.md): optimization presets
