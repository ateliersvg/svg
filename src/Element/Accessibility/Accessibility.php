<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Accessibility;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\ElementInterface;

/**
 * Utility class for improving SVG accessibility.
 *
 * This helper provides methods to add ARIA attributes, semantic structure,
 * and accessibility features to SVG documents, ensuring WCAG compliance
 * and better screen reader support.
 */
final class Accessibility
{
    /**
     * Sets the document-level title.
     *
     * Adds or updates a <title> element as the first child of the root SVG element.
     * This provides a high-level description of the entire document for screen readers.
     *
     * @param Document $document The SVG document
     * @param string   $title    The title text
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setTitle(Document $document, string $title): Document
    {
        $root = $document->getRootElement();

        if (null === $root) {
            return $document;
        }

        // Find existing title element
        $existingTitle = null;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof TitleElement) {
                $existingTitle = $child;
                break;
            }
        }

        if ($existingTitle) {
            $existingTitle->setContent($title);
        } else {
            $titleElement = new TitleElement();
            $titleElement->setContent($title);
            $root->appendChild($titleElement);
        }

        return $document;
    }

    /**
     * Sets the document-level description.
     *
     * Adds or updates a <desc> element as a child of the root SVG element.
     * This provides a detailed description of the document for screen readers.
     *
     * @param Document $document    The SVG document
     * @param string   $description The description text
     *
     * @return Document The modified document (for method chaining)
     */
    public static function setDescription(Document $document, string $description): Document
    {
        $root = $document->getRootElement();

        if (null === $root) {
            return $document;
        }

        // Find existing desc element
        $existingDesc = null;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DescElement) {
                $existingDesc = $child;
                break;
            }
        }

        if ($existingDesc) {
            $existingDesc->setContent($description);
        } else {
            $descElement = new DescElement();
            $descElement->setContent($description);

            // Insert after title if it exists
            $titleIndex = null;
            $children = $root->getChildren();
            foreach ($children as $i => $child) {
                if ($child instanceof TitleElement) {
                    $titleIndex = $i;
                    break;
                }
            }

            $root->appendChild($descElement);
        }

        return $document;
    }

    /**
     * Adds a title element to a specific element.
     *
     * @param ElementInterface $element The element to add a title to
     * @param string           $title   The title text
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function addTitle(ElementInterface $element, string $title): ElementInterface
    {
        if (!$element instanceof ContainerElementInterface) {
            // Element doesn't support children, use aria-label instead
            return self::setAriaLabel($element, $title);
        }

        // Find existing title element
        $existingTitle = null;
        foreach ($element->getChildren() as $child) {
            if ($child instanceof TitleElement) {
                $existingTitle = $child;
                break;
            }
        }

        if ($existingTitle) {
            $existingTitle->setContent($title);
        } else {
            $titleElement = new TitleElement();
            $titleElement->setContent($title);
            $element->appendChild($titleElement);
        }

        return $element;
    }

    /**
     * Adds a description element to a specific element.
     *
     * @param ElementInterface $element     The element to add a description to
     * @param string           $description The description text
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function addDescription(ElementInterface $element, string $description): ElementInterface
    {
        if (!$element instanceof ContainerElementInterface) {
            // Element doesn't support children
            return $element;
        }

        // Find existing desc element
        $existingDesc = null;
        foreach ($element->getChildren() as $child) {
            if ($child instanceof DescElement) {
                $existingDesc = $child;
                break;
            }
        }

        if ($existingDesc) {
            $existingDesc->setContent($description);
        } else {
            $descElement = new DescElement();
            $descElement->setContent($description);

            // Insert after title if it exists
            $titleIndex = null;
            $children = $element->getChildren();
            foreach ($children as $i => $child) {
                if ($child instanceof TitleElement) {
                    $titleIndex = $i;
                    break;
                }
            }

            $element->appendChild($descElement);
        }

        return $element;
    }

    /**
     * Sets the aria-label attribute on an element.
     *
     * @param ElementInterface $element The element
     * @param string           $label   The ARIA label text
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function setAriaLabel(ElementInterface $element, string $label): ElementInterface
    {
        $element->setAttribute('aria-label', $label);

        return $element;
    }

    /**
     * Sets the role attribute on an element.
     *
     * @param ElementInterface $element The element
     * @param string           $role    The ARIA role (e.g., 'img', 'button', 'presentation')
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function setAriaRole(ElementInterface $element, string $role): ElementInterface
    {
        $element->setAttribute('role', $role);

        return $element;
    }

    /**
     * Makes an element focusable or not focusable.
     *
     * @param ElementInterface $element   The element
     * @param bool             $focusable Whether the element should be focusable
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function setFocusable(ElementInterface $element, bool $focusable): ElementInterface
    {
        $element->setAttribute('focusable', $focusable ? 'true' : 'false');

        return $element;
    }

    /**
     * Sets the tabindex attribute on an element for keyboard navigation.
     *
     * @param ElementInterface $element The element
     * @param int              $index   The tab index (-1 to exclude from tab order, 0 for natural order, >0 for explicit order)
     *
     * @return ElementInterface The modified element (for method chaining)
     */
    public static function setTabIndex(ElementInterface $element, int $index): ElementInterface
    {
        $element->setAttribute('tabindex', (string) $index);

        return $element;
    }

    /**
     * Checks a document for common accessibility issues.
     *
     * Returns an array of issues found, each with a severity level and message.
     *
     * @param Document $document The SVG document to check
     *
     * @return array<array{severity: string, message: string, element?: string}> Array of accessibility issues
     */
    public static function checkAccessibility(Document $document): array
    {
        $issues = [];
        $root = $document->getRootElement();

        if (null === $root) {
            return $issues;
        }

        // Check for document-level title
        $hasTitle = false;
        foreach ($root->getChildren() as $child) {
            if ($child instanceof TitleElement) {
                $hasTitle = true;
                break;
            }
        }

        if (!$hasTitle) {
            $issues[] = [
                'severity' => 'warning',
                'message' => 'Document is missing a <title> element for screen readers',
            ];
        }

        // Check all elements for accessibility issues
        self::checkElementAccessibility($root, $issues);

        return $issues;
    }

    /**
     * Recursively checks elements for accessibility issues.
     *
     * @param ElementInterface                                                  $element The element to check
     * @param array<array{severity: string, message: string, element?: string}> &$issues Reference to issues array
     */
    private static function checkElementAccessibility(ElementInterface $element, array &$issues): void
    {
        $tagName = $element->getTagName();

        // Check images (svg, image, use elements) for text alternatives
        if (in_array($tagName, ['image', 'use'], true)) {
            $hasTextAlternative = false;

            // Check for aria-label
            if ($element->hasAttribute('aria-label')) {
                $hasTextAlternative = true;
            }

            // Check for title child
            if ($element instanceof ContainerElementInterface) {
                foreach ($element->getChildren() as $child) {
                    if ($child instanceof TitleElement) {
                        $hasTextAlternative = true;
                        break;
                    }
                }
            }

            if (!$hasTextAlternative) {
                $id = $element->hasAttribute('id') ? $element->getAttribute('id') : 'unknown';
                $issues[] = [
                    'severity' => 'error',
                    'message' => 'Image element without text alternative (aria-label or <title>)',
                    'element' => "{$tagName}#{$id}",
                ];
            }

            // Check for role attribute
            if (!$element->hasAttribute('role')) {
                $id = $element->hasAttribute('id') ? $element->getAttribute('id') : 'unknown';
                $issues[] = [
                    'severity' => 'warning',
                    'message' => "Image element should have role='img' for screen readers",
                    'element' => "{$tagName}#{$id}",
                ];
            }
        }

        // Check interactive elements for keyboard accessibility
        if (in_array($tagName, ['a', 'button'], true) || $element->hasAttribute('onclick')) {
            if (!$element->hasAttribute('tabindex') && !$element->hasAttribute('focusable')) {
                $id = $element->hasAttribute('id') ? $element->getAttribute('id') : 'unknown';
                $issues[] = [
                    'severity' => 'warning',
                    'message' => 'Interactive element should be keyboard accessible (add tabindex or focusable)',
                    'element' => "{$tagName}#{$id}",
                ];
            }
        }

        // Recurse into children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                self::checkElementAccessibility($child, $issues);
            }
        }
    }

    /**
     * Automatically improves accessibility by fixing common issues.
     *
     * Available options:
     * - add_missing_titles: Adds default titles to documents without them
     * - add_role_attributes: Adds role="img" to image elements
     * - ensure_focusable: Makes interactive elements keyboard accessible
     *
     * @param Document             $document The SVG document to improve
     * @param array<string, mixed> $options  Options for improvement
     *
     * @return Document The modified document (for method chaining)
     */
    public static function improveAccessibility(Document $document, array $options = []): Document
    {
        $defaultOptions = [
            'add_missing_titles' => true,
            'add_role_attributes' => true,
            'ensure_focusable' => true,
        ];

        $options = array_merge($defaultOptions, $options);
        $root = $document->getRootElement();

        if (null === $root) {
            return $document;
        }

        // Add missing document title
        if ($options['add_missing_titles']) {
            $hasTitle = false;
            foreach ($root->getChildren() as $child) {
                if ($child instanceof TitleElement) {
                    $hasTitle = true;
                    break;
                }
            }

            if (!$hasTitle) {
                self::setTitle($document, 'SVG Graphic');
            }
        }

        // Improve elements
        self::improveElementAccessibility($root, $options);

        return $document;
    }

    /**
     * Recursively improves element accessibility.
     *
     * @param ElementInterface     $element The element to improve
     * @param array<string, mixed> $options Options for improvement
     */
    private static function improveElementAccessibility(ElementInterface $element, array $options): void
    {
        $tagName = $element->getTagName();

        // Add role to image elements
        if ($options['add_role_attributes']) {
            if (in_array($tagName, ['image', 'use'], true)) {
                if (!$element->hasAttribute('role')) {
                    self::setAriaRole($element, 'img');
                }
            }
        }

        // Make interactive elements focusable
        if ($options['ensure_focusable']) {
            if (in_array($tagName, ['a', 'button'], true) || $element->hasAttribute('onclick')) {
                if (!$element->hasAttribute('tabindex') && !$element->hasAttribute('focusable')) {
                    self::setTabIndex($element, 0);
                }
            }
        }

        // Recurse into children
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                self::improveElementAccessibility($child, $options);
            }
        }
    }
}
