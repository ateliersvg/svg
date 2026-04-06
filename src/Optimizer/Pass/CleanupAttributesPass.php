<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Pass;

use Atelier\Svg\Element\ElementInterface;

/**
 * Optimization pass that cleans up attribute values.
 *
 * This pass performs various attribute value cleanup operations:
 * - Trim leading and trailing whitespace
 * - Remove duplicate spaces in class names
 * - Normalize whitespace in points lists (polygon, polyline)
 * - Normalize whitespace in path data
 * - Remove trailing zeros in transform matrices and numeric values
 */
final class CleanupAttributesPass extends AbstractOptimizerPass
{
    /**
     * Gets the name of this optimization pass.
     */
    public function getName(): string
    {
        return 'cleanup-attributes';
    }

    /**
     * Processes an element to clean up attributes.
     *
     * @param ElementInterface $element The element to process
     */
    protected function processElement(ElementInterface $element): void
    {
        // Clean up attributes on this element
        $this->cleanupAttributes($element);
    }

    /**
     * Cleans up all attributes on an element.
     *
     * @param ElementInterface $element The element to process
     */
    private function cleanupAttributes(ElementInterface $element): void
    {
        $attributes = $element->getAttributes();

        foreach ($attributes as $name => $value) {
            $cleanedValue = $this->cleanupAttributeValue($name, $value);

            if ($cleanedValue !== $value) {
                $element->setAttribute($name, $cleanedValue);
            }
        }
    }

    /**
     * Cleans up a single attribute value based on the attribute name.
     *
     * @param string $name  The attribute name
     * @param string $value The attribute value
     *
     * @return string The cleaned value
     */
    private function cleanupAttributeValue(string $name, string $value): string
    {
        // Trim all attribute values
        $value = trim($value);

        // Special handling for specific attributes
        return match ($name) {
            'class' => $this->cleanupClassAttribute($value),
            'points' => $this->cleanupPointsAttribute($value),
            'd' => $this->cleanupPathDataAttribute($value),
            'transform' => $this->cleanupTransformAttribute($value),
            'viewBox' => $this->cleanupViewBoxAttribute($value),
            default => $value,
        };
    }

    /**
     * Cleans up class attribute values by removing duplicate spaces.
     *
     * @param string $value The class attribute value
     *
     * @return string The cleaned value
     */
    private function cleanupClassAttribute(string $value): string
    {
        // Split on whitespace, filter empty, and rejoin with single spaces
        $classes = preg_split('/\s+/', $value);
        assert(false !== $classes);
        $classes = array_filter($classes);

        return implode(' ', $classes);
    }

    /**
     * Cleans up points attribute (for polygon/polyline).
     *
     * @param string $value The points attribute value
     *
     * @return string The cleaned value
     */
    private function cleanupPointsAttribute(string $value): string
    {
        // Normalize whitespace and commas
        // Replace multiple spaces/commas with single space
        $value = (string) preg_replace('/[\s,]+/', ' ', $value);
        $value = trim($value);

        // Remove trailing zeros from numbers
        $value = $this->removeTrailingZeros($value);

        return $value;
    }

    /**
     * Cleans up path data attribute.
     *
     * @param string $value The path data
     *
     * @return string The cleaned value
     */
    private function cleanupPathDataAttribute(string $value): string
    {
        // Normalize whitespace around path commands and numbers
        // Replace multiple spaces/commas with single space
        $value = (string) preg_replace('/[\s,]+/', ' ', $value);

        // Add space before path commands when they follow a number
        $value = (string) preg_replace('/([0-9])([MLHVCSQTAZmlhvcsqtaz])/', '$1 $2', $value);

        // Add space after path commands when they're followed by a number or minus
        $value = (string) preg_replace('/([MLHVCSQTAZmlhvcsqtaz])([0-9-])/', '$1 $2', $value);

        $value = trim($value);

        // Remove trailing zeros from numbers
        $value = $this->removeTrailingZeros($value);

        return $value;
    }

    /**
     * Cleans up transform attribute values.
     *
     * @param string $value The transform attribute value
     *
     * @return string The cleaned value
     */
    private function cleanupTransformAttribute(string $value): string
    {
        // Normalize whitespace
        $value = (string) preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        // Remove trailing zeros from numbers
        $value = $this->removeTrailingZeros($value);

        return $value;
    }

    /**
     * Cleans up viewBox attribute values.
     *
     * @param string $value The viewBox attribute value
     *
     * @return string The cleaned value
     */
    private function cleanupViewBoxAttribute(string $value): string
    {
        // Normalize whitespace
        $value = (string) preg_replace('/[\s,]+/', ' ', $value);
        $value = trim($value);

        // Remove trailing zeros from numbers
        $value = $this->removeTrailingZeros($value);

        return $value;
    }

    /**
     * Removes trailing zeros from decimal numbers in a string.
     *
     * Examples:
     * - "1.000" -> "1"
     * - "2.500" -> "2.5"
     * - "0.100" -> "0.1"
     * - "42" -> "42" (unchanged)
     *
     * @param string $value The string containing numbers
     *
     * @return string The string with trailing zeros removed
     */
    private function removeTrailingZeros(string $value): string
    {
        // Match decimal numbers and remove trailing zeros
        return (string) preg_replace_callback(
            '/\d+\.\d+/',
            function ($matches) {
                $number = rtrim($matches[0], '0');
                // If all decimals were zeros, remove the decimal point too
                $number = rtrim($number, '.');

                return $number;
            },
            $value
        );
    }
}
