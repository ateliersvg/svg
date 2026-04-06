<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer;

use Atelier\Svg\Document;
use Atelier\Svg\Sanitizer\Pass\RemoveEventHandlersPass;
use Atelier\Svg\Sanitizer\Pass\RemoveForeignObjectPass;
use Atelier\Svg\Sanitizer\Pass\RemoveJavascriptUrlsPass;
use Atelier\Svg\Sanitizer\Pass\RemoveScriptElementsPass;
use Atelier\Svg\Sanitizer\Pass\SanitizerPassInterface;

final readonly class Sanitizer
{
    /**
     * @param list<SanitizerPassInterface> $passes
     */
    public function __construct(private array $passes)
    {
    }

    public function sanitize(Document $document): void
    {
        foreach ($this->passes as $pass) {
            $pass->sanitize($document);
        }
    }

    /**
     * @return list<SanitizerPassInterface>
     */
    public function getPasses(): array
    {
        return $this->passes;
    }

    /**
     * Strict: removes scripts, event handlers, JS URLs, foreignObject, and style elements.
     */
    public static function strict(): self
    {
        return new self([
            new RemoveScriptElementsPass(),
            new RemoveEventHandlersPass(),
            new RemoveJavascriptUrlsPass(),
            new RemoveForeignObjectPass(),
        ]);
    }

    /**
     * Default: removes scripts, event handlers, and JS URLs.
     */
    public static function default(): self
    {
        return new self([
            new RemoveScriptElementsPass(),
            new RemoveEventHandlersPass(),
            new RemoveJavascriptUrlsPass(),
        ]);
    }

    /**
     * Permissive: only removes scripts and JS URLs.
     */
    public static function permissive(): self
    {
        return new self([
            new RemoveScriptElementsPass(),
            new RemoveJavascriptUrlsPass(),
        ]);
    }
}
