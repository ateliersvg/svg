<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

/**
 * SVG attribute name constants and helpers.
 *
 * This class provides constants for common SVG attributes and helper methods
 * for attribute validation and normalization.
 *
 * @see https://www.w3.org/TR/SVG11/attindex.html
 */
final class Attributes
{
    // Core attributes
    public const string ID = 'id';
    public const string CLASS_NAME = 'class';
    public const string STYLE = 'style';
    public const string LANG = 'lang';
    public const string XML_LANG = 'xml:lang';

    // Presentation attributes
    public const string FILL = 'fill';
    public const string FILL_OPACITY = 'fill-opacity';
    public const string FILL_RULE = 'fill-rule';
    public const string STROKE = 'stroke';
    public const string STROKE_WIDTH = 'stroke-width';
    public const string STROKE_OPACITY = 'stroke-opacity';
    public const string STROKE_LINECAP = 'stroke-linecap';
    public const string STROKE_LINEJOIN = 'stroke-linejoin';
    public const string STROKE_DASHARRAY = 'stroke-dasharray';
    public const string STROKE_DASHOFFSET = 'stroke-dashoffset';
    public const string OPACITY = 'opacity';
    public const string FONT_FAMILY = 'font-family';
    public const string FONT_SIZE = 'font-size';
    public const string FONT_WEIGHT = 'font-weight';
    public const string FONT_STYLE = 'font-style';
    public const string TEXT_ANCHOR = 'text-anchor';
    public const string TEXT_DECORATION = 'text-decoration';

    // Transform
    public const string TRANSFORM = 'transform';

    // Geometric attributes
    public const string X = 'x';
    public const string Y = 'y';
    public const string WIDTH = 'width';
    public const string HEIGHT = 'height';
    public const string CX = 'cx';
    public const string CY = 'cy';
    public const string R = 'r';
    public const string RX = 'rx';
    public const string RY = 'ry';
    public const string X1 = 'x1';
    public const string Y1 = 'y1';
    public const string X2 = 'x2';
    public const string Y2 = 'y2';
    public const string POINTS = 'points';
    public const string D = 'd';

    // SVG-specific
    public const string VIEWBOX = 'viewBox';
    public const string PRESERVE_ASPECT_RATIO = 'preserveAspectRatio';
    public const string XMLNS = 'xmlns';
    public const string VERSION = 'version';

    // Links
    public const string HREF = 'href';
    public const string XLINK_HREF = 'xlink:href';

    // Gradient attributes
    public const string GRADIENT_UNITS = 'gradientUnits';
    public const string GRADIENT_TRANSFORM = 'gradientTransform';
    public const string SPREAD_METHOD = 'spreadMethod';
    public const string OFFSET = 'offset';
    public const string STOP_COLOR = 'stop-color';
    public const string STOP_OPACITY = 'stop-opacity';

    // Filter attributes
    public const string FILTER = 'filter';
    public const string FILTER_UNITS = 'filterUnits';
    public const string IN = 'in';
    public const string RESULT = 'result';
    public const string STD_DEVIATION = 'stdDeviation';

    // Clipping attributes
    public const string CLIP_PATH = 'clip-path';
    public const string CLIP_PATH_UNITS = 'clipPathUnits';
    public const string MASK = 'mask';
    public const string MASK_UNITS = 'maskUnits';
    public const string MASK_CONTENT_UNITS = 'maskContentUnits';

    // Pattern attributes
    public const string PATTERN_UNITS = 'patternUnits';
    public const string PATTERN_CONTENT_UNITS = 'patternContentUnits';
    public const string PATTERN_TRANSFORM = 'patternTransform';

    // Marker attributes
    public const string MARKER_START = 'marker-start';
    public const string MARKER_MID = 'marker-mid';
    public const string MARKER_END = 'marker-end';
    public const string MARKER_UNITS = 'markerUnits';
    public const string MARKER_WIDTH = 'markerWidth';
    public const string MARKER_HEIGHT = 'markerHeight';
    public const string REF_X = 'refX';
    public const string REF_Y = 'refY';
    public const string ORIENT = 'orient';

    // Text attributes
    public const string DX = 'dx';
    public const string DY = 'dy';
    public const string ROTATE = 'rotate';
    public const string TEXT_LENGTH = 'textLength';
    public const string LENGTH_ADJUST = 'lengthAdjust';
    public const string START_OFFSET = 'startOffset';

    // Other common attributes
    public const string VISIBILITY = 'visibility';
    public const string DISPLAY = 'display';
    public const string OVERFLOW = 'overflow';

    /**
     * Checks if an attribute name is a presentation attribute.
     */
    public static function isPresentationAttribute(string $name): bool
    {
        return in_array($name, [
            self::FILL,
            self::FILL_OPACITY,
            self::FILL_RULE,
            self::STROKE,
            self::STROKE_WIDTH,
            self::STROKE_OPACITY,
            self::STROKE_LINECAP,
            self::STROKE_LINEJOIN,
            self::STROKE_DASHARRAY,
            self::STROKE_DASHOFFSET,
            self::OPACITY,
            self::FONT_FAMILY,
            self::FONT_SIZE,
            self::FONT_WEIGHT,
            self::FONT_STYLE,
            self::TEXT_ANCHOR,
            self::TEXT_DECORATION,
            self::VISIBILITY,
            self::DISPLAY,
        ], true);
    }

    /**
     * Normalizes an attribute name.
     * Converts camelCase to kebab-case for SVG compatibility.
     */
    public static function normalize(string $name): string
    {
        // Convert camelCase to kebab-case
        $kebab = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);

        return strtolower($kebab ?? $name);
    }

    /**
     * Checks if an attribute is a geometric attribute (position/size).
     */
    public static function isGeometricAttribute(string $name): bool
    {
        return in_array($name, [
            self::X,
            self::Y,
            self::WIDTH,
            self::HEIGHT,
            self::CX,
            self::CY,
            self::R,
            self::RX,
            self::RY,
            self::X1,
            self::Y1,
            self::X2,
            self::Y2,
        ], true);
    }
}
