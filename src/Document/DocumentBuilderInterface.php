<?php

declare(strict_types=1);

namespace Atelier\Svg\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\BuilderInterface;

/**
 * Interface for builders that produce a full SVG document.
 */
interface DocumentBuilderInterface extends BuilderInterface
{
    /**
     * Gets the built SVG document.
     */
    public function getSvgDocument(): Document;
}
