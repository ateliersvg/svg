<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;

/**
 * Abstract visitor with type-specific visit methods.
 *
 * This class provides a type-safe alternative to the generic visitor pattern
 * by dispatching to type-specific methods based on the element's class name.
 *
 * Benefits:
 * - Type safety: IDE knows the exact type in each visit method
 * - Better organization: Each element type has its own method
 * - Cleaner code: No need for instanceof checks
 * - Extensible: Easy to add support for new element types
 *
 * How it works:
 * - The visit() method uses reflection to find a matching visitXxx() method
 * - If found, it calls that method with the properly typed element
 * - If not found, it calls visitDefault() as a fallback
 *
 * Note on caching:
 * - Method names are cached per class to improve performance
 * - Cache is per-class (not shared across all TypedVisitor instances)
 * - Cache size is typically small (one entry per element type visited)
 *
 * @example
 * ```php
 * class ColorVisitor extends TypedVisitor {
 *     protected function visitCircle(CircleElement $circle): void {
 *         $circle->setAttribute('fill', '#3b82f6');
 *     }
 *
 *     protected function visitRect(RectElement $rect): void {
 *         $rect->setAttribute('fill', '#10b981');
 *     }
 *
 *     protected function visitDefault(ElementInterface $element): void {
 *         // Handle all other element types
 *     }
 * }
 * ```
 */
abstract class TypedVisitor extends AbstractVisitor
{
    /**
     * Cache of method names to avoid repeated reflection.
     * Keyed by visitor class name + element class name.
     *
     * @var array<string, string|null>
     */
    private static array $methodCache = [];

    /**
     * Visits an element by dispatching to a type-specific method.
     *
     * @param ElementInterface $element The element to visit
     *
     * @return mixed The result from the type-specific visit method
     */
    protected function doVisit(ElementInterface $element): mixed
    {
        $elementClass = $element::class;
        $visitorClass = static::class;

        // Create a unique cache key combining visitor and element class
        $cacheKey = $visitorClass.'::'.$elementClass;

        // Check cache first
        if (!isset(self::$methodCache[$cacheKey])) {
            self::$methodCache[$cacheKey] = $this->findVisitMethod($elementClass);
        }

        $methodName = self::$methodCache[$cacheKey];

        if (null !== $methodName) {
            return $this->$methodName($element);
        }

        // Fallback to default handler
        return $this->visitDefault($element);
    }

    /**
     * Finds the appropriate visit method for a class name.
     *
     * Looks for methods matching the pattern visitXxx where Xxx is the
     * short class name (without namespace).
     *
     * @param string $className Fully qualified class name
     *
     * @return string|null The method name if found, null otherwise
     */
    private function findVisitMethod(string $className): ?string
    {
        // Get short class name without namespace
        $shortName = substr($className, strrpos($className, '\\') + 1);

        // Remove "Element" suffix if present
        if (str_ends_with($shortName, 'Element')) {
            $shortName = substr($shortName, 0, -7);
        }

        // Build method name: visitCircle, visitRect, etc.
        $methodName = 'visit'.$shortName;

        // Check if method exists
        if (method_exists($this, $methodName)) {
            return $methodName;
        }

        return null;
    }

    /**
     * Default handler for elements without specific visit methods.
     *
     * Subclasses must implement this method to handle elements that don't
     * have their own visitXxx() method.
     *
     * @param ElementInterface $element The element to visit
     *
     * @return mixed The result of visiting the element
     */
    abstract protected function visitDefault(ElementInterface $element): mixed;
}
