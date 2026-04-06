<?php

declare(strict_types=1);

namespace Atelier\Svg\Optimizer\Util;

/**
 * Canonical lists of SVG attributes that contain numeric values.
 *
 * Provides a single source of truth for the attribute lists previously
 * duplicated across RoundValuesPass and CleanupNumericValuesPass.
 *
 * Attributes are grouped by SVG context so that passes can apply
 * different precision levels per category.
 */
final class NumericAttributes
{
    /** @var list<string> Coordinate attributes (positions). */
    public const array COORDINATES = [
        'x', 'y',
        'x1', 'y1', 'x2', 'y2',
        'cx', 'cy',
        'fx', 'fy',
        'dx', 'dy',
    ];

    /** @var list<string> Dimension attributes (sizes). */
    public const array DIMENSIONS = [
        'width', 'height',
        'r', 'rx', 'ry',
    ];

    /** @var list<string> Opacity-related attributes (values in [0, 1]). */
    public const array OPACITY = [
        'opacity',
        'fill-opacity',
        'stroke-opacity',
        'stop-opacity',
    ];

    /** @var list<string> Stroke-related numeric attributes. */
    public const array STROKE = [
        'stroke-width',
        'stroke-miterlimit',
        'stroke-dashoffset',
    ];

    /** @var list<string> Typography-related numeric attributes. */
    public const array TYPOGRAPHY = [
        'font-size',
        'letter-spacing',
        'word-spacing',
        'baseline-shift',
        'kerning',
    ];

    /** @var list<string> Other numeric attributes. */
    public const array OTHER = [
        'offset',
    ];

    /**
     * All numeric attributes (union of every group).
     *
     * @var list<string>
     */
    public const array ALL = [
        // Coordinates
        'x', 'y',
        'x1', 'y1', 'x2', 'y2',
        'cx', 'cy',
        'fx', 'fy',
        'dx', 'dy',
        // Dimensions
        'width', 'height',
        'r', 'rx', 'ry',
        // Opacity
        'opacity',
        'fill-opacity',
        'stroke-opacity',
        'stop-opacity',
        // Stroke
        'stroke-width',
        'stroke-miterlimit',
        'stroke-dashoffset',
        // Typography
        'font-size',
        'letter-spacing',
        'word-spacing',
        'baseline-shift',
        'kerning',
        // Other
        'offset',
    ];

    /**
     * Attributes that are safe to round aggressively.
     *
     * This is the subset historically used by RoundValuesPass: coordinates,
     * dimensions, and a few stroke/font attributes. It intentionally
     * excludes opacity (handled with dedicated precision) and the more
     * exotic typography attributes.
     *
     * @var list<string>
     */
    public const array ROUNDABLE = [
        'x', 'y',
        'x1', 'y1', 'x2', 'y2',
        'cx', 'cy',
        'dx', 'dy',
        'width', 'height',
        'r', 'rx', 'ry',
        'stroke-width',
        'stroke-dashoffset',
        'font-size',
        'offset',
    ];
}
