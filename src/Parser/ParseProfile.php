<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser;

/**
 * Defines parsing behavior profiles for SVG parsing.
 *
 * The profile determines how the parser handles errors, warnings, and edge cases:
 *
 * - STRICT: Surfaces all parse/validation errors with locations. Best for development
 *   and validation workflows where you want to catch all issues.
 *
 * - LENIENT: Records warnings but preserves input when possible. Best for real-world
 *   SVGs that may have minor issues but should still be processed.
 */
enum ParseProfile: string
{
    /**
     * Strict mode surfaces all parse/validation errors with locations.
     *
     * Use this mode when:
     * - Developing and testing SVG generation
     * - Validating SVGs against the specification
     * - Processing SVGs that should be spec-compliant
     */
    case STRICT = 'strict';

    /**
     * Lenient mode records warnings and preserves input when possible.
     *
     * Use this mode when:
     * - Processing real-world SVGs that may have minor issues
     * - Importing SVGs from external sources
     * - Maximum compatibility is more important than strict compliance
     */
    case LENIENT = 'lenient';

    /**
     * Determines if the profile should throw exceptions on parse errors.
     */
    public function shouldThrowOnError(): bool
    {
        return match ($this) {
            self::STRICT => true,
            self::LENIENT => false,
        };
    }

    /**
     * Determines if the profile should preserve unknown elements/attributes.
     */
    public function shouldPreserveUnknown(): bool
    {
        return match ($this) {
            self::STRICT => false,
            self::LENIENT => true,
        };
    }

    /**
     * Determines if the profile should warn about deprecated features.
     */
    public function shouldWarnOnDeprecated(): bool
    {
        return true; // Both profiles warn on deprecated features
    }
}
