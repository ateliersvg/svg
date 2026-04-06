---
order: 100
---
# Accessibility

The `Accessibility` helper class and built-in element methods provide
WCAG-friendly utilities for SVG documents.

## Element-Level Methods

Every element inherits accessibility methods from `AbstractElement`:

```php
$rect->addTitle('Blue rectangle');
$rect->addDescription('A decorative rectangle with rounded corners');
$rect->setAriaLabel('Navigation icon');
$rect->setAriaRole('img');
$rect->setFocusable(true);
$rect->setTabIndex(0);
```

`addTitle` and `addDescription` insert `<title>` and `<desc>` child
elements. If the element is not a container (e.g. `UseElement`),
`addTitle` falls back to setting `aria-label` instead.

## Document-Level Helpers

The `Accessibility` class provides static methods that operate on a
`Document` instance.

```php
use Atelier\Svg\Element\Accessibility\Accessibility;
use Atelier\Svg\Document;

$doc = Document::fromFile('icon.svg');

// Set document title and description
Accessibility::setTitle($doc, 'Company Logo');
Accessibility::setDescription($doc, 'The Acme Corp logo in full color');
```

These methods add or update `<title>` and `<desc>` as direct children
of the root `<svg>` element.

## Auditing

Check a document for common accessibility issues:

```php
$issues = Accessibility::checkAccessibility($doc);

foreach ($issues as $issue) {
    echo $issue['severity'];  // 'error' or 'warning'
    echo $issue['message'];
    echo $issue['element'];   // e.g. 'image#logo'
}
```

Checks performed:

- Missing `<title>` on the document root
- `<image>` and `<use>` elements without text alternatives
  (`aria-label` or child `<title>`)
- `<image>` and `<use>` elements without `role` attribute
- Interactive elements (`<a>`, elements with `onclick`) without
  `tabindex` or `focusable`

## Auto-Improvement

Automatically fix common accessibility issues:

```php
Accessibility::improveAccessibility($doc, [
    'add_missing_titles'  => true,  // add default <title> if missing
    'add_role_attributes' => true,  // add role="img" to images
    'ensure_focusable'    => true,  // add tabindex=0 to interactive elements
]);
```

All options default to `true`.

## See also

- [Overview](overview.md): base element API
- [Structure](structure.md): document structure
- [Text](text.md): text elements for readable content
