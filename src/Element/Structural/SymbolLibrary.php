<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Structural;

use Atelier\Svg\Element\ElementInterface;

/**
 * Symbol library for managing collections of symbols.
 */
final class SymbolLibrary
{
    /** @var array<string, SymbolElement> */
    private array $symbols = [];

    /**
     * Add a symbol to the library.
     *
     * @param string                         $id     Symbol ID
     * @param SymbolElement|ElementInterface $symbol Symbol element or any element to wrap in a symbol
     */
    public function add(string $id, SymbolElement|ElementInterface $symbol): self
    {
        if (!$symbol instanceof SymbolElement) {
            // Wrap in a symbol
            $symbolEl = new SymbolElement();
            $symbolEl->setId($id);
            $symbolEl->appendChild($symbol);
            $symbol = $symbolEl;
        } else {
            $symbol->setId($id);
        }

        $this->symbols[$id] = $symbol;

        return $this;
    }

    /**
     * Get a symbol from the library.
     */
    public function get(string $id): ?SymbolElement
    {
        return $this->symbols[$id] ?? null;
    }

    /**
     * Check if a symbol exists in the library.
     */
    public function has(string $id): bool
    {
        return isset($this->symbols[$id]);
    }

    /**
     * Remove a symbol from the library.
     */
    public function remove(string $id): self
    {
        unset($this->symbols[$id]);

        return $this;
    }

    /**
     * Get all symbols in the library.
     *
     * @return array<string, SymbolElement>
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    /**
     * Get all symbol IDs.
     *
     * @return array<string>
     */
    public function getIds(): array
    {
        return array_keys($this->symbols);
    }

    /**
     * Clear all symbols from the library.
     */
    public function clear(): self
    {
        $this->symbols = [];

        return $this;
    }

    /**
     * Merge another library into this one.
     */
    public function merge(SymbolLibrary $other): self
    {
        $this->symbols = array_merge($this->symbols, $other->symbols);

        return $this;
    }
}
