<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\Transform\MatrixTransform;
use Atelier\Svg\Value\Transform\RotateTransform;
use Atelier\Svg\Value\Transform\ScaleTransform;
use Atelier\Svg\Value\Transform\SkewXTransform;
use Atelier\Svg\Value\Transform\SkewYTransform;
use Atelier\Svg\Value\Transform\TranslateTransform;

/**
 * Represents an SVG transform list.
 *
 * Handles parsing and serialization of SVG transform attribute values.
 * Supports: translate, scale, rotate, skewX, skewY, and matrix transforms.
 *
 * @see https://www.w3.org/TR/SVG11/coords.html#TransformAttribute
 * @see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/transform
 */
final readonly class TransformList implements \Stringable
{
    /**
     * Private constructor, use static factory methods.
     *
     * @param array<Transform> $transforms List of transform functions
     */
    private function __construct(
        private array $transforms,
    ) {
    }

    /**
     * Creates a TransformList from an array of Transform objects.
     *
     * @param array<Transform> $transforms Array of Transform objects
     *
     * @return self A new TransformList instance
     */
    public static function fromArray(array $transforms): self
    {
        return new self($transforms);
    }

    /**
     * Parses a transform list string.
     *
     * @param string|null $value The transform attribute string to parse
     *
     * @return self A new TransformList instance
     *
     * @throws InvalidArgumentException if the value cannot be parsed
     */
    public static function parse(?string $value): self
    {
        if (null === $value || '' === trim($value)) {
            return new self([]);
        }

        $transforms = [];
        $pattern = '/([a-z]+)\s*\(\s*([^)]*)\s*\)/i';

        if (preg_match_all($pattern, $value, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $functionName = strtolower($match[1]);
                $args = self::parseArguments($match[2]);

                $transforms[] = match ($functionName) {
                    'translate' => self::createTranslate($args),
                    'scale' => self::createScale($args),
                    'rotate' => self::createRotate($args),
                    'skewx' => self::createSkewX($args),
                    'skewy' => self::createSkewY($args),
                    'matrix' => self::createMatrix($args),
                    default => throw new InvalidArgumentException("Unknown transform function: {$functionName}"),
                };
            }
        } else {
            throw new InvalidArgumentException("Invalid transform list format: {$value}");
        }

        return new self($transforms);
    }

    /**
     * Parses comma and/or whitespace separated arguments.
     *
     * @return array<string>
     */
    private static function parseArguments(string $argsString): array
    {
        // Replace commas with spaces and split by whitespace
        $parts = preg_split('/[\s,]+/', trim($argsString));

        assert(false !== $parts);

        return array_values(array_filter($parts, fn (string $arg) => '' !== $arg));
    }

    /**
     * Creates a translate transform.
     *
     * @param array<string> $args Arguments (tx, ty) - ty is optional
     */
    private static function createTranslate(array $args): Transform
    {
        $count = count($args);
        if ($count < 1 || $count > 2) {
            throw new InvalidArgumentException("Translate requires 1 or 2 arguments, got {$count}");
        }

        $tx = Length::parse($args[0]);
        $ty = $count > 1 ? Length::parse($args[1]) : Length::parse('0');

        return new TranslateTransform($tx, $ty);
    }

    /**
     * Creates a scale transform.
     *
     * @param array<string> $args Arguments (sx, sy) - sy is optional
     */
    private static function createScale(array $args): Transform
    {
        $count = count($args);
        if ($count < 1 || $count > 2) {
            throw new InvalidArgumentException("Scale requires 1 or 2 arguments, got {$count}");
        }

        $sx = (float) $args[0];
        $sy = $count > 1 ? (float) $args[1] : $sx;

        return new ScaleTransform($sx, $sy);
    }

    /**
     * Creates a rotate transform.
     *
     * @param array<string> $args Arguments (angle, cx, cy) - cx and cy are optional
     */
    private static function createRotate(array $args): Transform
    {
        $count = count($args);
        if ($count < 1 || $count > 3) {
            throw new InvalidArgumentException("Rotate requires 1 or 3 arguments, got {$count}");
        }

        $angle = Angle::parse($args[0]);

        if (1 === $count) {
            return new RotateTransform($angle);
        }

        if (3 === $count) {
            $cx = Length::parse($args[1]);
            $cy = Length::parse($args[2]);

            return new RotateTransform($angle, $cx, $cy);
        }

        throw new InvalidArgumentException('Rotate with center point requires both cx and cy parameters');
    }

    /**
     * Creates a skewX transform.
     *
     * @param array<string> $args
     */
    private static function createSkewX(array $args): Transform
    {
        if (1 !== count($args)) {
            throw new InvalidArgumentException('SkewX requires exactly 1 argument');
        }

        return new SkewXTransform(Angle::parse($args[0]));
    }

    /**
     * Creates a skewY transform.
     *
     * @param array<string> $args
     */
    private static function createSkewY(array $args): Transform
    {
        if (1 !== count($args)) {
            throw new InvalidArgumentException('SkewY requires exactly 1 argument');
        }

        return new SkewYTransform(Angle::parse($args[0]));
    }

    /**
     * Creates a matrix transform.
     *
     * @param array<string> $args
     */
    private static function createMatrix(array $args): Transform
    {
        if (6 !== count($args)) {
            throw new InvalidArgumentException('Matrix requires exactly 6 arguments');
        }

        return new MatrixTransform(
            (float) $args[0], (float) $args[1],
            (float) $args[2], (float) $args[3],
            (float) $args[4], (float) $args[5]
        );
    }

    /**
     * Returns all transforms in this list.
     *
     * @return array<Transform>
     */
    public function getTransforms(): array
    {
        return $this->transforms;
    }

    /**
     * Returns whether this transform list is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->transforms);
    }

    /**
     * Returns the number of transforms in this list.
     */
    public function count(): int
    {
        return count($this->transforms);
    }

    /**
     * Serializes to string format.
     */
    public function toString(): string
    {
        if ($this->isEmpty()) {
            return '';
        }

        return implode(' ', array_map(fn (Transform $t) => $t->toString(), $this->transforms));
    }

    /**
     * Magic method for string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
