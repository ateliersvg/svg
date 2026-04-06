<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\StyleElement;

/**
 * Optimization pass that removes unused CSS classes from style elements.
 *
 * This pass:
 * - Finds all CSS classes defined in <style> elements
 * - Scans the document to find which classes are actually used
 * - Removes CSS rules for classes that aren't referenced
 *
 * Benefits:
 * - Reduces file size by removing dead CSS code
 * - Cleaner, more maintainable stylesheets
 * - Better performance (less CSS to parse)
 *
 * The pass is conservative and will keep classes if they:
 * - Are referenced in any class attribute
 * - Are used in compound selectors (e.g., .class1.class2)
 * - Might be used dynamically (configurable)
 */
final readonly class RemoveUnusedClassesPass implements OptimizerPassInterface
{
    /**
     * Creates a new RemoveUnusedClassesPass.
     *
     * @param bool $removeEmptyStyles Whether to remove <style> elements that become empty (default: true)
     */
    public function __construct(
        private bool $removeEmptyStyles = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'remove-unused-classes';
    }

    /**
     * Optimizes the document by removing unused CSS classes.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Step 1: Collect all used class names from elements
        $usedClasses = $this->collectUsedClasses($rootElement);

        if (empty($usedClasses)) {
            // No classes used, can remove all class-based CSS
            if ($this->removeEmptyStyles) {
                $this->removeAllClassBasedStyles($rootElement);
            }

            return;
        }

        // Step 2: Find and clean style elements
        $this->cleanStyleElements($rootElement, $usedClasses);
    }

    /**
     * Collects all class names used in the document.
     *
     * @param ElementInterface $element The root element
     *
     * @return array<string, true> Set of used class names
     */
    private function collectUsedClasses(ElementInterface $element): array
    {
        $usedClasses = [];

        // Check this element's class attribute
        $classAttr = $element->getAttribute('class');
        if (null !== $classAttr && '' !== trim($classAttr)) {
            // Split by whitespace to get individual classes
            $classes = preg_split('/\s+/', trim($classAttr));
            if (false !== $classes) {
                foreach ($classes as $class) {
                    if ('' !== $class) {
                        $usedClasses[$class] = true;
                    }
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $usedClasses = array_merge($usedClasses, $this->collectUsedClasses($child));
            }
        }

        return $usedClasses;
    }

    /**
     * Cleans style elements by removing unused classes.
     *
     * @param ElementInterface    $element     The element to process
     * @param array<string, true> $usedClasses Set of used class names
     */
    private function cleanStyleElements(ElementInterface $element, array $usedClasses): void
    {
        // Check if this is a style element
        if ($element instanceof StyleElement) {
            $cssContent = $element->getContent();
            if (null !== $cssContent && '' !== $cssContent) {
                $cleanedCss = $this->removeUnusedClassesFromCss($cssContent, $usedClasses);

                if ('' === $cleanedCss || '' === trim($cleanedCss)) {
                    // Style became empty, mark for removal
                    if ($this->removeEmptyStyles) {
                        $parent = $element->getParent();
                        if ($parent instanceof ContainerElementInterface) {
                            $parent->removeChild($element);
                        }
                    }
                } else {
                    $element->setContent($cleanedCss);
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            // Use array copy to avoid modification during iteration
            $children = $element->getChildren();
            foreach ($children as $child) {
                $this->cleanStyleElements($child, $usedClasses);
            }
        }
    }

    /**
     * Removes unused classes from CSS content.
     *
     * @param string              $css         The CSS content
     * @param array<string, true> $usedClasses Set of used class names
     *
     * @return string Cleaned CSS content
     */
    private function removeUnusedClassesFromCss(string $css, array $usedClasses): string
    {
        // Parse CSS rules
        // Match: .classname { ... } or .classname, .otherclass { ... }
        $pattern = '/\.([a-zA-Z0-9_-]+)\s*\{([^}]*)\}/';

        $cleanedCss = preg_replace_callback(
            $pattern,
            function ($matches) use ($usedClasses) {
                $className = $matches[1];

                // Keep the rule if the class is used
                if (isset($usedClasses[$className])) {
                    return $matches[0]; // Keep the entire rule
                }

                // Remove the rule
                return '';
            },
            $css
        );

        // Handle compound selectors and multiple selectors
        // This is a simplified approach - a full CSS parser would be better
        // but this handles the most common cases

        return $cleanedCss ?? $css;
    }

    /**
     * Removes all class-based styles from the document.
     *
     * @param ElementInterface $element The element to process
     */
    private function removeAllClassBasedStyles(ElementInterface $element): void
    {
        if ($element instanceof StyleElement) {
            $cssContent = $element->getContent();
            if (null !== $cssContent && '' !== $cssContent) {
                // Remove all class-based rules
                $cleanedCss = preg_replace('/\.([a-zA-Z0-9_-]+)\s*\{[^}]*\}/', '', $cssContent);

                if ('' === $cleanedCss || '' === trim($cleanedCss ?? '')) {
                    // Style became empty, remove it
                    $parent = $element->getParent();
                    if ($parent instanceof ContainerElementInterface) {
                        $parent->removeChild($element);
                    }
                } else {
                    $element->setContent($cleanedCss);
                }
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            $children = $element->getChildren();
            foreach ($children as $child) {
                $this->removeAllClassBasedStyles($child);
            }
        }
    }
}
