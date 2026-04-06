<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\StyleElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StyleElement::class)]
final class StyleElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $style = new StyleElement();

        $this->assertSame('style', $style->getTagName());
    }

    public function testConstructorSetsDefaultType(): void
    {
        $style = new StyleElement();

        $this->assertSame('text/css', $style->getType());
        $this->assertSame('text/css', $style->getAttribute('type'));
    }

    public function testSetAndGetType(): void
    {
        $style = new StyleElement();
        $result = $style->setType('text/less');

        $this->assertSame($style, $result, 'setType should return self for chaining');
        $this->assertSame('text/less', $style->getType());
        $this->assertSame('text/less', $style->getAttribute('type'));
    }

    public function testSetAndGetContent(): void
    {
        $style = new StyleElement();
        $css = '.circle { fill: red; }';
        $result = $style->setContent($css);

        $this->assertSame($style, $result, 'setContent should return self for chaining');
        $this->assertSame($css, $style->getContent());
    }

    public function testGetContentReturnsNullWhenNotSet(): void
    {
        $style = new StyleElement();

        $this->assertNull($style->getContent());
    }

    public function testSetContentWithEmptyString(): void
    {
        $style = new StyleElement();
        $style->setContent('');

        $this->assertSame('', $style->getContent());
    }

    public function testSetContentWithMultilineCSS(): void
    {
        $style = new StyleElement();
        $css = ".circle {\n  fill: red;\n  stroke: blue;\n}";
        $style->setContent($css);

        $this->assertSame($css, $style->getContent());
    }

    public function testSetContentOverwritesPreviousContent(): void
    {
        $style = new StyleElement();
        $style->setContent('.first { color: red; }');
        $style->setContent('.second { color: blue; }');

        $this->assertSame('.second { color: blue; }', $style->getContent());
    }

    public function testMethodChaining(): void
    {
        $style = new StyleElement();
        $result = $style
            ->setAttribute('id', 'main-styles')
            ->setType('text/css')
            ->setContent('.element { fill: green; }');

        $this->assertSame($style, $result);
        $this->assertSame('main-styles', $style->getAttribute('id'));
        $this->assertSame('text/css', $style->getType());
        $this->assertSame('.element { fill: green; }', $style->getContent());
    }

    public function testCompleteStyleConfiguration(): void
    {
        $style = new StyleElement();
        $css = <<<CSS
.rect {
  fill: blue;
  stroke: black;
  stroke-width: 2;
}
.circle {
  fill: red;
}
CSS;

        $style
            ->setAttribute('id', 'svg-styles')
            ->setType('text/css')
            ->setContent($css);

        $this->assertSame('svg-styles', $style->getAttribute('id'));
        $this->assertSame('text/css', $style->getType());
        $this->assertSame($css, $style->getContent());
    }

    public function testStyleWithComplexCSS(): void
    {
        $style = new StyleElement();
        $css = <<<CSS
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.animated {
  animation: pulse 2s infinite;
}

.gradient-text {
  fill: url(#gradient);
}
CSS;

        $style->setContent($css);

        $this->assertSame($css, $style->getContent());
    }

    public function testStyleWithMediaQueries(): void
    {
        $style = new StyleElement();
        $css = <<<CSS
@media (max-width: 600px) {
  .responsive {
    font-size: 12px;
  }
}
CSS;

        $style->setContent($css);

        $this->assertSame($css, $style->getContent());
    }

    public function testStyleWithSpecialCharacters(): void
    {
        $style = new StyleElement();
        $css = '.selector::before { content: "→"; }';
        $style->setContent($css);

        $this->assertSame($css, $style->getContent());
    }

    public function testStyleResetToDefaultType(): void
    {
        $style = new StyleElement();
        $style->setType('custom/type');
        $style->setType('text/css');

        $this->assertSame('text/css', $style->getType());
    }
}
