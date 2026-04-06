---
order: 10
description: "Accept SVGs from users without risking XSS. Strip scripts, event handlers, and dangerous content, then validate and optimize before serving."
---
# Sanitize Uploads

Accept SVGs from users without risking XSS. Strip scripts, event handlers,
and dangerous content, then validate and optimize before serving.

```php
use Atelier\Svg\Svg;
use Atelier\Svg\Sanitizer\Sanitizer;
use Atelier\Svg\Validation\DocumentValidator;

$svg = Svg::load($uploadedFile);
$document = $svg->getDocument();

// Remove scripts, event handlers, data URIs, foreign objects
Sanitizer::strict()->sanitize($document);

// Fix broken references and structural issues
DocumentValidator::autoFix($document);

// Optimize and save
$svg->optimize()->save($outputPath);
```

## Sanitizer presets

| Preset | Behavior |
|--------|----------|
| `Sanitizer::strict()` | Removes all scripts, event handlers, JS URLs, foreign objects |
| `Sanitizer::default()` | Balanced: removes scripts but keeps more structural elements |

## What gets removed

The strict sanitizer strips:

- `<script>` elements
- `on*` event handler attributes (`onclick`, `onload`, etc.)
- `javascript:` and `data:` URLs in `href` and `xlink:href`
- `<foreignObject>` elements
- External resource references

## See also

- [Sanitization reference](../document/sanitization.md)
- [Validation](../document/validation.md)
- [Accessibility](../elements/accessibility.md): add metadata after sanitizing
