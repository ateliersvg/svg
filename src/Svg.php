<?php

declare(strict_types=1);

namespace Atelier\Svg;

use Atelier\Svg\Dumper\CompactXmlDumper;
use Atelier\Svg\Dumper\DumperInterface;
use Atelier\Svg\Dumper\PrettyXmlDumper;
use Atelier\Svg\Dumper\XmlDumper;
use Atelier\Svg\Element\Builder;
use Atelier\Svg\Element\ElementCollection;
use Atelier\Svg\Element\ElementInterface;
use Atelier\Svg\Exception\ParseException;
use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Loader\LoaderInterface;
use Atelier\Svg\Morphing\ShapeMorpher;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Sanitizer\SanitizeProfile;
use Atelier\Svg\Sanitizer\Sanitizer;

/**
 * Facade class providing a simplified API for common SVG operations.
 *
 * This class provides static factory methods and a fluent interface for
 * common SVG workflows, reducing the need to know many different classes.
 *
 * Benefits:
 * - Simplified API for common tasks
 * - Reduced cognitive load
 * - Better discoverability
 * - Fewer imports needed
 *
 * Note: This facade does NOT replace the underlying APIs. Advanced users
 * can still use the detailed interfaces directly for fine-grained control.
 *
 * @example Simple optimization workflow
 * ```php
 * Svg::load('input.svg')
 *     ->optimize()
 *     ->save('output.svg');
 * ```
 * @example Create new SVG
 * ```php
 * Svg::create(800, 600)
 *     ->rect(10, 10, 100, 100, fill: '#ff0000')
 *     ->circle(200, 200, 50, fill: '#00ff00')
 *     ->save('output.svg');
 * ```
 * @example Load and manipulate
 * ```php
 * $svg = Svg::load('input.svg');
 * $svg->querySelector('.icon')
 *     ->setAttribute('fill', '#3b82f6');
 * $svg->optimize()->savePretty('output.svg');
 * ```
 * @example Morph between shapes
 * ```php
 * $frames = Svg::morphFrames($startPath, $endPath, 60, 'ease-in-out');
 * ```
 */
final class Svg implements \Stringable
{
    public const string VERSION = '1.0.0';
    private ?Builder $builder = null;

    /**
     * Private constructor - use static factory methods instead.
     */
    private function __construct(private readonly Document $document)
    {
    }

    // ========================================================================
    // Static Factory Methods
    // ========================================================================

    /**
     * Loads an SVG from a file.
     *
     * @param string               $path   Path to the SVG file
     * @param LoaderInterface|null $loader Optional custom loader
     *
     * @return self Fluent interface
     *
     * @throws RuntimeException If the file cannot be read
     * @throws ParseException   If the SVG content cannot be parsed
     *
     * @example
     * ```php
     * $svg = Svg::load('input.svg');
     * ```
     */
    public static function load(string $path, ?LoaderInterface $loader = null): self
    {
        $loader ??= new DomLoader();
        $document = $loader->loadFromFile($path);

        return new self($document);
    }

    /**
     * Loads an SVG from a string.
     *
     * @param string               $svgContent SVG content as string
     * @param LoaderInterface|null $loader     Optional custom loader
     *
     * @return self Fluent interface
     *
     * @throws RuntimeException If the SVG content cannot be converted
     * @throws ParseException   If the SVG content cannot be parsed
     *
     * @example
     * ```php
     * $svg = Svg::fromString('<svg>...</svg>');
     * ```
     */
    public static function fromString(string $svgContent, ?LoaderInterface $loader = null): self
    {
        $loader ??= new DomLoader();
        $document = $loader->loadFromString($svgContent);

        return new self($document);
    }

    /**
     * Creates a new empty SVG document.
     *
     * Note: Default dimensions match browser default for SVG elements.
     * For most use cases, explicitly specify dimensions for clarity.
     *
     * @param int|float $width  Width of the SVG (default: 300)
     * @param int|float $height Height of the SVG (default: 150)
     *
     * @return self Fluent interface with builder support
     *
     * @example
     * ```php
     * $svg = Svg::create(800, 600);
     * ```
     */
    public static function create(int|float $width = 300, int|float $height = 150): self
    {
        $builder = new Builder();
        $builder->svg($width, $height);
        $document = $builder->getDocument();

        $instance = new self($document);
        $instance->builder = $builder;

        return $instance;
    }

    /**
     * Creates an SVG from an existing Document.
     *
     * @param Document $document The document to wrap
     *
     * @return self Fluent interface
     */
    public static function fromDocument(Document $document): self
    {
        return new self($document);
    }

    // ========================================================================
    // Document Access
    // ========================================================================

    /**
     * Gets the underlying Document object.
     *
     * Use this to access the full API when the facade is insufficient.
     *
     * @return Document The document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * Gets the Builder instance if this SVG was created with create().
     *
     * @return Builder|null The builder, or null if not created with create()
     */
    public function getBuilder(): ?Builder
    {
        return $this->builder;
    }

    // ========================================================================
    // Fluent Builder API (delegated to Builder)
    // ========================================================================

    /**
     * Adds a rectangle element (only works if created with create()).
     *
     * @param int|float            $x          X coordinate
     * @param int|float            $y          Y coordinate
     * @param int|float            $width      Width
     * @param int|float            $height     Height
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function rect(
        int|float $x,
        int|float $y,
        int|float $width,
        int|float $height,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->rect($x, $y, $width, $height);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a circle element (only works if created with create()).
     *
     * @param int|float            $cx         Center X coordinate
     * @param int|float            $cy         Center Y coordinate
     * @param int|float            $r          Radius
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function circle(
        int|float $cx,
        int|float $cy,
        int|float $r,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->circle($cx, $cy, $r);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a path element (only works if created with create()).
     *
     * @param string               $d          Path data
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function path(string $d, array $attributes = []): self
    {
        $this->ensureBuilder();
        $this->builder->path();
        $this->builder->attr('d', $d);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds an ellipse element (only works if created with create()).
     *
     * @param int|float            $cx         Center X coordinate
     * @param int|float            $cy         Center Y coordinate
     * @param int|float            $rx         X-axis radius
     * @param int|float            $ry         Y-axis radius
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function ellipse(
        int|float $cx,
        int|float $cy,
        int|float $rx,
        int|float $ry,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->ellipse($cx, $cy, $rx, $ry);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a line element (only works if created with create()).
     *
     * @param int|float            $x1         Start X coordinate
     * @param int|float            $y1         Start Y coordinate
     * @param int|float            $x2         End X coordinate
     * @param int|float            $y2         End Y coordinate
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function line(
        int|float $x1,
        int|float $y1,
        int|float $x2,
        int|float $y2,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->line($x1, $y1, $x2, $y2);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a polygon element (only works if created with create()).
     *
     * @param string               $points     Points string (e.g., "0,0 100,0 100,100")
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function polygon(string $points, array $attributes = []): self
    {
        $this->ensureBuilder();
        $this->builder->polygon($points);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a polyline element (only works if created with create()).
     *
     * @param string               $points     Points string (e.g., "0,0 100,0 100,100")
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function polyline(string $points, array $attributes = []): self
    {
        $this->ensureBuilder();
        $this->builder->polyline($points);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a text element (only works if created with create()).
     *
     * @param int|float            $x          X coordinate
     * @param int|float            $y          Y coordinate
     * @param string|null          $content    Optional text content
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function text(int|float $x, int|float $y, ?string $content = null, array $attributes = []): self
    {
        $this->ensureBuilder();
        $this->builder->text($x, $y, $content);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds an image element (only works if created with create()).
     *
     * @param string               $href       The image URL
     * @param int|float|null       $x          Optional X coordinate
     * @param int|float|null       $y          Optional Y coordinate
     * @param int|float|null       $width      Optional width
     * @param int|float|null       $height     Optional height
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function image(
        string $href,
        int|float|null $x = null,
        int|float|null $y = null,
        int|float|null $width = null,
        int|float|null $height = null,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->image($href, $x, $y, $width, $height);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a defs section (only works if created with create()).
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function defs(): self
    {
        $this->ensureBuilder();
        $this->builder->defs();

        return $this;
    }

    /**
     * Adds a use element (only works if created with create()).
     *
     * @param string               $href       The reference to the element to use
     * @param int|float|null       $x          Optional X coordinate
     * @param int|float|null       $y          Optional Y coordinate
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function useElement(
        string $href,
        int|float|null $x = null,
        int|float|null $y = null,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->use($href, $x, $y);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a linear gradient element (only works if created with create()).
     *
     * @param string               $id         The gradient ID
     * @param int|float|null       $x1         Optional start X coordinate
     * @param int|float|null       $y1         Optional start Y coordinate
     * @param int|float|null       $x2         Optional end X coordinate
     * @param int|float|null       $y2         Optional end Y coordinate
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function linearGradient(
        string $id,
        int|float|null $x1 = null,
        int|float|null $y1 = null,
        int|float|null $x2 = null,
        int|float|null $y2 = null,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->linearGradient($id, $x1, $y1, $x2, $y2);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Adds a radial gradient element (only works if created with create()).
     *
     * @param string               $id         The gradient ID
     * @param int|float|null       $cx         Optional center X coordinate
     * @param int|float|null       $cy         Optional center Y coordinate
     * @param int|float|null       $r          Optional radius
     * @param array<string, mixed> $attributes Additional attributes
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function radialGradient(
        string $id,
        int|float|null $cx = null,
        int|float|null $cy = null,
        int|float|null $r = null,
        array $attributes = [],
    ): self {
        $this->ensureBuilder();
        $this->builder->radialGradient($id, $cx, $cy, $r);
        $this->applyAttributesAndEnd($attributes);

        return $this;
    }

    /**
     * Sets the fill color on the current builder element.
     *
     * @param string $color The fill color
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function fill(string $color): self
    {
        $this->ensureBuilder();
        $this->builder->fill($color);

        return $this;
    }

    /**
     * Sets the stroke color on the current builder element.
     *
     * @param string $color The stroke color
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function stroke(string $color): self
    {
        $this->ensureBuilder();
        $this->builder->stroke($color);

        return $this;
    }

    /**
     * Sets the stroke-width on the current builder element.
     *
     * @param int|float $width The stroke width
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function strokeWidth(int|float $width): self
    {
        $this->ensureBuilder();
        $this->builder->strokeWidth($width);

        return $this;
    }

    /**
     * Sets the opacity on the current builder element.
     *
     * @param float $value The opacity value (0.0 to 1.0)
     *
     * @return self For chaining
     *
     * @throws RuntimeException If not created with create()
     */
    public function opacity(float $value): self
    {
        $this->ensureBuilder();
        $this->builder->opacity($value);

        return $this;
    }

    /**
     * Helper to apply attributes and end element building.
     *
     * @param array<string, mixed> $attributes Attributes to apply
     */
    private function applyAttributesAndEnd(array $attributes): void
    {
        // Builder is ensured by caller
        assert(null !== $this->builder);

        foreach ($attributes as $key => $value) {
            if (is_scalar($value) || $value instanceof \Stringable) {
                $this->builder->attr($key, (string) $value);
            }
        }
        $this->builder->end();
    }

    /**
     * Adds a group element (only works if created with create()).
     *
     * @return Builder The builder for nesting
     *
     * @throws RuntimeException If not created with create()
     */
    public function group(): Builder
    {
        $this->ensureBuilder();

        return $this->builder->g();
    }

    /**
     * Ensures a builder is available.
     *
     * @throws RuntimeException If no builder is available
     *
     * @phpstan-assert !null $this->builder
     */
    private function ensureBuilder(): void
    {
        if (null === $this->builder) {
            throw new RuntimeException('Builder methods are only available for SVGs created with Svg::create(). For loaded SVGs, use getDocument() to access the full API.');
        }
    }

    // ========================================================================
    // Query Methods (delegated to Document)
    // ========================================================================

    /**
     * Finds the first element matching a CSS selector.
     *
     * @param string $selector CSS selector
     *
     * @return ElementInterface|null The element or null
     */
    public function querySelector(string $selector): ?ElementInterface
    {
        return $this->document->querySelector($selector);
    }

    /**
     * Finds all elements matching a CSS selector.
     *
     * @param string $selector CSS selector
     *
     * @return ElementCollection Collection of elements
     */
    public function querySelectorAll(string $selector): ElementCollection
    {
        return $this->document->querySelectorAll($selector);
    }

    // ========================================================================
    // Optimization Methods
    // ========================================================================

    /**
     * Optimizes the SVG with default settings.
     *
     * @return self For chaining
     *
     * @example
     * ```php
     * $svg->optimize();
     * ```
     */
    public function optimize(): self
    {
        $optimizer = new Optimizer(OptimizerPresets::default());
        $optimizer->optimize($this->document);

        return $this;
    }

    /**
     * Optimizes the SVG aggressively for maximum size reduction.
     *
     * @return self For chaining
     */
    public function optimizeAggressive(): self
    {
        $optimizer = new Optimizer(OptimizerPresets::aggressive());
        $optimizer->optimize($this->document);

        return $this;
    }

    /**
     * Optimizes the SVG safely, preserving more metadata.
     *
     * @return self For chaining
     */
    public function optimizeSafe(): self
    {
        $optimizer = new Optimizer(OptimizerPresets::safe());
        $optimizer->optimize($this->document);

        return $this;
    }

    /**
     * Optimizes the SVG using the web preset.
     *
     * Optimized for production web delivery (inline SVG, `<img>`, CSS backgrounds, icons).
     * Strips title, desc, dimensions. Converts shapes to paths and merges them.
     *
     * @return self For chaining
     */
    public function optimizeWeb(): self
    {
        $optimizer = new Optimizer(OptimizerPresets::web());
        $optimizer->optimize($this->document);

        return $this;
    }

    /**
     * Optimizes the SVG with custom passes.
     *
     * @param array<\Atelier\Svg\Optimizer\Pass\OptimizerPassInterface> $passes
     *
     * @return self For chaining
     */
    public function optimizeWith(array $passes): self
    {
        $optimizer = new Optimizer($passes);
        $optimizer->optimize($this->document);

        return $this;
    }

    // ========================================================================
    // Sanitization
    // ========================================================================

    /**
     * Sanitizes the SVG by removing potentially dangerous content.
     *
     * Uses the default sanitization profile which removes script elements,
     * event handler attributes, and javascript: URLs.
     *
     * @return self For chaining
     */
    public function sanitize(SanitizeProfile $profile = SanitizeProfile::DEFAULT): self
    {
        $sanitizer = match ($profile) {
            SanitizeProfile::STRICT => Sanitizer::strict(),
            SanitizeProfile::DEFAULT => Sanitizer::default(),
            SanitizeProfile::PERMISSIVE => Sanitizer::permissive(),
        };

        $sanitizer->sanitize($this->document);

        return $this;
    }

    // ========================================================================
    // Output Methods
    // ========================================================================

    /**
     * Converts the SVG to a compact string.
     *
     * @return string Compact SVG markup
     */
    public function toString(): string
    {
        $dumper = new CompactXmlDumper();

        if ($this->document->getOmitXmlDeclaration()) {
            $dumper->includeXmlDeclaration(false);
        }

        return $dumper->dump($this->document);
    }

    /**
     * Converts the SVG to a pretty-printed string.
     *
     * @return string Pretty-printed SVG markup
     */
    public function toPrettyString(): string
    {
        $dumper = new PrettyXmlDumper();

        if ($this->document->getOmitXmlDeclaration()) {
            $dumper->includeXmlDeclaration(false);
        }

        return $dumper->dump($this->document);
    }

    /**
     * Saves the SVG to a file (compact format).
     *
     * @param string               $path   Path to save to
     * @param DumperInterface|null $dumper Optional custom dumper
     *
     * @return self For chaining
     *
     * @throws RuntimeException If the file cannot be written
     */
    public function save(string $path, ?DumperInterface $dumper = null): self
    {
        $dumper ??= new CompactXmlDumper();

        if ($this->document->getOmitXmlDeclaration() && $dumper instanceof XmlDumper) {
            $dumper->includeXmlDeclaration(false);
        }

        $content = $dumper->dump($this->document);

        $result = @file_put_contents($path, $content);
        if (false === $result) {
            throw new RuntimeException("Failed to write SVG to file: {$path}");
        }

        return $this;
    }

    /**
     * Saves the SVG to a file (pretty-printed format).
     *
     * @param string $path Path to save to
     *
     * @return self For chaining
     *
     * @throws RuntimeException If the file cannot be written
     */
    public function savePretty(string $path): self
    {
        return $this->save($path, new PrettyXmlDumper());
    }

    /**
     * Returns compact SVG markup when cast to string.
     *
     * @return string Compact SVG markup
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts the SVG to a data URI suitable for embedding in CSS or HTML.
     *
     * @param bool $base64 Whether to use base64 encoding (true) or URL encoding (false)
     *
     * @return string The data URI string
     */
    public function toDataUri(bool $base64 = true): string
    {
        $dumper = (new CompactXmlDumper())->includeXmlDeclaration(false);
        $svg = $dumper->dump($this->document);

        if ($base64) {
            return 'data:image/svg+xml;base64,'.base64_encode($svg);
        }

        return 'data:image/svg+xml,'.rawurlencode($svg);
    }

    // ========================================================================
    // Morphing Methods
    // ========================================================================

    /**
     * Morph between two SVG paths at a specific interpolation point.
     *
     * @param Data   $startPath Starting path
     * @param Data   $endPath   Ending path
     * @param float  $t         Interpolation value (0.0 = start, 1.0 = end)
     * @param string $easing    Easing function name (default: 'linear')
     *
     * @return Data Interpolated path
     *
     * @example
     * ```php
     * $midPath = Svg::morph($startPath, $endPath, 0.5);
     * ```
     */
    public static function morph(Data $startPath, Data $endPath, float $t, string $easing = 'linear'): Data
    {
        $morpher = new ShapeMorpher();

        return $morpher->morph($startPath, $endPath, $t, $easing);
    }

    /**
     * Generate multiple frames for morphing animation between two paths.
     *
     * @param Data   $startPath  Starting path
     * @param Data   $endPath    Ending path
     * @param int    $frameCount Number of frames to generate
     * @param string $easing     Easing function name (default: 'linear')
     *
     * @return array<Data> Array of interpolated paths
     *
     * @example
     * ```php
     * $frames = Svg::morphFrames($startPath, $endPath, 60, 'ease-in-out');
     * ```
     */
    public static function morphFrames(Data $startPath, Data $endPath, int $frameCount, string $easing = 'linear'): array
    {
        $morpher = new ShapeMorpher();

        return $morpher->generateFrames($startPath, $endPath, $frameCount, $easing);
    }

    /**
     * Create a ShapeMorpher builder for more advanced morphing options.
     *
     * @return ShapeMorpher New morpher instance
     *
     * @example
     * ```php
     * $frames = Svg::createMorpher()
     *     ->from($startPath)
     *     ->to($endPath)
     *     ->withDuration(2000, 60)
     *     ->withEasing('ease-in-out')
     *     ->generate();
     * ```
     */
    public static function createMorpher(): ShapeMorpher
    {
        return new ShapeMorpher();
    }
}
