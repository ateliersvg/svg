<?php

declare(strict_types=1);

namespace Atelier\Svg\Element;

use Atelier\Svg\Element\Accessibility\Accessibility;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Visitor\VisitorInterface;

/**
 * Abstract base class for all SVG elements.
 * Provides common functionality for attribute management, parent tracking, and visitor pattern support.
 */
abstract class AbstractElement implements ElementInterface
{
    /** @var array<string, string> Element attributes */
    private array $attributes = [];

    /** @var ElementInterface|null Parent element reference */
    private ?ElementInterface $parent = null;

    /**
     * @param string                $tagName             The SVG tag name
     * @param array<string>         $protectedAttributes List of attributes that should not be modified
     * @param array<string, string> $initialAttributes   Initial attribute values
     */
    public function __construct(
        private readonly string $tagName,
        private readonly array $protectedAttributes = [],
        array $initialAttributes = [],
    ) {
        foreach ($initialAttributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * Gets the tag name of this element.
     */
    public function getTagName(): string
    {
        return $this->tagName;
    }

    /**
     * Gets the value of an attribute.
     *
     * @param string $name The attribute name
     *
     * @return string|null The attribute value, or null if not set
     */
    public function getAttribute(string $name): ?string
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Sets the value of an attribute.
     *
     * @param string           $name  The attribute name
     * @param string|int|float $value The attribute value
     */
    public function setAttribute(string $name, string|int|float $value): static
    {
        if (in_array($name, $this->protectedAttributes, true)) {
            throw new InvalidArgumentException("Cannot modify protected attribute: {$name}");
        }
        $this->attributes[$name] = (string) $value;

        return $this;
    }

    /**
     * Checks if an attribute exists.
     *
     * @param string $name The attribute name
     *
     * @return bool True if the attribute exists, false otherwise
     */
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    /**
     * Removes an attribute.
     *
     * @param string $name The attribute name
     */
    public function removeAttribute(string $name): static
    {
        if (in_array($name, $this->protectedAttributes, true)) {
            throw new InvalidArgumentException("Cannot remove protected attribute: {$name}");
        }
        unset($this->attributes[$name]);

        return $this;
    }

    /**
     * Gets all attributes.
     *
     * @return array<string, string> All attributes as key-value pairs
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Gets the parent element.
     *
     * @return ElementInterface|null The parent element, or null if none
     */
    public function getParent(): ?ElementInterface
    {
        return $this->parent;
    }

    /**
     * Sets the parent element.
     *
     * @param ElementInterface|null $parent The parent element
     */
    public function setParent(?ElementInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Accepts a visitor for the visitor pattern.
     * Delegates to a specific visit method based on the element class name.
     *
     * @param VisitorInterface $visitor The visitor to accept
     */
    public function accept(VisitorInterface $visitor): mixed
    {
        $visitMethod = 'visit'.(new \ReflectionClass($this))->getShortName();
        if (method_exists($visitor, $visitMethod)) {
            return $visitor->$visitMethod($this);
        }

        return $visitor->visit($this);
    }

    /**
     * Sets the id attribute of this element.
     *
     * @param string $id The ID to set
     *
     * @return static This element (for method chaining)
     */
    public function setId(string $id): static
    {
        $this->setAttribute('id', $id);

        return $this;
    }

    /**
     * Gets the id attribute of this element.
     *
     * @return string|null The ID or null if not set
     */
    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    /**
     * Adds one or more CSS classes to this element.
     *
     * @param string $className Space-separated class names to add
     */
    public function addClass(string $className): static
    {
        $classes = $this->getClasses();
        $newClasses = array_filter(explode(' ', $className));

        foreach ($newClasses as $class) {
            if (!in_array($class, $classes, true)) {
                $classes[] = $class;
            }
        }

        $this->setAttribute('class', implode(' ', $classes));

        return $this;
    }

    /**
     * Removes one or more CSS classes from this element.
     *
     * @param string $className Space-separated class names to remove
     */
    public function removeClass(string $className): static
    {
        $classes = $this->getClasses();
        $removeClasses = array_filter(explode(' ', $className));

        $classes = array_filter($classes, fn ($c) => !in_array($c, $removeClasses, true));

        if (empty($classes)) {
            $this->removeAttribute('class');
        } else {
            $this->setAttribute('class', implode(' ', $classes));
        }

        return $this;
    }

    /**
     * Checks if this element has a specific CSS class.
     *
     * @param string $className The class name to check
     */
    public function hasClass(string $className): bool
    {
        return in_array($className, $this->getClasses(), true);
    }

    /**
     * Toggles a CSS class on this element.
     * If the class exists, it is removed. If it doesn't exist, it is added.
     *
     * @param string $className The class name to toggle
     */
    public function toggleClass(string $className): static
    {
        return $this->hasClass($className)
            ? $this->removeClass($className)
            : $this->addClass($className);
    }

    /**
     * Gets all CSS classes for this element as an array.
     *
     * @return array<string> Array of class names
     */
    public function getClasses(): array
    {
        $class = $this->getAttribute('class');

        return $class ? array_values(array_filter(explode(' ', $class))) : [];
    }

    /**
     * Creates a shallow clone of this element.
     * Copies all attributes but not children (if any).
     */
    public function clone(): static
    {
        $class = static::class;
        $clone = new $class();

        // Copy all attributes
        foreach ($this->getAttributes() as $name => $value) {
            $clone->setAttribute($name, $value);
        }

        return $clone;
    }

    /**
     * Gets a fluent transform helper for this element.
     */
    public function transform(): \Atelier\Svg\Geometry\TransformBuilder
    {
        return new \Atelier\Svg\Geometry\TransformBuilder($this);
    }

    /**
     * Gets the current transform list of this element.
     */
    public function getTransform(): \Atelier\Svg\Value\TransformList
    {
        return \Atelier\Svg\Value\TransformList::parse($this->getAttribute('transform'));
    }

    /**
     * Sets the transform of this element.
     *
     * @param \Atelier\Svg\Value\TransformList|string $transform The transform to set
     */
    public function setTransform(\Atelier\Svg\Value\TransformList|string $transform): static
    {
        if (is_string($transform)) {
            $transform = \Atelier\Svg\Value\TransformList::parse($transform);
        }

        if ($transform->isEmpty()) {
            $this->removeAttribute('transform');
        } else {
            $this->setAttribute('transform', $transform->toString());
        }

        return $this;
    }

    /**
     * Appends/composes a transform to the existing transforms.
     *
     * @param \Atelier\Svg\Value\TransformList|string $transform The transform to append
     */
    public function applyTransform(\Atelier\Svg\Value\TransformList|string $transform): static
    {
        if (is_string($transform)) {
            $transform = \Atelier\Svg\Value\TransformList::parse($transform);
        }

        $current = $this->getTransform();
        $combined = array_merge($current->getTransforms(), $transform->getTransforms());

        $this->setTransform(\Atelier\Svg\Value\TransformList::fromArray($combined));

        return $this;
    }

    /**
     * Clears all transforms from this element.
     */
    public function clearTransform(): static
    {
        $this->removeAttribute('transform');

        return $this;
    }

    /**
     * Gets the transformation matrix for this element.
     */
    public function getTransformMatrix(): \Atelier\Svg\Geometry\Matrix
    {
        return $this->transform()->toMatrix();
    }

    /**
     * Gets the translation component of the transform.
     *
     * @return array{0: float, 1: float} [x, y]
     */
    public function getTranslation(): array
    {
        return $this->transform()->getTranslation();
    }

    /**
     * Gets the rotation component of the transform in degrees.
     */
    public function getRotation(): float
    {
        return $this->transform()->getRotation();
    }

    /**
     * Gets the scale component of the transform.
     *
     * @return array{0: float, 1: float} [sx, sy]
     */
    public function getScale(): array
    {
        return $this->transform()->getScale();
    }

    /**
     * Sets the translation of this element.
     */
    public function setTranslation(float $x, float $y): static
    {
        $this->transform()->setTranslation($x, $y)->apply();

        return $this;
    }

    /**
     * Sets the rotation of this element.
     *
     * @param float      $angle Angle in degrees
     * @param float|null $cx    Center X (optional)
     * @param float|null $cy    Center Y (optional)
     */
    public function setRotation(float $angle, ?float $cx = null, ?float $cy = null): static
    {
        $this->transform()->setRotation($angle, $cx, $cy)->apply();

        return $this;
    }

    /**
     * Sets the scale of this element.
     */
    public function setScale(float $x, ?float $y = null): static
    {
        $this->transform()->setScale($x, $y)->apply();

        return $this;
    }

    /**
     * Gets the inline style object for this element.
     */
    public function getStyle(): \Atelier\Svg\Value\Style
    {
        return \Atelier\Svg\Value\Style::parse($this->getAttribute('style'));
    }

    /**
     * Sets multiple styles on this element.
     *
     * @param array<string, string> $styles
     */
    public function setStyles(array $styles): static
    {
        \Atelier\Svg\Value\Style\StyleUtils::setStyles($this, $styles);

        return $this;
    }

    /**
     * Sets a single style property.
     */
    public function setStyle(string $property, string $value): static
    {
        $style = $this->getStyle();
        $style->set($property, $value);
        $this->setAttribute('style', $style->toString());

        return $this;
    }

    /**
     * Gets a style property value.
     */
    public function getStyleProperty(string $property): ?string
    {
        return \Atelier\Svg\Value\Style\StyleUtils::getStyle($this, $property);
    }

    /**
     * Gets all styles as an array.
     * Returns both inline styles and presentation attributes.
     *
     * @return array<string, string>
     */
    public function getStyles(): array
    {
        return \Atelier\Svg\Value\Style\StyleUtils::getAllStyles($this)->toArray();
    }

    /**
     * Checks if a style property exists.
     * Checks both inline styles and presentation attributes.
     */
    public function hasStyle(string $property): bool
    {
        return null !== $this->getStyleProperty($property);
    }

    /**
     * Removes a style property.
     */
    public function removeStyle(string $property): static
    {
        \Atelier\Svg\Value\Style\StyleUtils::removeStyle($this, $property);

        return $this;
    }

    /**
     * Gets a fluent style helper for this element.
     * Provides a more intuitive API for chaining style operations.
     */
    public function style(): \Atelier\Svg\Value\Style\StyleBuilder
    {
        return new \Atelier\Svg\Value\Style\StyleBuilder($this);
    }

    /**
     * Converts inline styles to presentation attributes.
     */
    public function inlineStyles(): static
    {
        \Atelier\Svg\Value\Style\StyleUtils::stylesToAttributes($this);

        return $this;
    }

    /**
     * Converts presentation attributes to inline styles.
     */
    public function extractStyles(): static
    {
        $style = \Atelier\Svg\Value\Style\StyleUtils::attributesToStyles($this);
        $this->setAttribute('style', $style->toString());

        return $this;
    }

    // ========================================================================
    // Accessibility Methods
    // ========================================================================

    /**
     * Adds a title element to this element for accessibility.
     *
     * @param string $title The title text
     *
     * @return static This element (for method chaining)
     */
    public function addTitle(string $title): static
    {
        Accessibility::addTitle($this, $title);

        return $this;
    }

    /**
     * Adds a description element to this element for accessibility.
     *
     * @param string $description The description text
     *
     * @return static This element (for method chaining)
     */
    public function addDescription(string $description): static
    {
        Accessibility::addDescription($this, $description);

        return $this;
    }

    /**
     * Sets the aria-label attribute for accessibility.
     *
     * @param string $label The ARIA label text
     *
     * @return static This element (for method chaining)
     */
    public function setAriaLabel(string $label): static
    {
        Accessibility::setAriaLabel($this, $label);

        return $this;
    }

    /**
     * Sets the role attribute for accessibility.
     *
     * @param string $role The ARIA role (e.g., 'img', 'button', 'presentation')
     *
     * @return static This element (for method chaining)
     */
    public function setAriaRole(string $role): static
    {
        Accessibility::setAriaRole($this, $role);

        return $this;
    }

    /**
     * Makes this element focusable or not focusable.
     *
     * @param bool $focusable Whether the element should be focusable
     *
     * @return static This element (for method chaining)
     */
    public function setFocusable(bool $focusable): static
    {
        Accessibility::setFocusable($this, $focusable);

        return $this;
    }

    /**
     * Sets the tabindex attribute for keyboard navigation.
     *
     * @param int $index The tab index (-1 to exclude from tab order, 0 for natural order, >0 for explicit order)
     *
     * @return static This element (for method chaining)
     */
    public function setTabIndex(int $index): static
    {
        Accessibility::setTabIndex($this, $index);

        return $this;
    }

    // ========================================
    // Filter & Effect Methods
    // ========================================

    /**
     * Apply a filter to this element.
     *
     * @param string $filterId The filter ID (with or without url(#...) wrapper)
     *
     * @return static This element (for method chaining)
     */
    public function applyFilter(string $filterId): static
    {
        // Add url(#...) wrapper if not present
        if (!str_starts_with($filterId, 'url(')) {
            $filterId = "url(#{$filterId})";
        }

        $this->setAttribute('filter', $filterId);

        return $this;
    }

    /**
     * Remove the filter from this element.
     *
     * @return static This element (for method chaining)
     */
    public function removeFilter(): static
    {
        $this->removeAttribute('filter');

        return $this;
    }

    /**
     * Get the applied filter ID (without url(#...) wrapper).
     *
     * @return string|null The filter ID, or null if no filter is applied
     */
    public function getFilterId(): ?string
    {
        $filter = $this->getAttribute('filter');
        if (null === $filter) {
            return null;
        }

        // Extract ID from url(#id) format
        if (preg_match('/url\(#(.+?)\)/', $filter, $matches)) {
            return $matches[1];
        }

        return $filter;
    }

    // ========================================
    // Fill & Stroke Methods
    // ========================================

    /**
     * Set the fill to a gradient or pattern.
     *
     * @param string $paintServerId The paint server ID (gradient or pattern, with or without url(#...) wrapper)
     *
     * @return static This element (for method chaining)
     */
    public function setFillPaintServer(string $paintServerId): static
    {
        // Add url(#...) wrapper if not present
        if (!str_starts_with($paintServerId, 'url(')) {
            $paintServerId = "url(#{$paintServerId})";
        }

        $this->setAttribute('fill', $paintServerId);

        return $this;
    }

    /**
     * Set the stroke to a gradient or pattern.
     *
     * @param string $paintServerId The paint server ID (gradient or pattern, with or without url(#...) wrapper)
     *
     * @return static This element (for method chaining)
     */
    public function setStrokePaintServer(string $paintServerId): static
    {
        // Add url(#...) wrapper if not present
        if (!str_starts_with($paintServerId, 'url(')) {
            $paintServerId = "url(#{$paintServerId})";
        }

        $this->setAttribute('stroke', $paintServerId);

        return $this;
    }

    /**
     * Set the fill color or value of this element.
     *
     * @param string $fill The fill value (e.g. "red", "#ff0000", "none")
     *
     * @return static This element (for method chaining)
     */
    public function setFill(string $fill): static
    {
        $this->setAttribute('fill', $fill);

        return $this;
    }

    /**
     * Set the stroke color or value of this element.
     *
     * @param string $stroke The stroke value (e.g. "black", "#000000", "none")
     *
     * @return static This element (for method chaining)
     */
    public function setStroke(string $stroke): static
    {
        $this->setAttribute('stroke', $stroke);

        return $this;
    }

    /**
     * Set the stroke width of this element.
     *
     * @param string|int|float $strokeWidth The stroke width value
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeWidth(string|int|float $strokeWidth): static
    {
        $this->setAttribute('stroke-width', (string) $strokeWidth);

        return $this;
    }

    /**
     * Set the stroke-linecap of this element.
     *
     * @param string $linecap The linecap value (e.g. "butt", "round", "square")
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeLinecap(string $linecap): static
    {
        $this->setAttribute('stroke-linecap', $linecap);

        return $this;
    }

    /**
     * Set the stroke-linejoin of this element.
     *
     * @param string $linejoin The linejoin value (e.g. "miter", "round", "bevel")
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeLinejoin(string $linejoin): static
    {
        $this->setAttribute('stroke-linejoin', $linejoin);

        return $this;
    }

    /**
     * Set the stroke-dasharray of this element.
     *
     * @param string $dasharray The dasharray value (e.g. "5,10", "3 5 2")
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeDasharray(string $dasharray): static
    {
        $this->setAttribute('stroke-dasharray', $dasharray);

        return $this;
    }

    /**
     * Set the stroke-dashoffset of this element.
     *
     * @param string|int|float $dashoffset The dashoffset value
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeDashoffset(string|int|float $dashoffset): static
    {
        $this->setAttribute('stroke-dashoffset', (string) $dashoffset);

        return $this;
    }

    /**
     * Set the stroke-miterlimit of this element.
     *
     * @param string|int|float $miterlimit The miterlimit value
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeMiterlimit(string|int|float $miterlimit): static
    {
        $this->setAttribute('stroke-miterlimit', (string) $miterlimit);

        return $this;
    }

    /**
     * Set the fill-rule of this element.
     *
     * @param string $fillRule The fill-rule value (e.g. "nonzero", "evenodd")
     *
     * @return static This element (for method chaining)
     */
    public function setFillRule(string $fillRule): static
    {
        $this->setAttribute('fill-rule', $fillRule);

        return $this;
    }

    /**
     * Set the display property of this element.
     *
     * @param string $display The display value (e.g. "none", "inline", "block")
     *
     * @return static This element (for method chaining)
     */
    public function setDisplay(string $display): static
    {
        $this->setAttribute('display', $display);

        return $this;
    }

    /**
     * Set the visibility of this element.
     *
     * @param string $visibility The visibility value (e.g. "visible", "hidden", "collapse")
     *
     * @return static This element (for method chaining)
     */
    public function setVisibility(string $visibility): static
    {
        $this->setAttribute('visibility', $visibility);

        return $this;
    }

    /**
     * Set the opacity of this element.
     *
     * @param float $opacity Opacity value (0-1)
     *
     * @return static This element (for method chaining)
     */
    public function setOpacity(float $opacity): static
    {
        $this->setAttribute('opacity', (string) $opacity);

        return $this;
    }

    /**
     * Get the opacity of this element.
     *
     * @return float|null The opacity value, or null if not set
     */
    public function getOpacity(): ?float
    {
        $opacity = $this->getAttribute('opacity');

        return null !== $opacity ? (float) $opacity : null;
    }

    /**
     * Set the fill-opacity of this element.
     *
     * @param float $opacity Fill opacity value (0-1)
     *
     * @return static This element (for method chaining)
     */
    public function setFillOpacity(float $opacity): static
    {
        $this->setAttribute('fill-opacity', (string) $opacity);

        return $this;
    }

    /**
     * Set the stroke-opacity of this element.
     *
     * @param float $opacity Stroke opacity value (0-1)
     *
     * @return static This element (for method chaining)
     */
    public function setStrokeOpacity(float $opacity): static
    {
        $this->setAttribute('stroke-opacity', (string) $opacity);

        return $this;
    }

    /**
     * Set the pointer-events property of this element.
     *
     * @param string $pointerEvents The pointer-events value (e.g. "none", "all", "visiblePainted")
     *
     * @return static This element (for method chaining)
     */
    public function setPointerEvents(string $pointerEvents): static
    {
        $this->setAttribute('pointer-events', $pointerEvents);

        return $this;
    }

    /**
     * Set the cursor property of this element.
     *
     * @param string $cursor The cursor value (e.g. "pointer", "default", "crosshair")
     *
     * @return static This element (for method chaining)
     */
    public function setCursor(string $cursor): static
    {
        $this->setAttribute('cursor', $cursor);

        return $this;
    }

    /**
     * Set the clip-path of this element using a reference ID.
     *
     * @param string $clipPathId The clip-path element ID
     *
     * @return static This element (for method chaining)
     */
    public function setClipPath(string $clipPathId): static
    {
        $this->setAttribute('clip-path', sprintf('url(#%s)', $clipPathId));

        return $this;
    }

    /**
     * Set the mask of this element using a reference ID.
     *
     * @param string $maskId The mask element ID
     *
     * @return static This element (for method chaining)
     */
    public function setMask(string $maskId): static
    {
        $this->setAttribute('mask', sprintf('url(#%s)', $maskId));

        return $this;
    }

    // ========================================
    // Short Aliases
    // ========================================

    /**
     * Alias for setFill().
     */
    public function fill(string $fill): static
    {
        return $this->setFill($fill);
    }

    /**
     * Alias for setStroke().
     */
    public function stroke(string $stroke): static
    {
        return $this->setStroke($stroke);
    }

    /**
     * Alias for setStrokeWidth().
     */
    public function strokeWidth(string|int|float $strokeWidth): static
    {
        return $this->setStrokeWidth($strokeWidth);
    }

    /**
     * Alias for setOpacity().
     */
    public function opacity(float $opacity): static
    {
        return $this->setOpacity($opacity);
    }

    // ========================================
    // Marker Methods
    // ========================================

    /**
     * Set the marker-start of this element using a marker ID.
     *
     * @param string $markerId The marker element ID
     *
     * @return static This element (for method chaining)
     */
    public function setMarkerStart(string $markerId): static
    {
        $this->setAttribute('marker-start', sprintf('url(#%s)', $markerId));

        return $this;
    }

    /**
     * Set the marker-mid of this element using a marker ID.
     *
     * @param string $markerId The marker element ID
     *
     * @return static This element (for method chaining)
     */
    public function setMarkerMid(string $markerId): static
    {
        $this->setAttribute('marker-mid', sprintf('url(#%s)', $markerId));

        return $this;
    }

    /**
     * Set the marker-end of this element using a marker ID.
     *
     * @param string $markerId The marker element ID
     *
     * @return static This element (for method chaining)
     */
    public function setMarkerEnd(string $markerId): static
    {
        $this->setAttribute('marker-end', sprintf('url(#%s)', $markerId));

        return $this;
    }

    // ========================================
    // Bounding Box Methods
    // ========================================

    /**
     * Get a bounding box helper for this element.
     */
    public function bbox(): \Atelier\Svg\Geometry\BoundingBoxCalculator
    {
        return new \Atelier\Svg\Geometry\BoundingBoxCalculator($this);
    }

    // ========================================
    // Crop/Clip Methods
    // ========================================

    /**
     * Get a crop helper for this element.
     */
    public function crop(): \Atelier\Svg\Document\DocumentCropper
    {
        return new \Atelier\Svg\Document\DocumentCropper($this);
    }
}
