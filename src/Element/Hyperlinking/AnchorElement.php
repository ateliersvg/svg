<?php

declare(strict_types=1);

namespace Atelier\Svg\Element\Hyperlinking;

use Atelier\Svg\Element\AbstractContainerElement;

final class AnchorElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('a');
    }

    public function setHref(string $href): static
    {
        $this->setAttribute('href', $href);

        return $this;
    }

    public function getHref(): ?string
    {
        return $this->getAttribute('href');
    }

    public function setTarget(string $target): static
    {
        $this->setAttribute('target', $target);

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->getAttribute('target');
    }
}
