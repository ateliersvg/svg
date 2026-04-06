<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Builder;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\SymbolElement;
use Atelier\Svg\Element\Structural\SymbolLibrary;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Exception\RuntimeException;

/**
 * Utility class for creating and managing SVG symbols.
 *
 * Symbols allow you to define reusable graphical elements that can be
 * instantiated multiple times with the <use> element.
 *
 * Example usage:
 * ```php
 * // Create a symbol
 * $symbol = SymbolHelper::createSymbol($doc, 'icon-home', '0 0 24 24');
 * $symbol->appendChild($pathElement);
 *
 * // Use the symbol
 * SymbolHelper::useSymbol($doc, 'icon-home', 10, 10);
 *
 * // Symbol library
 * $library = new SymbolLibrary();
 * $library->add('home', $homeIcon);
 * $library->add('user', $userIcon);
 * SymbolHelper::importLibrary($doc, $library);
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/struct.html#SymbolElement
 */
final class SymbolBuilder
{
    /**
     * Create a new symbol and add it to defs.
     *
     * @param Document    $document The document
     * @param string      $id       Symbol ID
     * @param string|null $viewBox  Optional viewBox
     */
    public static function createSymbol(
        Document $document,
        string $id,
        ?string $viewBox = null,
    ): SymbolElement {
        $symbol = new SymbolElement();
        $symbol->setId($id);

        if (null !== $viewBox) {
            $symbol->setViewbox($viewBox);
        }

        $defs = self::getOrCreateDefs($document);
        $defs->appendChild($symbol);

        // Register the symbol ID with the document
        try {
            $document->registerElementId($id, $symbol);
        } catch (\Exception) {
            // ID might already exist, which is fine
        }

        return $symbol;
    }

    /**
     * Use a symbol at a specific position.
     *
     * @param Document   $document The document
     * @param string     $symbolId Symbol ID to reference
     * @param float      $x        X position
     * @param float      $y        Y position
     * @param float|null $width    Optional width
     * @param float|null $height   Optional height
     */
    public static function useSymbol(
        Document $document,
        string $symbolId,
        float $x = 0,
        float $y = 0,
        ?float $width = null,
        ?float $height = null,
    ): UseElement {
        $use = new UseElement();
        $use->setHref("#$symbolId");
        $use->setX($x);
        $use->setY($y);

        if (null !== $width) {
            $use->setWidth($width);
        }

        if (null !== $height) {
            $use->setHeight($height);
        }

        $root = $document->getRootElement();
        if (null !== $root) {
            $root->appendChild($use);
        }

        return $use;
    }

    /**
     * Check if a symbol exists in the document.
     *
     * @param Document $document The document
     * @param string   $id       Symbol ID
     */
    public static function symbolExists(Document $document, string $id): bool
    {
        return $document->getElementById($id) instanceof SymbolElement;
    }

    /**
     * Get a symbol by ID.
     *
     * @param Document $document The document
     * @param string   $id       Symbol ID
     */
    public static function getSymbol(Document $document, string $id): ?SymbolElement
    {
        $element = $document->getElementById($id);

        return $element instanceof SymbolElement ? $element : null;
    }

    /**
     * Import a symbol library into the document.
     *
     * @param Document      $document The document
     * @param SymbolLibrary $library  The symbol library
     */
    public static function importLibrary(Document $document, SymbolLibrary $library): void
    {
        $defs = self::getOrCreateDefs($document);

        foreach ($library->getSymbols() as $id => $symbol) {
            $defs->appendChild($symbol);

            // Register symbol ID
            if ($symbol->getId() === $id) {
                try {
                    $document->registerElementId($id, $symbol);
                } catch (\Exception) {
                    // Ignore duplicates
                }
            }
        }
    }

    /**
     * Get or create the defs element.
     */
    private static function getOrCreateDefs(Document $document): DefsElement
    {
        $root = $document->getRootElement();
        if (null === $root) {
            throw new RuntimeException('Document has no root element');
        }

        // Look for existing defs
        foreach ($root->getChildren() as $child) {
            if ($child instanceof DefsElement) {
                return $child;
            }
        }

        // Create new defs as first child
        $defs = new DefsElement();
        $root->prependChild($defs);

        return $defs;
    }
}
