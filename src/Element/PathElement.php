<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\PathParser;

/**
 * Represents an SVG <path> element.
 *
 * The <path> element is used to define a path consisting of lines, curves, arcs, etc.
 * The path is defined by the 'd' attribute which contains path commands and coordinates.
 * Path elements can contain child elements such as animate elements for animations.
 */
final class PathElement extends AbstractContainerElement
{
    public function __construct()
    {
        parent::__construct('path');
    }

    /**
     * Sets the path data using a raw string.
     *
     * @param string $pathData The raw 'd' attribute string
     */
    public function setPathData(string $pathData): static
    {
        $this->setAttribute('d', $pathData);

        return $this;
    }

    /**
     * Sets the path data using a raw string (alias for setPathData).
     *
     * This method matches the SVG 'd' attribute name for improved discoverability.
     *
     * @param string $d The raw 'd' attribute string
     */
    public function setD(string $d): static
    {
        return $this->setPathData($d);
    }

    /**
     * Gets the raw path data string.
     *
     * @return string|null The 'd' attribute value, or null if not set
     */
    public function getPathData(): ?string
    {
        return $this->getAttribute('d');
    }

    /**
     * Sets the path data using a Data object.
     *
     * @param Data $data The structured path data
     */
    public function setData(Data $data): static
    {
        $pathString = $this->serializeData($data);
        $this->setAttribute('d', $pathString);

        return $this;
    }

    /**
     * Gets the path data as a Data object.
     *
     * @return Data|null The structured path data, or null if not set
     */
    public function getData(): ?Data
    {
        $pathData = $this->getAttribute('d');

        if (null === $pathData) {
            return null;
        }

        return (new PathParser())->parse($pathData);
    }

    /**
     * Serializes a Data object to a path string.
     *
     * @param Data $data The path data to serialize
     *
     * @return string The serialized path string
     */
    private function serializeData(Data $data): string
    {
        $parts = [];

        foreach ($data->getSegments() as $segment) {
            $command = $segment->getCommand();
            $args = $segment->commandArgumentsToString();

            if ('' !== $args) {
                $parts[] = $command.$args;
            } else {
                $parts[] = $command;
            }
        }

        return implode(' ', $parts);
    }
}
