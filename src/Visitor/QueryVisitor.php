<?php

declare(strict_types=1);

namespace Atelier\Svg\Visitor;

use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Selector\SelectorMatcher;

/**
 * Visitor that finds elements matching a selector.
 *
 * Traverses the element tree and collects elements that match the given selector.
 */
final class QueryVisitor extends AbstractVisitor
{
    /** @var array<ElementInterface> */
    private array $matches = [];

    private bool $foundFirst = false;

    public function __construct(
        private readonly string $selector,
        private readonly SelectorMatcher $matcher,
        private readonly bool $findFirst = false,
    ) {
    }

    /**
     * Visits an element and checks if it matches the selector.
     */
    protected function doVisit(ElementInterface $element): mixed
    {
        // If we're only looking for the first match and we found it, stop
        if ($this->findFirst && $this->foundFirst) {
            return null;
        }

        // Check if this element matches the selector
        if ($this->matcher->matches($element, $this->selector)) {
            $this->matches[] = $element;

            if ($this->findFirst) {
                $this->foundFirst = true;
            }
        }

        return null;
    }

    /**
     * Gets all matched elements.
     *
     * @return array<ElementInterface>
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * Gets the first matched element.
     */
    public function getFirstMatch(): ?ElementInterface
    {
        return $this->matches[0] ?? null;
    }

    /**
     * Checks if any matches were found.
     */
    public function hasMatches(): bool
    {
        return !empty($this->matches);
    }

    /**
     * Gets the number of matches.
     */
    public function getMatchCount(): int
    {
        return count($this->matches);
    }
}
