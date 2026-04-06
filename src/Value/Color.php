<?php

declare(strict_types=1);

namespace Atelier\Svg\Value;

use Atelier\Svg\Exception\InvalidArgumentException;

/**
 * Represents an SVG color value.
 *
 * Supports various color formats:
 * - Named colors (e.g., "red", "blue")
 * - Hex colors (e.g., "#FF0000", "#F00")
 * - RGB/RGBA (e.g., "rgb(255, 0, 0)", "rgba(255, 0, 0, 0.5)")
 * - HSL/HSLA (e.g., "hsl(0, 100%, 50%)", "hsla(0, 100%, 50%, 0.5)")
 * - Special values ("none", "currentColor")
 *
 * @see https://www.w3.org/TR/SVG11/types.html#DataTypeColor
 * @see https://www.w3.org/TR/css-color-3/
 */
final readonly class Color implements \Stringable
{
    private const array NAMED_COLORS = [
        'aliceblue' => [240, 248, 255],
        'antiquewhite' => [250, 235, 215],
        'aqua' => [0, 255, 255],
        'aquamarine' => [127, 255, 212],
        'azure' => [240, 255, 255],
        'beige' => [245, 245, 220],
        'bisque' => [255, 228, 196],
        'black' => [0, 0, 0],
        'blanchedalmond' => [255, 235, 205],
        'blue' => [0, 0, 255],
        'blueviolet' => [138, 43, 226],
        'brown' => [165, 42, 42],
        'burlywood' => [222, 184, 135],
        'cadetblue' => [95, 158, 160],
        'chartreuse' => [127, 255, 0],
        'chocolate' => [210, 105, 30],
        'coral' => [255, 127, 80],
        'cornflowerblue' => [100, 149, 237],
        'cornsilk' => [255, 248, 220],
        'crimson' => [220, 20, 60],
        'cyan' => [0, 255, 255],
        'darkblue' => [0, 0, 139],
        'darkcyan' => [0, 139, 139],
        'darkgoldenrod' => [184, 134, 11],
        'darkgray' => [169, 169, 169],
        'darkgrey' => [169, 169, 169],
        'darkgreen' => [0, 100, 0],
        'darkkhaki' => [189, 183, 107],
        'darkmagenta' => [139, 0, 139],
        'darkolivegreen' => [85, 107, 47],
        'darkorange' => [255, 140, 0],
        'darkorchid' => [153, 50, 204],
        'darkred' => [139, 0, 0],
        'darksalmon' => [233, 150, 122],
        'darkseagreen' => [143, 188, 143],
        'darkslateblue' => [72, 61, 139],
        'darkslategray' => [47, 79, 79],
        'darkslategrey' => [47, 79, 79],
        'darkturquoise' => [0, 206, 209],
        'darkviolet' => [148, 0, 211],
        'deeppink' => [255, 20, 147],
        'deepskyblue' => [0, 191, 255],
        'dimgray' => [105, 105, 105],
        'dimgrey' => [105, 105, 105],
        'dodgerblue' => [30, 144, 255],
        'firebrick' => [178, 34, 34],
        'floralwhite' => [255, 250, 240],
        'forestgreen' => [34, 139, 34],
        'fuchsia' => [255, 0, 255],
        'gainsboro' => [220, 220, 220],
        'ghostwhite' => [248, 248, 255],
        'gold' => [255, 215, 0],
        'goldenrod' => [218, 165, 32],
        'gray' => [128, 128, 128],
        'grey' => [128, 128, 128],
        'green' => [0, 128, 0],
        'greenyellow' => [173, 255, 47],
        'honeydew' => [240, 255, 240],
        'hotpink' => [255, 105, 180],
        'indianred' => [205, 92, 92],
        'indigo' => [75, 0, 130],
        'ivory' => [255, 255, 240],
        'khaki' => [240, 230, 140],
        'lavender' => [230, 230, 250],
        'lavenderblush' => [255, 240, 245],
        'lawngreen' => [124, 252, 0],
        'lemonchiffon' => [255, 250, 205],
        'lightblue' => [173, 216, 230],
        'lightcoral' => [240, 128, 128],
        'lightcyan' => [224, 255, 255],
        'lightgoldenrodyellow' => [250, 250, 210],
        'lightgray' => [211, 211, 211],
        'lightgrey' => [211, 211, 211],
        'lightgreen' => [144, 238, 144],
        'lightpink' => [255, 182, 193],
        'lightsalmon' => [255, 160, 122],
        'lightseagreen' => [32, 178, 170],
        'lightskyblue' => [135, 206, 250],
        'lightslategray' => [119, 136, 153],
        'lightslategrey' => [119, 136, 153],
        'lightsteelblue' => [176, 196, 222],
        'lightyellow' => [255, 255, 224],
        'lime' => [0, 255, 0],
        'limegreen' => [50, 205, 50],
        'linen' => [250, 240, 230],
        'magenta' => [255, 0, 255],
        'maroon' => [128, 0, 0],
        'mediumaquamarine' => [102, 205, 170],
        'mediumblue' => [0, 0, 205],
        'mediumorchid' => [186, 85, 211],
        'mediumpurple' => [147, 112, 219],
        'mediumseagreen' => [60, 179, 113],
        'mediumslateblue' => [123, 104, 238],
        'mediumspringgreen' => [0, 250, 154],
        'mediumturquoise' => [72, 209, 204],
        'mediumvioletred' => [199, 21, 133],
        'midnightblue' => [25, 25, 112],
        'mintcream' => [245, 255, 250],
        'mistyrose' => [255, 228, 225],
        'moccasin' => [255, 228, 181],
        'navajowhite' => [255, 222, 173],
        'navy' => [0, 0, 128],
        'oldlace' => [253, 245, 230],
        'olive' => [128, 128, 0],
        'olivedrab' => [107, 142, 35],
        'orange' => [255, 165, 0],
        'orangered' => [255, 69, 0],
        'orchid' => [218, 112, 214],
        'palegoldenrod' => [238, 232, 170],
        'palegreen' => [152, 251, 152],
        'paleturquoise' => [175, 238, 238],
        'palevioletred' => [219, 112, 147],
        'papayawhip' => [255, 239, 213],
        'peachpuff' => [255, 218, 185],
        'peru' => [205, 133, 63],
        'pink' => [255, 192, 203],
        'plum' => [221, 160, 221],
        'powderblue' => [176, 224, 230],
        'purple' => [128, 0, 128],
        'rebeccapurple' => [102, 51, 153],
        'red' => [255, 0, 0],
        'rosybrown' => [188, 143, 143],
        'royalblue' => [65, 105, 225],
        'saddlebrown' => [139, 69, 19],
        'salmon' => [250, 128, 114],
        'sandybrown' => [244, 164, 96],
        'seagreen' => [46, 139, 87],
        'seashell' => [255, 245, 238],
        'sienna' => [160, 82, 45],
        'silver' => [192, 192, 192],
        'skyblue' => [135, 206, 235],
        'slateblue' => [106, 90, 205],
        'slategray' => [112, 128, 144],
        'slategrey' => [112, 128, 144],
        'snow' => [255, 250, 250],
        'springgreen' => [0, 255, 127],
        'steelblue' => [70, 130, 180],
        'tan' => [210, 180, 140],
        'teal' => [0, 128, 128],
        'thistle' => [216, 191, 216],
        'tomato' => [255, 99, 71],
        'turquoise' => [64, 224, 208],
        'violet' => [238, 130, 238],
        'wheat' => [245, 222, 179],
        'white' => [255, 255, 255],
        'whitesmoke' => [245, 245, 245],
        'yellow' => [255, 255, 0],
        'yellowgreen' => [154, 205, 50],
    ];

    private function __construct(
        private int $red,
        private int $green,
        private int $blue,
        private float $alpha = 1.0,
        private ?string $originalFormat = null,
    ) {
    }

    /**
     * Creates a Color from RGB values.
     *
     * @param int   $red   Red component (0-255)
     * @param int   $green Green component (0-255)
     * @param int   $blue  Blue component (0-255)
     * @param float $alpha Alpha component (0.0-1.0)
     */
    public static function fromRgb(int $red, int $green, int $blue, float $alpha = 1.0): self
    {
        if ($red < 0 || $red > 255 || $green < 0 || $green > 255 || $blue < 0 || $blue > 255) {
            throw new InvalidArgumentException('RGB values must be between 0 and 255');
        }

        if ($alpha < 0.0 || $alpha > 1.0) {
            throw new InvalidArgumentException('Alpha value must be between 0.0 and 1.0');
        }

        return new self($red, $green, $blue, $alpha);
    }

    /**
     * Parses a color string into a Color object.
     *
     * @param string $value The color string to parse
     */
    public static function parse(string $value): self
    {
        $value = trim(strtolower($value));

        if ('' === $value) {
            throw new InvalidArgumentException('Cannot parse an empty string as a Color.');
        }

        // Special values
        if ('none' === $value || 'transparent' === $value) {
            return new self(0, 0, 0, 0.0, $value);
        }

        if ('currentcolor' === $value) {
            return new self(0, 0, 0, 1.0, $value);
        }

        // Named colors
        if (isset(self::NAMED_COLORS[$value])) {
            [$r, $g, $b] = self::NAMED_COLORS[$value];

            return new self($r, $g, $b, 1.0, $value);
        }

        // Hex colors (#RGB or #RRGGBB or #RRGGBBAA)
        if (str_starts_with($value, '#')) {
            return self::parseHex($value);
        }

        // RGB/RGBA
        if (preg_match('/^rgba?\s*\(\s*(.+?)\s*\)$/i', $value, $matches)) {
            return self::parseRgb($matches[1], str_starts_with($value, 'rgba'));
        }

        // HSL/HSLA
        if (preg_match('/^hsla?\s*\(\s*(.+?)\s*\)$/i', $value, $matches)) {
            return self::parseHsl($matches[1], str_starts_with($value, 'hsla'));
        }

        throw new InvalidArgumentException(sprintf("Invalid color format: '%s'", $value));
    }

    private static function parseHex(string $hex): self
    {
        $hex = ltrim($hex, '#');
        $len = strlen($hex);

        if (3 === $len) {
            // #RGB -> #RRGGBB
            $r = hexdec($hex[0].$hex[0]);
            $g = hexdec($hex[1].$hex[1]);
            $b = hexdec($hex[2].$hex[2]);
            $a = 1.0;
        } elseif (4 === $len) {
            // #RGBA -> #RRGGBBAA
            $r = hexdec($hex[0].$hex[0]);
            $g = hexdec($hex[1].$hex[1]);
            $b = hexdec($hex[2].$hex[2]);
            $a = hexdec($hex[3].$hex[3]) / 255;
        } elseif (6 === $len) {
            // #RRGGBB
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $a = 1.0;
        } elseif (8 === $len) {
            // #RRGGBBAA
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $a = hexdec(substr($hex, 6, 2)) / 255;
        } else {
            throw new InvalidArgumentException(sprintf("Invalid hex color format: '#%s'", $hex));
        }

        return new self((int) $r, (int) $g, (int) $b, $a, '#'.$hex);
    }

    private static function parseRgb(string $values, bool $hasAlpha): self
    {
        $parts = array_map(trim(...), explode(',', $values));

        if ((!$hasAlpha && 3 !== count($parts)) || ($hasAlpha && 4 !== count($parts))) {
            throw new InvalidArgumentException('Invalid RGB/RGBA format');
        }

        $r = self::parseRgbValue($parts[0]);
        $g = self::parseRgbValue($parts[1]);
        $b = self::parseRgbValue($parts[2]);
        $a = $hasAlpha ? (float) $parts[3] : 1.0;

        return new self($r, $g, $b, $a, $hasAlpha ? 'rgba' : 'rgb');
    }

    private static function parseRgbValue(string $value): int
    {
        $value = trim($value);

        // Handle percentage values
        if (str_ends_with($value, '%')) {
            $percent = (float) rtrim($value, '%');

            return (int) round(($percent / 100) * 255);
        }

        return (int) $value;
    }

    private static function parseHsl(string $values, bool $hasAlpha): self
    {
        $parts = array_map(trim(...), explode(',', $values));

        if ((!$hasAlpha && 3 !== count($parts)) || ($hasAlpha && 4 !== count($parts))) {
            throw new InvalidArgumentException('Invalid HSL/HSLA format');
        }

        $h = (float) rtrim($parts[0], 'deg');
        $s = (float) rtrim($parts[1], '%') / 100;
        $l = (float) rtrim($parts[2], '%') / 100;
        $a = $hasAlpha ? (float) $parts[3] : 1.0;

        // Convert HSL to RGB
        [$r, $g, $b] = self::hslToRgb($h, $s, $l);

        return new self($r, $g, $b, $a, $hasAlpha ? 'hsla' : 'hsl');
    }

    /**
     * @return array{0: int, 1: int, 2: int} RGB values
     */
    private static function hslToRgb(float $h, float $s, float $l): array
    {
        $h = fmod($h, 360) / 360;

        if (0.0 === $s) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = self::hueToRgb($p, $q, $h + 1 / 3);
            $g = self::hueToRgb($p, $q, $h);
            $b = self::hueToRgb($p, $q, $h - 1 / 3);
        }

        return [
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255),
        ];
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            ++$t;
        }
        if ($t > 1) {
            --$t;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    public function getAlpha(): float
    {
        return $this->alpha;
    }

    public function isTransparent(): bool
    {
        return 0.0 === $this->alpha;
    }

    public function isOpaque(): bool
    {
        return 1.0 === $this->alpha;
    }

    /**
     * Returns the color as a hex string (#RRGGBB or #RRGGBBAA if not opaque).
     */
    public function toHex(): string
    {
        $hex = sprintf('#%02x%02x%02x', $this->red, $this->green, $this->blue);

        if (!$this->isOpaque()) {
            $hex .= sprintf('%02x', (int) round($this->alpha * 255));
        }

        return $hex;
    }

    /**
     * Returns the color as an RGB or RGBA string.
     */
    public function toRgb(): string
    {
        if ($this->isOpaque()) {
            return sprintf('rgb(%d, %d, %d)', $this->red, $this->green, $this->blue);
        }

        return sprintf('rgba(%d, %d, %d, %.2f)', $this->red, $this->green, $this->blue, $this->alpha);
    }

    /**
     * Serializes the Color to its string representation.
     * Prefers the original format if available, otherwise uses hex.
     */
    public function toString(): string
    {
        if ('none' === $this->originalFormat || 'transparent' === $this->originalFormat || 'currentcolor' === $this->originalFormat) {
            return $this->originalFormat;
        }

        // If we have a named color that matches, use it
        foreach (self::NAMED_COLORS as $name => [$r, $g, $b]) {
            if ($r === $this->red && $g === $this->green && $b === $this->blue && $this->isOpaque()) {
                return $name;
            }
        }

        return $this->toHex();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
