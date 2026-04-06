<?php

declare(strict_types=1);

namespace Atelier\Svg\Parser\Attribute;

/**
 * Registry of SVG attribute specifications.
 *
 * This class provides a centralized registry of known SVG attributes and their
 * parsing specifications. It is used by the parser to determine how to handle
 * each attribute and what type of value it should contain.
 *
 * ## Limitations
 *
 * Some SVG attributes are context-dependent and have different types or allowed
 * values depending on which element they appear on. Notable examples:
 *
 * - `type`: Different values for feColorMatrix, feTurbulence, feFuncR/G/B/A, and animateTransform
 * - `operator`: Different values for feComposite vs feMorphology
 * - `fill`: Presentation attribute (paint value) vs animation attribute (freeze/remove)
 * - `dx`/`dy`: LENGTH_OR_PERCENTAGE for text elements, NUMBER for feOffset
 * - `x`/`y`: LENGTH_OR_PERCENTAGE for most elements, NUMBER for light source elements
 *
 * This registry stores the most common/general type for each attribute. Context-aware
 * attribute resolution (based on element type) should be handled by the parser.
 *
 * ## Usage
 *
 * ```php
 * // Check if an attribute is known
 * if (AttributeRegistry::isKnown('fill')) {
 *     $spec = AttributeRegistry::get('fill');
 *     echo $spec->getType()->value; // 'paint'
 * }
 *
 * // Get attribute type
 * $type = AttributeRegistry::getType('transform'); // AttributeType::TRANSFORM
 *
 * // Check for deprecation
 * if (AttributeRegistry::isDeprecated('xlink:href')) {
 *     // Warn user to use 'href' instead
 * }
 *
 * // Register custom attribute
 * AttributeRegistry::register(AttributeSpec::string('data-custom'));
 * ```
 *
 * @see https://www.w3.org/TR/SVG11/attindex.html
 * @see https://www.w3.org/TR/SVG2/attindex.html
 */
final class AttributeRegistry
{
    /**
     * XLink namespace URI.
     */
    public const string XLINK_NAMESPACE = 'http://www.w3.org/1999/xlink';

    /**
     * XML namespace URI.
     */
    public const string XML_NAMESPACE = 'http://www.w3.org/XML/1998/namespace';

    /**
     * Cached attribute specifications.
     *
     * @var array<string, AttributeSpec>|null
     */
    private static ?array $specs = null;

    /**
     * Gets the specification for an attribute by name.
     *
     * @param string $name The attribute name (e.g., "fill", "xlink:href")
     *
     * @return AttributeSpec|null The specification, or null if unknown
     */
    public static function get(string $name): ?AttributeSpec
    {
        self::ensureInitialized();

        return self::$specs[$name] ?? null;
    }

    /**
     * Checks if an attribute is known.
     */
    public static function isKnown(string $name): bool
    {
        self::ensureInitialized();

        return isset(self::$specs[$name]);
    }

    /**
     * Gets the type for an attribute, or UNKNOWN if not registered.
     */
    public static function getType(string $name): AttributeType
    {
        $spec = self::get($name);

        return $spec?->getType() ?? AttributeType::UNKNOWN;
    }

    /**
     * Checks if an attribute is deprecated.
     */
    public static function isDeprecated(string $name): bool
    {
        $spec = self::get($name);

        return $spec?->isDeprecated() ?? false;
    }

    /**
     * Gets all registered attribute specifications.
     *
     * @return array<string, AttributeSpec>
     */
    public static function getAll(): array
    {
        self::ensureInitialized();

        return self::$specs ?? [];
    }

    /**
     * Registers a custom attribute specification.
     *
     * This allows extending the registry with custom or vendor-specific attributes.
     */
    public static function register(AttributeSpec $spec): void
    {
        self::ensureInitialized();
        self::$specs[$spec->getName()] = $spec;
    }

    /**
     * Initializes the attribute specifications if not already done.
     */
    private static function ensureInitialized(): void
    {
        if (null !== self::$specs) {
            return;
        }

        self::$specs = [];

        // Core attributes
        self::addSpec(AttributeSpec::string('id'));
        self::addSpec(AttributeSpec::string('class'));
        self::addSpec(AttributeSpec::string('style'));
        self::addSpec(AttributeSpec::string('lang'));
        self::addSpec(AttributeSpec::namespaced('xml:lang', self::XML_NAMESPACE, AttributeType::STRING));

        // Presentation attributes - fill and stroke
        self::addSpec(AttributeSpec::paint('fill', default: 'black'));
        self::addSpec(AttributeSpec::number('fill-opacity', default: 1.0));
        self::addSpec(AttributeSpec::enum('fill-rule', ['nonzero', 'evenodd'], default: 'nonzero'));
        self::addSpec(AttributeSpec::paint('stroke', default: 'none'));
        self::addSpec(AttributeSpec::length('stroke-width', default: '1'));
        self::addSpec(AttributeSpec::number('stroke-opacity', default: 1.0));
        self::addSpec(AttributeSpec::enum('stroke-linecap', ['butt', 'round', 'square'], default: 'butt'));
        self::addSpec(AttributeSpec::enum('stroke-linejoin', ['miter', 'round', 'bevel'], default: 'miter'));
        self::addSpec(new AttributeSpec('stroke-dasharray', AttributeType::DASH_ARRAY, defaultValue: 'none'));
        self::addSpec(AttributeSpec::length('stroke-dashoffset', default: '0'));
        self::addSpec(AttributeSpec::number('stroke-miterlimit', default: 4.0));
        self::addSpec(AttributeSpec::number('opacity', default: 1.0));

        // Font attributes
        self::addSpec(AttributeSpec::string('font-family'));
        self::addSpec(AttributeSpec::length('font-size'));
        self::addSpec(AttributeSpec::string('font-weight'));
        self::addSpec(AttributeSpec::string('font-style'));
        self::addSpec(AttributeSpec::enum('text-anchor', ['start', 'middle', 'end']));
        self::addSpec(AttributeSpec::string('text-decoration'));

        // Geometry - position and size
        self::addSpec(new AttributeSpec('x', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('y', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('width', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('height', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('cx', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('cy', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('r', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('rx', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('ry', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('x1', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('y1', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('x2', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('y2', AttributeType::LENGTH_OR_PERCENTAGE));

        // Path and points
        self::addSpec(new AttributeSpec('d', AttributeType::PATH_DATA, required: true));
        self::addSpec(new AttributeSpec('points', AttributeType::POINTS, required: true));

        // Transform
        self::addSpec(AttributeSpec::transform('transform'));
        self::addSpec(AttributeSpec::transform('gradientTransform'));
        self::addSpec(AttributeSpec::transform('patternTransform'));

        // ViewBox and aspect ratio
        self::addSpec(new AttributeSpec('viewBox', AttributeType::VIEWBOX));
        self::addSpec(new AttributeSpec('preserveAspectRatio', AttributeType::PRESERVE_ASPECT_RATIO, defaultValue: 'xMidYMid meet'));

        // References
        self::addSpec(AttributeSpec::iri('href'));
        self::addSpec(AttributeSpec::deprecated('xlink:href', AttributeType::IRI, 'Use href instead of xlink:href'));

        // Gradient attributes
        self::addSpec(AttributeSpec::enum('gradientUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'objectBoundingBox'));
        self::addSpec(AttributeSpec::enum('spreadMethod', ['pad', 'reflect', 'repeat'], default: 'pad'));
        self::addSpec(new AttributeSpec('offset', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(AttributeSpec::color('stop-color', default: 'black'));
        self::addSpec(AttributeSpec::number('stop-opacity', default: 1.0));

        // Filter attributes
        self::addSpec(AttributeSpec::iri('filter'));
        self::addSpec(AttributeSpec::enum('filterUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'objectBoundingBox'));
        self::addSpec(AttributeSpec::string('in'));
        self::addSpec(AttributeSpec::string('in2'));
        self::addSpec(AttributeSpec::string('result'));
        self::addSpec(new AttributeSpec('stdDeviation', AttributeType::NUMBER_LIST));

        // Clipping and masking
        self::addSpec(AttributeSpec::iri('clip-path'));
        self::addSpec(AttributeSpec::enum('clipPathUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'userSpaceOnUse'));
        self::addSpec(AttributeSpec::iri('mask'));
        self::addSpec(AttributeSpec::enum('maskUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'objectBoundingBox'));
        self::addSpec(AttributeSpec::enum('maskContentUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'userSpaceOnUse'));

        // Pattern attributes
        self::addSpec(AttributeSpec::enum('patternUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'objectBoundingBox'));
        self::addSpec(AttributeSpec::enum('patternContentUnits', ['userSpaceOnUse', 'objectBoundingBox'], default: 'userSpaceOnUse'));

        // Marker attributes
        self::addSpec(AttributeSpec::iri('marker-start'));
        self::addSpec(AttributeSpec::iri('marker-mid'));
        self::addSpec(AttributeSpec::iri('marker-end'));
        self::addSpec(AttributeSpec::enum('markerUnits', ['strokeWidth', 'userSpaceOnUse'], default: 'strokeWidth'));
        self::addSpec(new AttributeSpec('markerWidth', AttributeType::LENGTH_OR_PERCENTAGE, defaultValue: '3'));
        self::addSpec(new AttributeSpec('markerHeight', AttributeType::LENGTH_OR_PERCENTAGE, defaultValue: '3'));
        self::addSpec(new AttributeSpec('refX', AttributeType::LENGTH_OR_PERCENTAGE, defaultValue: '0'));
        self::addSpec(new AttributeSpec('refY', AttributeType::LENGTH_OR_PERCENTAGE, defaultValue: '0'));
        self::addSpec(AttributeSpec::string('orient'));

        // Text attributes
        self::addSpec(new AttributeSpec('dx', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('dy', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(new AttributeSpec('rotate', AttributeType::NUMBER_LIST));
        self::addSpec(new AttributeSpec('textLength', AttributeType::LENGTH_OR_PERCENTAGE));
        self::addSpec(AttributeSpec::enum('lengthAdjust', ['spacing', 'spacingAndGlyphs'], default: 'spacing'));
        self::addSpec(new AttributeSpec('startOffset', AttributeType::LENGTH_OR_PERCENTAGE, defaultValue: '0'));

        // Visibility
        self::addSpec(AttributeSpec::enum('visibility', ['visible', 'hidden', 'collapse'], default: 'visible'));
        self::addSpec(AttributeSpec::enum('display', ['inline', 'block', 'none', 'inherit']));
        self::addSpec(AttributeSpec::enum('overflow', ['visible', 'hidden', 'scroll', 'auto', 'inherit'], default: 'visible'));

        // Filter primitive common attributes
        self::addSpec(new AttributeSpec('color-interpolation-filters', AttributeType::ENUM, enumValues: ['auto', 'sRGB', 'linearRGB'], defaultValue: 'linearRGB'));

        // feGaussianBlur
        self::addSpec(AttributeSpec::enum('edgeMode', ['duplicate', 'wrap', 'none'], default: 'none'));

        // feColorMatrix - 'type' has context-dependent values, we use the most common (feColorMatrix)
        self::addSpec(AttributeSpec::enum('type', ['matrix', 'saturate', 'hueRotate', 'luminanceToAlpha']));
        self::addSpec(new AttributeSpec('values', AttributeType::NUMBER_LIST));

        // feBlend
        self::addSpec(AttributeSpec::enum('mode', ['normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'hard-light', 'soft-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity'], default: 'normal'));

        // feComposite - 'operator' has context-dependent values
        // We use feComposite values as they're more comprehensive
        self::addSpec(AttributeSpec::enum('operator', ['over', 'in', 'out', 'atop', 'xor', 'lighter', 'arithmetic'], default: 'over'));
        self::addSpec(AttributeSpec::number('k1', default: 0.0));
        self::addSpec(AttributeSpec::number('k2', default: 0.0));
        self::addSpec(AttributeSpec::number('k3', default: 0.0));
        self::addSpec(AttributeSpec::number('k4', default: 0.0));

        // feFlood
        self::addSpec(AttributeSpec::color('flood-color', default: 'black'));
        self::addSpec(AttributeSpec::number('flood-opacity', default: 1.0));

        // feOffset - Note: 'dx' and 'dy' are registered as LENGTH_OR_PERCENTAGE for text elements
        // For feOffset, they should be numbers, but we keep the more general type since
        // context-aware parsing would be needed for strict type checking

        // feTurbulence
        self::addSpec(AttributeSpec::number('baseFrequency'));
        self::addSpec(AttributeSpec::number('numOctaves', default: 1.0));
        self::addSpec(AttributeSpec::number('seed', default: 0.0));
        self::addSpec(AttributeSpec::enum('stitchTiles', ['stitch', 'noStitch'], default: 'noStitch'));
        // Note: feTurbulence 'type' has different values than feColorMatrix
        // Context-dependent resolution needed for strict validation

        // feMorphology - 'operator' has different values than feComposite
        // We keep feComposite values as they're more comprehensive
        self::addSpec(new AttributeSpec('radius', AttributeType::NUMBER_LIST, defaultValue: '0'));

        // feConvolveMatrix
        self::addSpec(new AttributeSpec('order', AttributeType::NUMBER_LIST, defaultValue: '3'));
        self::addSpec(new AttributeSpec('kernelMatrix', AttributeType::NUMBER_LIST));
        self::addSpec(AttributeSpec::number('divisor'));
        self::addSpec(AttributeSpec::number('bias', default: 0.0));
        self::addSpec(AttributeSpec::number('targetX'));
        self::addSpec(AttributeSpec::number('targetY'));
        // Note: feConvolveMatrix edgeMode default is 'duplicate', not 'none' like feGaussianBlur
        self::addSpec(new AttributeSpec('kernelUnitLength', AttributeType::NUMBER_LIST));
        self::addSpec(new AttributeSpec('preserveAlpha', AttributeType::BOOLEAN, defaultValue: false));

        // feDisplacementMap
        self::addSpec(AttributeSpec::number('scale', default: 0.0));
        self::addSpec(AttributeSpec::enum('xChannelSelector', ['R', 'G', 'B', 'A'], default: 'A'));
        self::addSpec(AttributeSpec::enum('yChannelSelector', ['R', 'G', 'B', 'A'], default: 'A'));

        // Light source elements
        // Note: 'x', 'y' for light sources are numbers, but we keep LENGTH_OR_PERCENTAGE
        // which is the more general type used for most geometric attributes
        self::addSpec(AttributeSpec::number('z'));
        self::addSpec(AttributeSpec::number('azimuth', default: 0.0));
        self::addSpec(AttributeSpec::number('elevation', default: 0.0));
        self::addSpec(AttributeSpec::number('pointsAtX', default: 0.0));
        self::addSpec(AttributeSpec::number('pointsAtY', default: 0.0));
        self::addSpec(AttributeSpec::number('pointsAtZ', default: 0.0));
        self::addSpec(AttributeSpec::number('specularExponent', default: 1.0));
        self::addSpec(AttributeSpec::number('limitingConeAngle'));

        // Lighting attributes
        self::addSpec(AttributeSpec::number('surfaceScale', default: 1.0));
        self::addSpec(AttributeSpec::number('diffuseConstant', default: 1.0));
        self::addSpec(AttributeSpec::number('specularConstant', default: 1.0));
        self::addSpec(AttributeSpec::color('lighting-color', default: 'white'));

        // feFuncR/G/B/A - 'type' has different values, context-dependent
        // Note: The 'type' for feFuncR/G/B/A differs from feColorMatrix and feTurbulence
        self::addSpec(new AttributeSpec('tableValues', AttributeType::NUMBER_LIST));
        self::addSpec(AttributeSpec::number('slope', default: 1.0));
        self::addSpec(AttributeSpec::number('intercept', default: 0.0));
        self::addSpec(AttributeSpec::number('amplitude', default: 1.0));
        self::addSpec(AttributeSpec::number('exponent', default: 1.0));
        // Note: 'offset' for feFunc* elements is a number, registered earlier as LENGTH_OR_PERCENTAGE
        // for gradient stops. We keep the more general type.

        // Animation attributes
        // Note: Some attributes like 'fill' and 'type' have different meanings in animation context
        // vs. presentation context. The registry stores the most common (presentation) definitions.
        // Context-aware attribute resolution should be handled by the parser based on element type.
        self::addSpec(AttributeSpec::string('attributeName'));
        self::addSpec(AttributeSpec::enum('attributeType', ['CSS', 'XML', 'auto'], default: 'auto'));
        self::addSpec(AttributeSpec::string('from'));
        self::addSpec(AttributeSpec::string('to'));
        self::addSpec(AttributeSpec::string('by'));
        // 'values' is overloaded - animation uses it differently from feColorMatrix
        // We keep the number list version since it's more complex
        self::addSpec(AttributeSpec::string('begin'));
        self::addSpec(AttributeSpec::string('dur'));
        self::addSpec(AttributeSpec::string('end'));
        // Note: Animation 'fill' (freeze/remove) differs from presentation 'fill' (paint)
        // We keep presentation 'fill' as the default since it's more common
        self::addSpec(AttributeSpec::enum('calcMode', ['discrete', 'linear', 'paced', 'spline'], default: 'linear'));
        self::addSpec(AttributeSpec::string('keyTimes'));
        self::addSpec(AttributeSpec::string('keySplines'));
        self::addSpec(AttributeSpec::enum('additive', ['replace', 'sum'], default: 'replace'));
        self::addSpec(AttributeSpec::enum('accumulate', ['none', 'sum'], default: 'none'));
        self::addSpec(AttributeSpec::string('repeatCount'));
        self::addSpec(AttributeSpec::string('repeatDur'));

        // animateTransform-specific
        // Note: 'type' is also used by feColorMatrix/feFuncR etc with different values
        // Context-aware resolution is needed for proper handling
    }

    /**
     * Adds a specification to the registry.
     */
    private static function addSpec(AttributeSpec $spec): void
    {
        self::$specs[$spec->getName()] = $spec;
    }
}
