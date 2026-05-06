---
order: 999
private: true
description: "POC: SVG Viewer component for documentation."
---

# SVG Viewer POC

## Preview only

<div class="svg-viewer" data-mode="preview">
<div class="svg-viewer-preview">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect x="20" y="20" width="160" height="160" rx="12" fill="#3b82f6" opacity="0.15"/>
  <circle cx="100" cy="100" r="50" fill="none" stroke="#3b82f6" stroke-width="2"/>
  <circle cx="100" cy="100" r="4" fill="#3b82f6"/>
</svg>
</div>
</div>

## Code only

<div class="svg-viewer" data-mode="code">
<pre class="svg-viewer-code"><code>$svg = Svg::create(200, 200);
$svg->rect(20, 20, 160, 160, [
    'rx'   => 12,
    'fill' => '#3b82f6',
    'opacity' => 0.15,
]);
$svg->circle(100, 100, 50, [
    'fill'         => 'none',
    'stroke'       => '#3b82f6',
    'stroke-width' => 2,
]);</code></pre>
</div>

## Both: preview + code (highlight values)

<div class="svg-viewer" data-mode="split" data-highlight=".polar-string">
<div class="svg-viewer-preview">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <rect x="20" y="20" width="160" height="160" rx="12" fill="#3b82f6" opacity="0.15"/>
  <circle cx="100" cy="100" r="50" fill="none" stroke="#3b82f6" stroke-width="2"/>
  <circle cx="100" cy="100" r="4" fill="#3b82f6"/>
</svg>
</div>
<pre class="svg-viewer-code"><code>$svg = Svg::create(200, 200);
$svg->rect(20, 20, 160, 160, [
    'rx'   => 12,
    'fill' => '#3b82f6',
    'opacity' => 0.15,
]);
$svg->circle(100, 100, 50, [
    'fill'         => 'none',
    'stroke'       => '#3b82f6',
    'stroke-width' => 2,
]);</code></pre>
</div>

## Split: highlight properties

<div class="svg-viewer" data-mode="split" data-highlight=".polar-function-call">
<div class="svg-viewer-preview">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 120">
  <line x1="20" y1="60" x2="180" y2="60" stroke="#6a6a7a" stroke-width="1" stroke-dasharray="4 3"/>
  <polygon points="100,15 140,50 130,95 70,95 60,50" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linejoin="round"/>
  <circle cx="100" cy="15" r="3" fill="#3b82f6"/>
  <circle cx="140" cy="50" r="3" fill="#3b82f6"/>
  <circle cx="130" cy="95" r="3" fill="#3b82f6"/>
  <circle cx="70" cy="95" r="3" fill="#3b82f6"/>
  <circle cx="60" cy="50" r="3" fill="#3b82f6"/>
</svg>
</div>
<pre class="svg-viewer-code"><code>$svg = Svg::create(200, 120);
$svg->line(20, 60, 180, 60, [
    'stroke-dasharray' => '4 3',
]);
$svg->polygon([
    [100, 15], [140, 50],
    [130, 95], [70, 95], [60, 50],
], [
    'stroke-linejoin' => 'round',
]);</code></pre>
</div>

## Split: highlight specific lines

<div class="svg-viewer" data-mode="split" data-highlight-lines-lines-value="3,4,5">
<div class="svg-viewer-preview">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
  <defs>
    <radialGradient id="glow">
      <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.6"/>
      <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="100" cy="100" r="80" fill="url(#glow)"/>
  <circle cx="100" cy="100" r="30" fill="none" stroke="#3b82f6" stroke-width="1.5"/>
</svg>
</div>
<pre class="svg-viewer-code"><code>$svg = Svg::create(200, 200);
// Define a radial gradient
$svg->radialGradient('glow')
    ->stop('0%',   '#3b82f6', 0.6)
    ->stop('100%', '#3b82f6', 0);
$svg->circle(100, 100, 80, [
    'fill' => 'url(#glow)',
]);</code></pre>
</div>
