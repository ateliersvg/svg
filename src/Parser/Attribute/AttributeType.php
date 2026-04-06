<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser\Attribute;

/**
 * Defines the type of an SVG attribute for parsing purposes.
 *
 * This enum categorizes attributes by their value type, which determines
 * how they should be parsed and validated.
 */
enum AttributeType: string
{
    /**
     * String attribute - raw string value, no special parsing.
     */
    case STRING = 'string';

    /**
     * Identifier - typically used for id, class attributes.
     */
    case IDENTIFIER = 'identifier';

    /**
     * Length - numeric value with optional unit (px, em, %, etc.).
     * Examples: "10px", "50%", "1.5em".
     */
    case LENGTH = 'length';

    /**
     * Number - plain numeric value without unit.
     * Examples: "0.5", "100", "-10".
     */
    case NUMBER = 'number';

    /**
     * Percentage - numeric value with % unit.
     * Examples: "50%", "100%".
     */
    case PERCENTAGE = 'percentage';

    /**
     * Length or percentage - can be either a length or percentage.
     */
    case LENGTH_OR_PERCENTAGE = 'length_or_percentage';

    /**
     * Color - named color, hex, rgb(), hsl(), etc.
     * Examples: "red", "#FF0000", "rgb(255, 0, 0)".
     */
    case COLOR = 'color';

    /**
     * Paint - can be a color, url(#id), none, currentColor.
     * Examples: "red", "url(#gradient)", "none".
     */
    case PAINT = 'paint';

    /**
     * Transform - transform function or list.
     * Examples: "translate(10, 20)", "rotate(45) scale(2)".
     */
    case TRANSFORM = 'transform';

    /**
     * Path data - SVG path commands.
     * Examples: "M 10 10 L 90 90 Z".
     */
    case PATH_DATA = 'path_data';

    /**
     * Points list - list of x,y coordinate pairs.
     * Examples: "10,20 30,40 50,60".
     */
    case POINTS = 'points';

    /**
     * IRI reference - URL or fragment reference.
     * Examples: "#myId", "url(#gradient)".
     */
    case IRI = 'iri';

    /**
     * ViewBox - four numbers defining the viewport.
     * Examples: "0 0 100 100".
     */
    case VIEWBOX = 'viewbox';

    /**
     * Preserve aspect ratio value.
     * Examples: "xMidYMid meet", "none".
     */
    case PRESERVE_ASPECT_RATIO = 'preserve_aspect_ratio';

    /**
     * List of numbers separated by whitespace or commas.
     * Examples: "1 2 3 4", "0.5, 1.0, 1.5".
     */
    case NUMBER_LIST = 'number_list';

    /**
     * Dash array for stroke-dasharray.
     * Examples: "5 10", "5, 10, 15".
     */
    case DASH_ARRAY = 'dash_array';

    /**
     * Enumerated value from a predefined set.
     * Examples: visibility: "visible" | "hidden" | "collapse".
     */
    case ENUM = 'enum';

    /**
     * Boolean value (true/false, yes/no).
     */
    case BOOLEAN = 'boolean';

    /**
     * Angle value with optional unit (deg, rad, grad, turn).
     * Examples: "45deg", "1.57rad".
     */
    case ANGLE = 'angle';

    /**
     * Unknown or untyped attribute - preserved as raw string.
     */
    case UNKNOWN = 'unknown';
}
