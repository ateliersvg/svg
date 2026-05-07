---
order: 30
---
# Validation

Atelier SVG provides comprehensive validation to check SVG documents for structural issues, broken references, duplicate IDs, accessibility problems, and spec compliance.

## Quick Validation via Document

The `Document` class provides direct validation methods:

```php
use Atelier\Svg\Svg;

$document = Svg::load('icon.svg')->getDocument();

// Check if valid (no errors)
if ($document->isValid()) {
    echo 'Document is valid';
}

// Get a detailed validation result
$result = $document->validate();
echo $result->format();
```

## Validator

For more control, use the `Validator` class directly with a `ValidationProfile`:

```php
use Atelier\Svg\Validation\Validator;
use Atelier\Svg\Validation\ValidationProfile;

$validator = new Validator(ValidationProfile::strict());
$result = $validator->validate($document);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $issue) {
        echo $issue->format() . "\n";
    }
}
```

## Validation Profiles

Profiles control which rules are checked and how strictly. Three built-in profiles are available:

- **`ValidationProfile::strict()`**: SVG 1.1 spec compliance. Errors on missing required attributes, invalid nesting, broken references, and duplicate IDs.
- **`ValidationProfile::lenient()`**: Real-world tolerance. Most issues reported as warnings. Default.
- **`ValidationProfile::accessible()`**: WCAG focus. Requires `<title>`, `viewBox`, and alt text on images.

Extend or build custom profiles:

```php
$profile = ValidationProfile::lenient()->with([
    'check_id_format' => true,
    'require_viewbox' => true,
]);
```

## ValidationResult

The result object provides typed access to all issues found:

```php
$result = $document->validate();

$result->isValid();              // true if no errors (warnings are OK)
$result->hasIssues();            // true if any issues at all
$result->count();                // total issue count

$result->getErrors();            // array of error-level issues
$result->getWarnings();          // array of warning-level issues
$result->getInfo();              // array of info-level issues
$result->getCounts();            // ['errors' => 0, 'warnings' => 2, 'info' => 1]

echo $result->format();          // human-readable report
$result->toArray();              // array for serialization
```

## DocumentValidator

`DocumentValidator` provides static utility methods for common validation tasks:

```php
use Atelier\Svg\Validation\DocumentValidator;

// Simple validation (returns array of error strings)
$errors = DocumentValidator::validate($document);

// Lint with configurable checks
$warnings = DocumentValidator::lint($document, [
    'check_ids' => true,
    'check_references' => true,
    'check_colors' => true,
    'check_transforms' => true,
    'check_accessibility' => true,
]);

// Get improvement suggestions
$suggestions = DocumentValidator::suggestImprovements($document);
```

## Auto-fixing Issues

The library can automatically fix common problems:

```php
// Fix everything at once
$fixes = $document->autoFix();
// ['broken_references' => 3, 'duplicate_ids' => 1]

// Or fix specific issues
$count = $document->fixBrokenReferences(); // removes broken url(#id) references
$count = $document->fixDuplicateIds();     // renames duplicate IDs
```

## Reference Tracking

Check for broken and circular references:

```php
// Find references pointing to non-existent IDs
$broken = $document->findBrokenReferences();

// Find circular dependency chains (e.g., A references B, B references A)
$cycles = $document->findCircularReferences();
```

## Error handling

Validation does not throw. `validate()` always returns a `ValidationResult`; check `isValid()` to determine whether to proceed.

```php
<?php
use Atelier\Svg\Svg;
use Atelier\Svg\Validation\Validator;
use Atelier\Svg\Validation\ValidationProfile;

$document = Svg::load('icon.svg')->getDocument();
$result = (new Validator(ValidationProfile::strict()))->validate($document);

if (!$result->isValid()) {
    foreach ($result->getErrors() as $issue) {
        // Each $issue has ->format(), ->getMessage(), ->getSeverity(), ->getElement()
        echo $issue->format() . "\n";
    }
    // Optionally bail out, auto-fix, or log
}
```

### What `ValidationResult` contains on failure

| Method | Returns |
|--------|---------|
| `isValid()` | `false` when at least one error exists |
| `hasIssues()` | `true` when any issue exists (including warnings) |
| `getErrors()` | Array of error-level `ValidationIssue` objects |
| `getWarnings()` | Array of warning-level `ValidationIssue` objects |
| `getInfo()` | Array of info-level `ValidationIssue` objects |
| `getCounts()` | `['errors' => N, 'warnings' => N, 'info' => N]` |
| `format()` | Human-readable multi-line report |
| `toArray()` | Serializable array of all issues |

Validation never throws `ParseException` or `RuntimeException`; those come from loading. If you need to catch loading failures before validating, wrap `Svg::load()` in a try/catch (see [Parsing](/document/parsing/)).

## See also

- [Document Overview](overview.md): Core concepts
- [Sanitization](sanitization.md): Security-focused cleaning
- [Parsing SVGs](parsing.md): Parse profiles for input validation
