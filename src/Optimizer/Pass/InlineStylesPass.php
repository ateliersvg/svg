<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\StyleElement;

/**
 * Optimization pass that inlines CSS styles as element attributes.
 *
 * This pass:
 * - Reads styles from <style> elements
 * - Applies class-based styles to elements as inline attributes
 * - Removes the class attribute after inlining
 * - Optionally removes the <style> element if all styles are inlined
 *
 * Benefits:
 * - Eliminates dependency on CSS (useful for some renderers)
 * - Can reduce file size if classes are only used once
 * - Better for SVGs used in certain contexts (email, some frameworks)
 *
 * Note: This is the opposite of AddClassesToSVGPass. Use this when you need
 * styles as attributes rather than CSS classes.
 */
final readonly class InlineStylesPass implements OptimizerPassInterface
{
    /**
     * Creates a new InlineStylesPass.
     *
     * @param bool $removeStyleElements   Whether to remove <style> elements after inlining (default: true)
     * @param bool $removeClassAttributes Whether to remove class attributes after inlining (default: true)
     */
    public function __construct(
        private bool $removeStyleElements = true,
        private bool $removeClassAttributes = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'inline-styles';
    }

    /**
     * Optimizes the document by inlining styles.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Step 1: Parse all CSS from style elements
        $cssRules = $this->parseCssRules($rootElement);

        if (empty($cssRules)) {
            return; // No CSS to inline
        }

        // Step 2: Apply styles to elements
        $this->applyStylesToElements($rootElement, $cssRules);

        // Step 3: Remove style elements if configured
        if ($this->removeStyleElements) {
            $this->removeStyleElements($rootElement);
        }
    }

    /**
     * Parses CSS rules from all style elements.
     *
     * @param ElementInterface $element The root element
     *
     * @return array<string, array<string, string>> Map of class name to properties
     */
    private function parseCssRules(ElementInterface $element): array
    {
        $rules = [];

        // Check if this is a style element
        if ($element instanceof StyleElement) {
            $cssContent = $element->getContent();
            if (null !== $cssContent && '' !== $cssContent) {
                $rules = array_merge($rules, $this->parseCss($cssContent));
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $rules = array_merge($rules, $this->parseCssRules($child));
            }
        }

        return $rules;
    }

    /**
     * Parses CSS content into rules.
     *
     * @param string $css The CSS content
     *
     * @return array<string, array<string, string>> Map of class name to properties
     */
    private function parseCss(string $css): array
    {
        $rules = [];

        // Simple CSS parser for class-based rules
        // Matches: .classname { property: value; }
        $pattern = '/\.([a-zA-Z0-9_-]+)\s*\{([^}]+)\}/';

        if (preg_match_all($pattern, $css, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $className = $match[1];
                $properties = $this->parseProperties($match[2]);

                if (!empty($properties)) {
                    $rules[$className] = $properties;
                }
            }
        }

        return $rules;
    }

    /**
     * Parses CSS properties from a rule body.
     *
     * @param string $propertiesStr The properties string
     *
     * @return array<string, string> Map of property name to value
     */
    private function parseProperties(string $propertiesStr): array
    {
        $properties = [];

        // Split by semicolon
        $declarations = explode(';', $propertiesStr);

        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ('' === $declaration) {
                continue;
            }

            // Split by colon
            $parts = explode(':', $declaration, 2);
            if (2 === count($parts)) {
                $property = trim($parts[0]);
                $value = trim($parts[1]);

                if ('' !== $property && '' !== $value) {
                    $properties[$property] = $value;
                }
            }
        }

        return $properties;
    }

    /**
     * Applies styles to elements based on their class attributes.
     *
     * @param ElementInterface                     $element  The element to process
     * @param array<string, array<string, string>> $cssRules CSS rules
     */
    private function applyStylesToElements(ElementInterface $element, array $cssRules): void
    {
        // Check if this element has a class attribute
        $classAttr = $element->getAttribute('class');
        if (null !== $classAttr && '' !== trim($classAttr)) {
            $stylesApplied = false;

            // Split by whitespace to get individual classes
            $classes = preg_split('/\s+/', trim($classAttr));
            if (false !== $classes) {
                foreach ($classes as $class) {
                    if ('' !== $class && isset($cssRules[$class])) {
                        // Apply this class's styles
                        foreach ($cssRules[$class] as $property => $value) {
                            // Only set if not already set (element attributes take precedence)
                            if (!$element->hasAttribute($property)) {
                                $element->setAttribute($property, $value);
                                $stylesApplied = true;
                            }
                        }
                    }
                }
            }

            // Remove class attribute if configured and styles were actually applied
            if ($this->removeClassAttributes && $stylesApplied) {
                $element->removeAttribute('class');
            }
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $this->applyStylesToElements($child, $cssRules);
            }
        }
    }

    /**
     * Removes style elements from the document.
     *
     * @param ElementInterface $element The element to process
     */
    private function removeStyleElements(ElementInterface $element): void
    {
        if ($element instanceof ContainerElementInterface) {
            // Use array copy to avoid modification during iteration
            $children = $element->getChildren();

            foreach ($children as $child) {
                if ($child instanceof StyleElement) {
                    $element->removeChild($child);
                } else {
                    // Recurse for non-style elements
                    $this->removeStyleElements($child);
                }
            }
        }
    }
}
