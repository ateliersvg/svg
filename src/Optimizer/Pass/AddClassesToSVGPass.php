<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\StyleElement;

/**
 * Optimization pass that extracts common inline styles to CSS classes.
 *
 * This pass:
 * - Identifies elements with common style attributes
 * - Extracts those common styles into CSS classes
 * - Adds a <style> element to the SVG with the extracted classes
 * - Replaces inline style attributes with class references
 *
 * Benefits:
 * - Reduces file size by eliminating duplicate style attributes
 * - Better gzip/brotli compression due to pattern reuse
 * - Cleaner SVG markup
 * - Easier style maintenance
 *
 * The pass only extracts styles that appear on multiple elements to ensure
 * it actually reduces file size.
 */
final readonly class AddClassesToSVGPass implements OptimizerPassInterface
{
    /**
     * Style attributes that can be extracted to classes.
     *
     * @var array<string>
     */
    private const array STYLEABLE_ATTRIBUTES = [
        'fill',
        'fill-opacity',
        'fill-rule',
        'stroke',
        'stroke-width',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-opacity',
        'opacity',
        'color',
        'stop-color',
        'stop-opacity',
        'font-family',
        'font-size',
        'font-weight',
        'font-style',
        'text-anchor',
        'text-decoration',
        'dominant-baseline',
    ];

    /**
     * Creates a new AddClassesToSVGPass.
     *
     * @param int    $minOccurrences          Minimum number of elements with same styles to create a class (default: 2)
     * @param string $classPrefix             Prefix for generated class names (default: 'cls-')
     * @param bool   $preserveExistingClasses Whether to preserve existing class attributes (default: true)
     */
    public function __construct(
        private int $minOccurrences = 2,
        private string $classPrefix = 'cls-',
        private bool $preserveExistingClasses = true,
    ) {
    }

    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'add-classes-to-svg';
    }

    /**
     * Optimizes the document by extracting common styles to classes.
     *
     * @param Document $document The document to optimize
     */
    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        // Step 1: Collect all elements and their style attributes
        $elementsWithStyles = $this->collectElementStyles($rootElement);

        if (empty($elementsWithStyles)) {
            return; // No elements with styles to optimize
        }

        // Step 2: Find common style patterns
        $styleGroups = $this->groupElementsByStyles($elementsWithStyles);

        // Step 3: Filter groups that have enough occurrences
        $styleGroups = array_filter(
            $styleGroups,
            fn (array $group) => count($group['elements']) >= $this->minOccurrences
        );

        if (empty($styleGroups)) {
            return; // No common styles worth extracting
        }

        // Step 4: Generate CSS classes and apply them
        $cssRules = [];
        $classCounter = 1;

        foreach ($styleGroups as $group) {
            $className = $this->classPrefix.$classCounter++;
            $cssRules[] = $this->generateCSSRule($className, $group['styles']);

            // Apply class to all elements in this group
            foreach ($group['elements'] as $element) {
                $this->applyClassToElement($element, $className, $group['styles']);
            }
        }

        // Step 5: Add <style> element to the document
        $this->addStyleElement($rootElement, $cssRules);
    }

    /**
     * Collects all elements with style attributes.
     *
     * @param ElementInterface $element The root element to start from
     *
     * @return array<array{element: ElementInterface, styles: array<string, string>}>
     */
    private function collectElementStyles(ElementInterface $element): array
    {
        $result = [];

        // Collect styles from this element
        $styles = $this->extractStyles($element);
        if (!empty($styles)) {
            $result[] = [
                'element' => $element,
                'styles' => $styles,
            ];
        }

        // Recurse to children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                $result = array_merge($result, $this->collectElementStyles($child));
            }
        }

        return $result;
    }

    /**
     * Extracts style attributes from an element.
     *
     * @param ElementInterface $element The element to extract styles from
     *
     * @return array<string, string> Style attributes and their values
     */
    private function extractStyles(ElementInterface $element): array
    {
        $styles = [];

        foreach (self::STYLEABLE_ATTRIBUTES as $attr) {
            $value = $element->getAttribute($attr);
            if (null !== $value && '' !== $value) {
                $styles[$attr] = $value;
            }
        }

        return $styles;
    }

    /**
     * Groups elements by their style attributes.
     *
     * @param array<array{element: ElementInterface, styles: array<string, string>}> $elementsWithStyles
     *
     * @return array<string, array{styles: array<string, string>, elements: array<ElementInterface>}>
     */
    private function groupElementsByStyles(array $elementsWithStyles): array
    {
        $groups = [];

        foreach ($elementsWithStyles as $item) {
            // Create a hash key from the styles
            $styleHash = $this->hashStyles($item['styles']);

            if (!isset($groups[$styleHash])) {
                $groups[$styleHash] = [
                    'styles' => $item['styles'],
                    'elements' => [],
                ];
            }

            $groups[$styleHash]['elements'][] = $item['element'];
        }

        return $groups;
    }

    /**
     * Creates a hash key from style attributes.
     *
     * @param array<string, string> $styles Style attributes
     *
     * @return string Hash key
     */
    private function hashStyles(array $styles): string
    {
        // Sort by key to ensure consistent hashing
        ksort($styles);

        // Create a string representation
        $parts = [];
        foreach ($styles as $key => $value) {
            $parts[] = "$key:$value";
        }

        return implode(';', $parts);
    }

    /**
     * Generates a CSS rule for a class.
     *
     * @param string                $className The class name
     * @param array<string, string> $styles    The style attributes
     *
     * @return string The CSS rule
     */
    private function generateCSSRule(string $className, array $styles): string
    {
        $properties = [];

        foreach ($styles as $attr => $value) {
            // CSS property names use the same format as SVG attributes
            $properties[] = "  $attr: $value;";
        }

        return ".$className {\n".implode("\n", $properties)."\n}";
    }

    /**
     * Applies a class to an element and removes the extracted style attributes.
     *
     * @param ElementInterface      $element   The element to modify
     * @param string                $className The class name to add
     * @param array<string, string> $styles    The styles that are now in the class
     */
    private function applyClassToElement(ElementInterface $element, string $className, array $styles): void
    {
        // Add or append to class attribute
        if ($this->preserveExistingClasses && $element->hasAttribute('class')) {
            $existingClass = $element->getAttribute('class');
            $element->setAttribute('class', $existingClass.' '.$className);
        } else {
            $element->setAttribute('class', $className);
        }

        // Remove the style attributes that are now in the class
        foreach (array_keys($styles) as $attr) {
            $element->removeAttribute($attr);
        }
    }

    /**
     * Adds a <style> element to the document.
     *
     * @param ElementInterface $rootElement The root SVG element
     * @param array<string>    $cssRules    The CSS rules to add
     */
    private function addStyleElement(ElementInterface $rootElement, array $cssRules): void
    {
        assert($rootElement instanceof ContainerElementInterface);

        // Create the style element
        $styleElement = new StyleElement();
        $cssContent = implode("\n\n", $cssRules);
        $styleElement->setContent($cssContent);

        // Try to find an existing <defs> element
        $defsElement = $this->findDefsElement($rootElement);

        if (null !== $defsElement) {
            // Add style to existing <defs>
            // Insert at the beginning of defs for better organization
            $defsElement->appendChild($styleElement);
        } else {
            // Create a new <defs> element and add it to the root
            $defsElement = new DefsElement();
            $defsElement->appendChild($styleElement);

            // Insert <defs> as the first child of the root element
            $this->prependChild($rootElement, $defsElement);
        }
    }

    /**
     * Finds the first <defs> element in the root.
     *
     * @param ContainerElementInterface $rootElement The root element
     *
     * @return DefsElement|null The defs element, or null if not found
     */
    private function findDefsElement(ContainerElementInterface $rootElement): ?DefsElement
    {
        foreach ($rootElement->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Prepends a child to a container element.
     *
     * @param ContainerElementInterface $parent The parent container
     * @param ElementInterface          $child  The child to prepend
     */
    private function prependChild(ContainerElementInterface $parent, ElementInterface $child): void
    {
        $children = $parent->getChildren();

        // Clear all children
        $parent->clearChildren();

        // Add new child first
        $parent->appendChild($child);

        // Re-add existing children
        foreach ($children as $existingChild) {
            $parent->appendChild($existingChild);
        }
    }
}
