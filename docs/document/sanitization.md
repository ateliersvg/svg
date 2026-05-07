---
order: 40
---
# Sanitization

SVG files can contain embedded scripts, event handlers, and other potentially dangerous content. The sanitizer removes these threats, making SVGs safe to display in browsers and embed in web pages.

## Quick Sanitization via the Facade

```php
use Atelier\Svg\Svg;

$svg = Svg::load('untrusted.svg')
    ->sanitize()
    ->save('safe.svg');
```

By default, `sanitize()` removes script elements, event handler attributes, and `javascript:` URLs.

## Sanitize Profiles

Three profiles are available via the `SanitizeProfile` enum:

```php
use Atelier\Svg\Sanitizer\SanitizeProfile;

// Default: removes scripts, event handlers, and JS URLs
$svg->sanitize(SanitizeProfile::DEFAULT);

// Strict: also removes <foreignObject> elements
$svg->sanitize(SanitizeProfile::STRICT);

// Permissive: only removes scripts and JS URLs (keeps event handlers)
$svg->sanitize(SanitizeProfile::PERMISSIVE);
```

| Profile | Scripts | Event Handlers | JS URLs | foreignObject |
|---------|---------|---------------|---------|---------------|
| DEFAULT | Removed | Removed | Removed | Kept |
| STRICT | Removed | Removed | Removed | Removed |
| PERMISSIVE | Removed | Kept | Removed | Kept |

## Using the Sanitizer Directly

For full control, use the `Sanitizer` class directly with its factory methods:

```php
use Atelier\Svg\Sanitizer\Sanitizer;

$document = Svg::load('untrusted.svg')->getDocument();

$sanitizer = Sanitizer::default();
$sanitizer->sanitize($document);

// Or use other presets
$sanitizer = Sanitizer::strict();
$sanitizer = Sanitizer::permissive();
```

## Custom Sanitizer with Specific Passes

Build a sanitizer with exactly the passes you need:

```php
use Atelier\Svg\Sanitizer\Sanitizer;
use Atelier\Svg\Sanitizer\Pass\RemoveScriptElementsPass;
use Atelier\Svg\Sanitizer\Pass\RemoveEventHandlersPass;
use Atelier\Svg\Sanitizer\Pass\RemoveJavascriptUrlsPass;
use Atelier\Svg\Sanitizer\Pass\RemoveForeignObjectPass;

$sanitizer = new Sanitizer([
    new RemoveScriptElementsPass(),
    new RemoveEventHandlersPass(),
]);

$sanitizer->sanitize($document);
```

## Built-in Sanitizer Passes

### RemoveScriptElementsPass

Removes all `<script>` elements from the document tree.

### RemoveEventHandlersPass

Removes all attributes starting with `on` (e.g., `onclick`, `onload`, `onmouseover`) from every element in the document.

### RemoveJavascriptUrlsPass

Removes `javascript:` and `data:text/html` values from URL-bearing attributes: `href`, `xlink:href`, `src`, `from`, `to`, and `values`. Whitespace and casing variations are normalized before checking.

### RemoveForeignObjectPass

Removes all `<foreignObject>` elements. These can contain arbitrary HTML, which may include scripts or other unsafe content.

## Writing a Custom Pass

Implement `SanitizerPassInterface` to create your own sanitization logic:

```php
use Atelier\Svg\Document;
use Atelier\Svg\Sanitizer\Pass\SanitizerPassInterface;

class RemoveStyleElementsPass implements SanitizerPassInterface
{
    public function getName(): string
    {
        return 'remove-style-elements';
    }

    public function sanitize(Document $document): void
    {
        // Your removal logic here
    }
}
```

Then include it in a custom sanitizer:

```php
$sanitizer = new Sanitizer([
    new RemoveScriptElementsPass(),
    new RemoveStyleElementsPass(),
]);
```

## Inspecting Passes

List the passes configured on a sanitizer:

```php
$sanitizer = Sanitizer::strict();
$passes = $sanitizer->getPasses();

foreach ($passes as $pass) {
    echo $pass->getName() . "\n";
}
```

## See also

- [Document Overview](overview.md): Core concepts
- [Validation](validation.md): Structural validation (complementary to sanitization)
- [Parsing SVGs](parsing.md): Parser security features (XXE prevention)
