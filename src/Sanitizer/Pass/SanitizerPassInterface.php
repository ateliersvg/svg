<?php

declare(strict_types=1);

namespace Atelier\Svg\Sanitizer\Pass;

use Atelier\Svg\Document;

interface SanitizerPassInterface
{
    public function getName(): string;

    public function sanitize(Document $document): void;
}
