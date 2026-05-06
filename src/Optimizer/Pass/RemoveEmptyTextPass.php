<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\ContainerElementInterface;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Element\Text\TextElement;
use Atelier\Svg\Element\Text\TspanElement;

/**
 * Removes empty <text> and <tspan> elements.
 *
 * A text element is considered empty when it has no text content and no
 * children with text content. Elements with preserving attributes (id, class,
 * event handlers) are kept by default.
 *
 * Equivalent to SVGO's `removeEmptyText` plugin.
 */
final class RemoveEmptyTextPass implements OptimizerPassInterface
{
    use PreservingAttributesTrait;

    /** @var list<string> */
    private readonly array $preservingAttributes;

    /**
     * @param list<string>|null $preservingAttributes Attributes that prevent removal
     */
    public function __construct(
        ?array $preservingAttributes = null,
    ) {
        $this->preservingAttributes = $preservingAttributes ?? $this->getDefaultPreservingAttributes();
    }

    public function getName(): string
    {
        return 'remove-empty-text';
    }

    public function optimize(Document $document): void
    {
        $rootElement = $document->getRootElement();

        if (null === $rootElement) {
            return;
        }

        $this->processElement($rootElement);
    }

    private function processElement(ContainerElementInterface $element): void
    {
        foreach ($element->getChildren() as $child) {
            if ($child instanceof ContainerElementInterface) {
                $this->processElement($child);
            }
        }

        $toRemove = [];
        foreach ($element->getChildren() as $child) {
            if ($this->shouldRemove($child)) {
                $toRemove[] = $child;
            }
        }

        foreach ($toRemove as $child) {
            $element->removeChild($child);
        }
    }

    private function shouldRemove(ElementInterface $element): bool
    {
        if (!$element instanceof TextElement && !$element instanceof TspanElement) {
            return false;
        }

        if ($this->hasPreservingAttributes($element, $this->preservingAttributes)) {
            return false;
        }

        return $this->isEmptyTextElement($element);
    }

    private function isEmptyTextElement(ElementInterface $element): bool
    {
        // Check for direct text content
        if ($element instanceof TextElement || $element instanceof TspanElement) {
            $content = $element->getTextContent();
            if (null !== $content && '' !== trim($content)) {
                return false;
            }
        }

        // Check children for any text content
        if ($element instanceof ContainerElementInterface) {
            foreach ($element->getChildren() as $child) {
                if (!$this->isEmptyTextElement($child)) {
                    return false;
                }
            }
        }

        return true;
    }
}
