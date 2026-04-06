<?php

declare(strict_types=1);

namespace Atelier\Svg\Morphing;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Animation\AnimateElement;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Path\Data;

/**
 * Utility class for exporting morphing animations to various formats.
 */
final class AnimationExporter
{
    /**
     * Exports frames as SVG with SMIL animation.
     *
     * @param Data[]               $frames  Array of path data frames
     * @param array<string, mixed> $options Animation options (duration, repeatCount, etc.)
     */
    public static function toAnimatedSVG(array $frames, array $options = []): Document
    {
        $doc = new Document(new \Atelier\Svg\Element\SvgElement());
        $svg = $doc->getRootElement();
        assert(null !== $svg);

        // Set viewBox from options or use default
        $viewBox = isset($options['viewBox']) && (is_string($options['viewBox']) || $options['viewBox'] instanceof \Stringable) ? (string) $options['viewBox'] : '0 0 200 200';
        $svg->setAttribute('viewBox', $viewBox);

        if (isset($options['width']) && (is_scalar($options['width']) || $options['width'] instanceof \Stringable)) {
            $svg->setAttribute('width', (string) $options['width']);
        }
        if (isset($options['height']) && (is_scalar($options['height']) || $options['height'] instanceof \Stringable)) {
            $svg->setAttribute('height', (string) $options['height']);
        }

        // Create path element
        $path = new PathElement();
        $path->setData($frames[0]);

        // Set styling
        $fill = isset($options['fill']) && (is_string($options['fill']) || $options['fill'] instanceof \Stringable) ? (string) $options['fill'] : '#000000';
        $stroke = isset($options['stroke']) && (is_string($options['stroke']) || $options['stroke'] instanceof \Stringable) ? (string) $options['stroke'] : 'none';
        $strokeWidth = isset($options['strokeWidth']) && (is_scalar($options['strokeWidth']) || $options['strokeWidth'] instanceof \Stringable) ? (string) $options['strokeWidth'] : '1';

        $path->setAttribute('fill', $fill);
        $path->setAttribute('stroke', $stroke);
        $path->setAttribute('stroke-width', $strokeWidth);

        // Create SMIL animation
        $animate = new AnimateElement();
        $animate->setAttribute('attributeName', 'd');
        $duration = isset($options['duration']) && (is_scalar($options['duration']) || $options['duration'] instanceof \Stringable) ? (string) $options['duration'] : '3';
        $animate->setAttribute('dur', $duration.'s');
        $repeatCount = isset($options['repeatCount']) && (is_scalar($options['repeatCount']) || $options['repeatCount'] instanceof \Stringable) ? (string) $options['repeatCount'] : 'indefinite';
        $animate->setAttribute('repeatCount', $repeatCount);

        // Convert frames to values string
        $values = array_map(fn ($frame) => $frame->toString(), $frames);
        $animate->setAttribute('values', implode('; ', $values));

        // Add easing
        if (isset($options['calcMode']) && (is_string($options['calcMode']) || $options['calcMode'] instanceof \Stringable)) {
            $animate->setAttribute('calcMode', (string) $options['calcMode']);
        }

        if (isset($options['keySplines']) && (is_string($options['keySplines']) || $options['keySplines'] instanceof \Stringable)) {
            $animate->setAttribute('keySplines', (string) $options['keySplines']);
        }

        $path->appendChild($animate);
        $svg->appendChild($path);

        return $doc;
    }

    /**
     * Exports frames as CSS keyframes animation code.
     *
     * @param Data[] $frames
     *
     * @return string CSS code
     */
    public static function toCSSKeyframes(array $frames, string $animationName = 'morph'): string
    {
        $css = "@keyframes $animationName {\n";

        $frameCount = count($frames);
        foreach ($frames as $i => $frame) {
            $percentage = $frameCount > 1 ? ($i / ($frameCount - 1)) * 100 : 0;
            $pathData = $frame->toString();

            // Escape quotes in path data
            $pathData = str_replace('"', '\\"', $pathData);

            $css .= sprintf("  %.1f%% {\n", $percentage);
            $css .= "    d: path(\"$pathData\");\n";
            $css .= "  }\n";
        }

        $css .= "}\n\n";

        // Add example usage
        $css .= "/* Usage:\n";
        $css .= ".morphing-path {\n";
        $css .= "  animation: $animationName 3s ease-in-out infinite;\n";
        $css .= "}\n";
        $css .= "*/\n";

        return $css;
    }

    /**
     * Exports frames as JavaScript array for programmatic animation.
     *
     * @param Data[] $frames
     *
     * @return string JavaScript code
     */
    public static function toJavaScript(array $frames, string $variableName = 'morphFrames'): string
    {
        $js = "// Morphing animation frames\n";
        $js .= "const $variableName = [\n";

        foreach ($frames as $i => $frame) {
            $pathData = $frame->toString();
            // Escape quotes and backslashes
            $pathData = str_replace(['\\', '"'], ['\\\\', '\\"'], $pathData);

            $js .= "  \"$pathData\"";
            $js .= $i < count($frames) - 1 ? ",\n" : "\n";
        }

        $js .= "];\n\n";

        // Add example usage
        $js .= "// Example usage:\n";
        $js .= "// const path = document.querySelector('.morph-path');\n";
        $js .= "// let frameIndex = 0;\n";
        $js .= "// setInterval(() => {\n";
        $js .= "//   path.setAttribute('d', {$variableName}[frameIndex]);\n";
        $js .= "//   frameIndex = (frameIndex + 1) % {$variableName}.length;\n";
        $js .= "// }, 1000 / 60); // 60fps\n";

        return $js;
    }

    /**
     * Exports frames as JSON data.
     *
     * @param Data[]               $frames
     * @param array<string, mixed> $metadata Optional metadata to include
     *
     * @return string JSON
     */
    public static function toJSON(array $frames, array $metadata = []): string
    {
        $data = [
            'version' => '1.0',
            'frameCount' => count($frames),
            'metadata' => $metadata,
            'frames' => array_map(fn ($frame) => $frame->toString(), $frames),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT);
        assert(is_string($json));

        return $json;
    }

    /**
     * Exports frames as individual SVG files for sprite sheet.
     *
     * @param Data[]               $frames
     * @param string               $outputDir Directory to save files
     * @param array<string, mixed> $options   SVG options
     */
    public static function toSpriteSheet(array $frames, string $outputDir, array $options = []): void
    {
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $viewBox = isset($options['viewBox']) && (is_string($options['viewBox']) || $options['viewBox'] instanceof \Stringable) ? (string) $options['viewBox'] : '0 0 200 200';
        $width = isset($options['width']) && (is_string($options['width']) || $options['width'] instanceof \Stringable) ? (string) $options['width'] : '200';
        $height = isset($options['height']) && (is_string($options['height']) || $options['height'] instanceof \Stringable) ? (string) $options['height'] : '200';
        $fill = isset($options['fill']) && (is_string($options['fill']) || $options['fill'] instanceof \Stringable) ? (string) $options['fill'] : '#000000';
        $stroke = isset($options['stroke']) && (is_string($options['stroke']) || $options['stroke'] instanceof \Stringable) ? (string) $options['stroke'] : 'none';

        foreach ($frames as $i => $frame) {
            $doc = new Document(new \Atelier\Svg\Element\SvgElement());
            $svg = $doc->getRootElement();
            assert(null !== $svg);
            $svg->setAttribute('viewBox', $viewBox);
            $svg->setAttribute('width', $width);
            $svg->setAttribute('height', $height);

            $path = new PathElement();
            $path->setData($frame);
            $path->setAttribute('fill', $fill);
            $path->setAttribute('stroke', $stroke);

            $svg->appendChild($path);

            $filename = sprintf('%s/frame-%04d.svg', $outputDir, $i);
            $dumper = new \Atelier\Svg\Dumper\CompactXmlDumper();
            file_put_contents($filename, $dumper->dump($doc));
        }
    }

    /**
     * Exports as Web Animations API JavaScript.
     *
     * @param Data[]               $frames
     * @param array<string, mixed> $options Animation options
     *
     * @return string JavaScript code
     */
    public static function toWebAnimationsAPI(array $frames, array $options = []): string
    {
        $durationVal = $options['duration'] ?? 3;
        $duration = (is_numeric($durationVal) ? (float) $durationVal : 3.0) * 1000; // Convert to ms
        $easing = isset($options['easing']) && (is_string($options['easing']) || $options['easing'] instanceof \Stringable) ? (string) $options['easing'] : 'ease-in-out';
        $iterations = isset($options['iterations']) && (is_string($options['iterations']) || $options['iterations'] instanceof \Stringable) ? (string) $options['iterations'] : 'Infinity';

        $keyframes = array_map(
            fn ($frame) => ['d' => "path('".$frame->toString()."')"],
            $frames
        );

        $js = "// Web Animations API morphing animation\n";
        $js .= 'const morphKeyframes = '.json_encode($keyframes, JSON_PRETTY_PRINT).";\n\n";

        $js .= "const morphOptions = {\n";
        $js .= "  duration: $duration,\n";
        $js .= "  easing: '$easing',\n";
        $js .= "  iterations: $iterations\n";
        $js .= "};\n\n";

        $js .= "// Apply to element:\n";
        $js .= "// const path = document.querySelector('.morph-path');\n";
        $js .= "// path.animate(morphKeyframes, morphOptions);\n";

        return $js;
    }

    /**
     * Creates a debug visualization of the morphing process.
     *
     * @param Data[] $frames Sample frames to visualize
     * @param int    $cols   Number of columns in grid
     *
     * @return Document SVG document showing frame progression
     */
    public static function createDebugVisualization(array $frames, int $cols = 5): Document
    {
        $doc = new Document(new \Atelier\Svg\Element\SvgElement());
        $svg = $doc->getRootElement();
        assert(null !== $svg);

        $frameWidth = 200;
        $frameHeight = 200;
        $padding = 20;

        $frameCount = count($frames);
        $rows = (int) ceil($frameCount / $cols);

        $totalWidth = $cols * ($frameWidth + $padding) + $padding;
        $totalHeight = $rows * ($frameHeight + $padding) + $padding;

        $svg->setAttribute('viewBox', "0 0 $totalWidth $totalHeight");
        $svg->setAttribute('width', (string) $totalWidth);
        $svg->setAttribute('height', (string) $totalHeight);

        // Background
        $bg = new \Atelier\Svg\Element\Shape\RectElement();
        $bg->setAttribute('width', (string) $totalWidth);
        $bg->setAttribute('height', (string) $totalHeight);
        $bg->setAttribute('fill', '#f5f5f5');
        $svg->appendChild($bg);

        // Draw each frame
        foreach ($frames as $i => $frame) {
            $col = $i % $cols;
            $row = (int) ($i / $cols);

            $x = $padding + $col * ($frameWidth + $padding);
            $y = $padding + $row * ($frameHeight + $padding);

            // Frame background
            $frameBg = new \Atelier\Svg\Element\Shape\RectElement();
            $frameBg->setAttribute('x', (string) $x);
            $frameBg->setAttribute('y', (string) $y);
            $frameBg->setAttribute('width', (string) $frameWidth);
            $frameBg->setAttribute('height', (string) $frameHeight);
            $frameBg->setAttribute('fill', 'white');
            $frameBg->setAttribute('stroke', '#ddd');
            $svg->appendChild($frameBg);

            // Create group for frame
            $group = new \Atelier\Svg\Element\Structural\GroupElement();
            $group->setAttribute('transform', "translate($x, $y)");

            // Path
            $path = new PathElement();
            $path->setData($frame);
            $path->setAttribute('fill', '#4a90e2');
            $path->setAttribute('fill-opacity', '0.7');
            $path->setAttribute('stroke', '#2c5aa0');
            $path->setAttribute('stroke-width', '2');

            $group->appendChild($path);

            // Frame label
            $text = new \Atelier\Svg\Element\Text\TextElement();
            $text->setAttribute('x', (string) ($frameWidth / 2));
            $text->setAttribute('y', (string) ($frameHeight - 10));
            $text->setAttribute('text-anchor', 'middle');
            $text->setAttribute('font-size', '12');
            $text->setAttribute('fill', '#666');
            $text->setTextContent("Frame $i");

            $group->appendChild($text);
            $svg->appendChild($group);
        }

        return $doc;
    }
}
