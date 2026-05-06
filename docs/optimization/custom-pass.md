---
order: 50
---
# Custom Pass

You can extend the optimization pipeline by implementing your own passes. The library provides an interface, an abstract base class with tree traversal, and a trait for common element-preservation logic.

## OptimizerPassInterface

Every pass must implement `Atelier\Svg\Optimizer\Pass\OptimizerPassInterface`:

```php
namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;

interface OptimizerPassInterface
{
    public function getName(): string;

    public function optimize(Document $document): void;
}
```

- `getName()` returns a unique identifier for the pass (used in logging and debugging).
- `optimize()` receives the full `Document` and modifies it in place.

### Minimal example

```php
use Atelier\Svg\Document;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;

final class RemoveDataAttributesPass implements OptimizerPassInterface
{
    public function getName(): string
    {
        return 'remove-data-attributes';
    }

    public function optimize(Document $document): void
    {
        $root = $document->getRootElement();
        if (null === $root) {
            return;
        }

        // Walk the tree and remove data-* attributes
        $this->process($root);
    }

    private function process(\Atelier\Svg\Element\ElementInterface $element): void
    {
        foreach ($element->getAttributes() as $name => $value) {
            if (str_starts_with($name, 'data-')) {
                $element->removeAttribute($name);
            }
        }

        if ($element instanceof \Atelier\Svg\Element\ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->process($child);
            }
        }
    }
}
```

## AbstractOptimizerPass

`Atelier\Svg\Optimizer\Pass\AbstractOptimizerPass` provides top-down recursive tree traversal. Extend it and implement `processElement()` instead of writing your own traversal loop.

```php
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Optimizer\Pass\AbstractOptimizerPass;

final class StripFillNonePass extends AbstractOptimizerPass
{
    public function getName(): string
    {
        return 'strip-fill-none';
    }

    protected function processElement(ElementInterface $element): void
    {
        if ($element->getAttribute('fill') === 'none') {
            $element->removeAttribute('fill');
        }
    }
}
```

The base class handles:

1. Null-checking the document root.
2. Calling `processElement()` on every element in the tree (top-down, depth-first).

Override `traverseElement()` if you need different traversal order (e.g. bottom-up):

```php
protected function traverseElement(ElementInterface $element): void
{
    // Process children first (bottom-up)
    if ($element instanceof ContainerElementInterface) {
        foreach ($element->getChildren() as $child) {
            $this->traverseElement($child);
        }
    }

    // Then process this element
    $this->processElement($element);
}
```

## PreservingAttributesTrait

`Atelier\Svg\Optimizer\Pass\PreservingAttributesTrait` provides logic for checking whether an element has attributes that should prevent its removal (e.g. `id`, `class`, event handlers).

```php
use Atelier\Svg\Optimizer\Pass\AbstractOptimizerPass;
use Atelier\Svg\Optimizer\Pass\PreservingAttributesTrait;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\ContainerElementInterface;

final class RemoveEmptyTextPass extends AbstractOptimizerPass
{
    use PreservingAttributesTrait;

    public function getName(): string
    {
        return 'remove-empty-text';
    }

    protected function processElement(ElementInterface $element): void
    {
        if ('text' !== $element->getTagName()) {
            return;
        }

        // Skip if element has preserving attributes (id, class, event handlers)
        if ($this->hasPreservingAttributes($element, $this->getDefaultPreservingAttributes())) {
            return;
        }

        // Remove empty text elements
        if ($element instanceof ContainerElementInterface && 0 === count($element->getChildren())) {
            $element->getParent()?->removeChild($element);
        }
    }
}
```

The trait provides two methods:

| Method | Description |
|---|---|
| `hasPreservingAttributes($element, $attrs)` | Returns `true` if the element has any of the given attribute names |
| `getDefaultPreservingAttributes()` | Returns the default list: `id`, `class`, `onclick`, `onload`, `onmouseover`, `onmouseout`, `onmousemove`, `onmousedown`, `onmouseup`, `onfocus`, `onblur` |

## Registering a custom pass

Pass your custom pass to the `Optimizer` constructor or use `addPass()`:

```php
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;

// Add to an existing preset
$passes = OptimizerPresets::default();
$passes[] = new RemoveDataAttributesPass();

$optimizer = new Optimizer($passes);
$optimizer->optimize($document);
```

Or use the `Svg` facade with `optimizeWith()`:

```php
use Atelier\Svg\Svg;

Svg::load('input.svg')
    ->optimizeWith([
        ...OptimizerPresets::default(),
        new RemoveDataAttributesPass(),
    ])
    ->save('output.svg');
```

## Pass ordering guidelines

- Cleanup and removal passes run early (remove noise before optimization).
- Structural passes (collapse groups, move attributes) run in the middle.
- Transform conversion runs before path operations.
- Path simplification and merging run late.
- `CleanupIdsPass` and `RemoveUnusedNSPass` run last (after all references are finalized).

## See also

- [Optimization overview](../overview.md)
- [Cleanup passes](passes/cleanup.md)
- [Conversion passes](passes/convert.md)
- [Removal passes](passes/remove.md)
- [Merge and restructure passes](passes/merge.md)
