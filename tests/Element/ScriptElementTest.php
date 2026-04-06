<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element;

use Atelier\Svg\Element\ScriptElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ScriptElement::class)]
final class ScriptElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $script = new ScriptElement();

        $this->assertSame('script', $script->getTagName());
    }

    public function testConstructorSetsDefaultType(): void
    {
        $script = new ScriptElement();

        $this->assertSame('text/javascript', $script->getType());
        $this->assertSame('text/javascript', $script->getAttribute('type'));
    }

    public function testSetAndGetType(): void
    {
        $script = new ScriptElement();
        $result = $script->setType('application/ecmascript');

        $this->assertSame($script, $result, 'setType should return self for chaining');
        $this->assertSame('application/ecmascript', $script->getType());
        $this->assertSame('application/ecmascript', $script->getAttribute('type'));
    }

    public function testSetAndGetContent(): void
    {
        $script = new ScriptElement();
        $js = 'console.log("Hello World");';
        $result = $script->setContent($js);

        $this->assertSame($script, $result, 'setContent should return self for chaining');
        $this->assertSame($js, $script->getContent());
    }

    public function testGetContentReturnsNullWhenNotSet(): void
    {
        $script = new ScriptElement();

        $this->assertNull($script->getContent());
    }

    public function testSetContentWithEmptyString(): void
    {
        $script = new ScriptElement();
        $script->setContent('');

        $this->assertSame('', $script->getContent());
    }

    public function testSetContentWithMultilineJavaScript(): void
    {
        $script = new ScriptElement();
        $js = "function init() {\n  console.log('Initialized');\n}";
        $script->setContent($js);

        $this->assertSame($js, $script->getContent());
    }

    public function testSetContentOverwritesPreviousContent(): void
    {
        $script = new ScriptElement();
        $script->setContent('console.log("first");');
        $script->setContent('console.log("second");');

        $this->assertSame('console.log("second");', $script->getContent());
    }

    public function testMethodChaining(): void
    {
        $script = new ScriptElement();
        $result = $script
            ->setAttribute('id', 'main-script')
            ->setType('text/javascript')
            ->setContent('alert("test");');

        $this->assertSame($script, $result);
        $this->assertSame('main-script', $script->getAttribute('id'));
        $this->assertSame('text/javascript', $script->getType());
        $this->assertSame('alert("test");', $script->getContent());
    }

    public function testCompleteScriptConfiguration(): void
    {
        $script = new ScriptElement();
        $js = <<<JS
(function() {
  'use strict';

  function animateCircle(circle) {
    let radius = parseFloat(circle.getAttribute('r'));
    let growing = true;

    setInterval(function() {
      if (growing) {
        radius += 0.5;
        if (radius >= 50) growing = false;
      } else {
        radius -= 0.5;
        if (radius <= 10) growing = true;
      }
      circle.setAttribute('r', radius);
    }, 50);
  }

  window.addEventListener('load', function() {
    const circles = document.querySelectorAll('circle');
    circles.forEach(animateCircle);
  });
})();
JS;

        $script
            ->setAttribute('id', 'svg-animation')
            ->setType('text/javascript')
            ->setContent($js);

        $this->assertSame('svg-animation', $script->getAttribute('id'));
        $this->assertSame('text/javascript', $script->getType());
        $this->assertSame($js, $script->getContent());
    }

    public function testScriptWithEventHandler(): void
    {
        $script = new ScriptElement();
        $js = <<<JS
document.addEventListener('DOMContentLoaded', function() {
  const svg = document.querySelector('svg');
  svg.addEventListener('click', function(e) {
    console.log('SVG clicked at:', e.clientX, e.clientY);
  });
});
JS;

        $script->setContent($js);

        $this->assertSame($js, $script->getContent());
    }

    public function testScriptWithES6Syntax(): void
    {
        $script = new ScriptElement();
        $js = <<<JS
const animatePath = (path) => {
  const length = path.getTotalLength();
  path.style.strokeDasharray = length;
  path.style.strokeDashoffset = length;

  setTimeout(() => {
    path.style.transition = 'stroke-dashoffset 2s ease-in-out';
    path.style.strokeDashoffset = 0;
  }, 100);
};
JS;

        $script->setContent($js);

        $this->assertSame($js, $script->getContent());
    }

    public function testScriptWithSpecialCharacters(): void
    {
        $script = new ScriptElement();
        $js = 'const message = "Hello \"World\"!";';
        $script->setContent($js);

        $this->assertSame($js, $script->getContent());
    }

    public function testScriptResetToDefaultType(): void
    {
        $script = new ScriptElement();
        $script->setType('application/ecmascript');
        $script->setType('text/javascript');

        $this->assertSame('text/javascript', $script->getType());
    }
}
