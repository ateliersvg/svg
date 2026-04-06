---
order: 80
---
# Selectors

Atelier SVG provides CSS-like selectors for querying elements and a visitor
pattern for traversing and transforming the element tree.

## SelectorMatcher

Matches elements against CSS-like selector strings.

Supported selectors:

| Syntax              | Description                  |
|---------------------|------------------------------|
| `*`                 | Universal: matches any     |
| `rect`              | Tag name                     |
| `#myId`             | ID selector                  |
| `.myClass`          | Class selector               |
| `[attr]`            | Attribute exists             |
| `[attr="value"]`    | Exact match                  |
| `[attr^="value"]`   | Starts with                  |
| `[attr$="value"]`   | Ends with                    |
| `[attr*="value"]`   | Contains                     |
| `[attr~="value"]`   | Word in space-separated list |
| `[attr\|="value"]`  | Exact or dash-prefixed       |

```php
use Atelier\Svg\Selector\SelectorMatcher;

$matcher = new SelectorMatcher();
$matcher->matches($element, 'rect');            // true if <rect>
$matcher->matches($element, '#header');         // true if id="header"
$matcher->matches($element, '.active');         // true if has class "active"
$matcher->matches($element, '[fill="red"]');    // true if fill="red"
```

## Traverser

Performs a depth-first traversal of the element tree, applying a visitor
to every element.

```php
use Atelier\Svg\Visitor\Traverser;

$traverser = new Traverser($visitor);
$traverser->traverse($rootElement);
```

The traverser visits the element, then recursively visits each child of
container elements.

## VisitorInterface

The base contract. Implement `visit(ElementInterface $element): mixed`.

## AbstractVisitor

Template class with `beforeVisit`, `doVisit`, and `afterVisit` hooks.
Subclass and implement `doVisit` for your logic.

```php
use Atelier\Svg\Visitor\AbstractVisitor;
use Atelier\Svg\Element\ElementInterface;

class CountVisitor extends AbstractVisitor
{
    public int $count = 0;

    protected function doVisit(ElementInterface $element): mixed
    {
        $this->count++;
        return null;
    }
}
```

## CallbackVisitor

Wraps a closure so you can traverse without creating a dedicated class.

```php
use Atelier\Svg\Visitor\CallbackVisitor;
use Atelier\Svg\Visitor\Traverser;

$visitor = new CallbackVisitor(function ($element) {
    $element->addClass('visited');
    return true; // return false to stop
});

$traverser = new Traverser($visitor);
$traverser->traverse($root);
```

## QueryVisitor

Collects elements matching a selector during traversal.

```php
use Atelier\Svg\Visitor\QueryVisitor;
use Atelier\Svg\Selector\SelectorMatcher;
use Atelier\Svg\Visitor\Traverser;

$query = new QueryVisitor('.highlight', new SelectorMatcher());
$traverser = new Traverser($query);
$traverser->traverse($root);

$query->getMatches();      // ElementInterface[]
$query->getFirstMatch();   // ElementInterface|null
$query->hasMatches();      // bool
$query->getMatchCount();   // int
```

Pass `findFirst: true` to the constructor to stop after the first match.

## TypedVisitor

Dispatches to type-specific `visitXxx` methods based on the element class
name. The `Element` suffix is stripped automatically.

```php
use Atelier\Svg\Visitor\TypedVisitor;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\ElementInterface;

class ColorVisitor extends TypedVisitor
{
    protected function visitCircle(CircleElement $circle): mixed
    {
        $circle->setFill('#3b82f6');
        return null;
    }

    protected function visitRect(RectElement $rect): mixed
    {
        $rect->setFill('#10b981');
        return null;
    }

    protected function visitDefault(ElementInterface $element): mixed
    {
        return null; // required fallback
    }
}
```

Method names are cached per class for performance.

## TransformVisitor

Applies a 2D affine transformation matrix to elements during traversal.

```php
use Atelier\Svg\Visitor\TransformVisitor;

$visitor = new TransformVisitor();
$visitor->setTransformMatrix([
    'a' => 1.0, 'b' => 0.0,
    'c' => 0.0, 'd' => 1.0,
    'e' => 50.0, 'f' => 25.0,  // translate(50, 25)
]);

// Parse existing transforms, merge matrices
$matrix = $visitor->parseTransformToMatrix('matrix(1 0 0 1 10 10)');
$merged = $visitor->mergeMatrices($matrix, $otherMatrix);
```

## See also

- [Overview](overview.md): element tree basics
- [Structure](structure.md): container elements to traverse
