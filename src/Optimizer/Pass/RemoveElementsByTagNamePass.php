<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that removes elements by their tag names.
 *
 * This is a flexible pass that can remove any elements matching specified tag names.
 * It consolidates the functionality of RemoveDescPass, RemoveTitlePass, and similar
 * passes into a single configurable class.
 *
 * Example:
 * ```php
 * // Remove desc and title elements
 * $pass = new RemoveElementsByTagNamePass(['desc', 'title']);
 *
 * // Remove only metadata elements
 * $pass = new RemoveElementsByTagNamePass(['metadata']);
 * ```
 */
final readonly class RemoveElementsByTagNamePass implements OptimizerPassInterface
{
    /** @var list<string> */
    private array $tagNames;

    private string $name;

    /**
     * Creates a new RemoveElementsByTagNamePass.
     *
     * @param list<string> $tagNames The tag names of elements to remove
     * @param string|null  $name     Optional custom name for this pass (auto-generated if null)
     */
    public function __construct(array $tagNames, ?string $name = null)
    {
        $this->tagNames = array_values(array_map(strtolower(...), $tagNames));
        $this->name = $name ?? 'remove-'.implode('-', $this->tagNames);
    }

    /**
     * Creates a pass to remove desc elements.
     */
    public static function removeDesc(): self
    {
        return new self(['desc'], 'remove-desc');
    }

    /**
     * Creates a pass to remove title elements.
     */
    public static function removeTitle(): self
    {
        return new self(['title'], 'remove-title');
    }

    /**
     * Creates a pass to remove metadata elements.
     */
    public static function removeMetadata(): self
    {
        return new self(['metadata'], 'remove-metadata-elements');
    }

    /**
     * Creates a pass to remove all descriptive elements (desc, title, metadata).
     */
    public static function removeAllDescriptive(): self
    {
        return new self(['desc', 'title', 'metadata'], 'remove-descriptive');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    private function processElement(ElementInterface $element): void
    {
        if (!$element instanceof ContainerElementInterface) {
            return;
        }

        $childrenToRemove = [];

        foreach ($element->getChildren() as $child) {
            if ($this->shouldRemove($child)) {
                $childrenToRemove[] = $child;
            } else {
                // Recursively process children
                $this->processElement($child);
            }
        }

        foreach ($childrenToRemove as $child) {
            $element->removeChild($child);
        }
    }

    private function shouldRemove(ElementInterface $element): bool
    {
        return in_array(strtolower($element->getTagName()), $this->tagNames, true);
    }
}
