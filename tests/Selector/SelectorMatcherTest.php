<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Selector;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Selector\SelectorMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SelectorMatcher::class)]
final class SelectorMatcherTest extends TestCase
{
    private SelectorMatcher $matcher;
    private Document $doc;

    protected function setUp(): void
    {
        $this->matcher = new SelectorMatcher();
        $this->doc = Document::create();
    }

    public function testUniversalSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $circle = new CircleElement($this->doc->getRoot());

        $this->assertTrue($this->matcher->matches($rect, '*'));
        $this->assertTrue($this->matcher->matches($circle, '*'));
    }

    public function testIdSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('id', 'myRect');

        $this->assertTrue($this->matcher->matches($rect, '#myRect'));
        $this->assertFalse($this->matcher->matches($rect, '#otherId'));
    }

    public function testIdSelectorNoId(): void
    {
        $rect = new RectElement($this->doc->getRoot());

        $this->assertFalse($this->matcher->matches($rect, '#myRect'));
    }

    public function testClassSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->addClass('highlighted');

        $this->assertTrue($this->matcher->matches($rect, '.highlighted'));
        $this->assertFalse($this->matcher->matches($rect, '.other'));
    }

    public function testClassSelectorMultipleClasses(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->addClass('foo');
        $rect->addClass('bar');
        $rect->addClass('baz');

        $this->assertTrue($this->matcher->matches($rect, '.foo'));
        $this->assertTrue($this->matcher->matches($rect, '.bar'));
        $this->assertTrue($this->matcher->matches($rect, '.baz'));
        $this->assertFalse($this->matcher->matches($rect, '.notThere'));
    }

    public function testTagSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $circle = new CircleElement($this->doc->getRoot());

        $this->assertTrue($this->matcher->matches($rect, 'rect'));
        $this->assertFalse($this->matcher->matches($rect, 'circle'));

        $this->assertTrue($this->matcher->matches($circle, 'circle'));
        $this->assertFalse($this->matcher->matches($circle, 'rect'));
    }

    public function testAttributeExistenceSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('data-test', 'value');

        $this->assertTrue($this->matcher->matches($rect, '[data-test]'));
        $this->assertFalse($this->matcher->matches($rect, '[data-other]'));
    }

    public function testAttributeExactMatchSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('fill', 'red');

        $this->assertTrue($this->matcher->matches($rect, '[fill="red"]'));
        $this->assertFalse($this->matcher->matches($rect, '[fill="blue"]'));
    }

    public function testAttributeExactMatchSelectorNoAttribute(): void
    {
        $rect = new RectElement($this->doc->getRoot());

        $this->assertFalse($this->matcher->matches($rect, '[fill="red"]'));
    }

    public function testAttributeWordInListSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('class', 'foo bar baz');

        $this->assertTrue($this->matcher->matches($rect, '[class~="foo"]'));
        $this->assertTrue($this->matcher->matches($rect, '[class~="bar"]'));
        $this->assertTrue($this->matcher->matches($rect, '[class~="baz"]'));
        $this->assertFalse($this->matcher->matches($rect, '[class~="foobar"]'));
    }

    public function testAttributeDashPrefixSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('lang', 'en-US');

        $this->assertTrue($this->matcher->matches($rect, '[lang|="en"]'));
        $this->assertFalse($this->matcher->matches($rect, '[lang|="us"]'));

        $rect->setAttribute('lang', 'fr');
        $this->assertTrue($this->matcher->matches($rect, '[lang|="fr"]'));
    }

    public function testAttributeStartsWithSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('href', 'https://example.com');

        $this->assertTrue($this->matcher->matches($rect, '[href^="https://"]'));
        $this->assertTrue($this->matcher->matches($rect, '[href^="https"]'));
        $this->assertFalse($this->matcher->matches($rect, '[href^="http://"]'));
    }

    public function testAttributeEndsWithSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('href', 'document.pdf');

        $this->assertTrue($this->matcher->matches($rect, '[href$=".pdf"]'));
        $this->assertTrue($this->matcher->matches($rect, '[href$="pdf"]'));
        $this->assertFalse($this->matcher->matches($rect, '[href$=".doc"]'));
    }

    public function testAttributeContainsSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('title', 'This is a test');

        $this->assertTrue($this->matcher->matches($rect, '[title*="is"]'));
        $this->assertTrue($this->matcher->matches($rect, '[title*="test"]'));
        $this->assertTrue($this->matcher->matches($rect, '[title*=" a "]'));
        $this->assertFalse($this->matcher->matches($rect, '[title*="foo"]'));
    }

    public function testSelectorWithWhitespace(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('id', 'test');

        $this->assertTrue($this->matcher->matches($rect, '  #test  '));
        $this->assertTrue($this->matcher->matches($rect, '  rect  '));
    }

    public function testAttributeSelectorWithColon(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('xlink:href', '#symbol');

        $this->assertTrue($this->matcher->matches($rect, '[xlink:href]'));
        $this->assertTrue($this->matcher->matches($rect, '[xlink:href="#symbol"]'));
    }

    public function testAttributeSelectorWithHyphen(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('data-id', '123');

        $this->assertTrue($this->matcher->matches($rect, '[data-id]'));
        $this->assertTrue($this->matcher->matches($rect, '[data-id="123"]'));
    }

    public function testMultipleAttributeValues(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('class', 'primary active selected');

        $this->assertTrue($this->matcher->matches($rect, '[class~="primary"]'));
        $this->assertTrue($this->matcher->matches($rect, '[class~="active"]'));
        $this->assertTrue($this->matcher->matches($rect, '[class~="selected"]'));
    }

    public function testEmptyAttributeValue(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('title', '');

        $this->assertTrue($this->matcher->matches($rect, '[title]'));
        $this->assertTrue($this->matcher->matches($rect, '[title=""]'));
        $this->assertFalse($this->matcher->matches($rect, '[title="something"]'));
    }

    public function testCaseSensitivity(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('id', 'MyRect');

        $this->assertTrue($this->matcher->matches($rect, '#MyRect'));
        $this->assertFalse($this->matcher->matches($rect, '#myrect'));
        $this->assertFalse($this->matcher->matches($rect, '#MYRECT'));
    }

    public function testComplexAttributeSelectors(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('data-value', 'prefix-middle-suffix');

        $this->assertTrue($this->matcher->matches($rect, '[data-value^="prefix"]'));
        $this->assertTrue($this->matcher->matches($rect, '[data-value$="suffix"]'));
        $this->assertTrue($this->matcher->matches($rect, '[data-value*="middle"]'));
        $this->assertTrue($this->matcher->matches($rect, '[data-value*="-"]'));
    }

    public function testAttributeSelectorEdgeCases(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('value', 'a');

        $this->assertTrue($this->matcher->matches($rect, '[value^="a"]'));
        $this->assertTrue($this->matcher->matches($rect, '[value$="a"]'));
        $this->assertTrue($this->matcher->matches($rect, '[value*="a"]'));
        $this->assertTrue($this->matcher->matches($rect, '[value="a"]'));
    }

    public function testDifferentElements(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('id', 'shape1');

        $circle = new CircleElement($this->doc->getRoot());
        $circle->setAttribute('id', 'shape2');

        $this->assertTrue($this->matcher->matches($rect, 'rect'));
        $this->assertFalse($this->matcher->matches($rect, 'circle'));

        $this->assertTrue($this->matcher->matches($circle, 'circle'));
        $this->assertFalse($this->matcher->matches($circle, 'rect'));

        $this->assertTrue($this->matcher->matches($rect, '#shape1'));
        $this->assertTrue($this->matcher->matches($circle, '#shape2'));
    }

    public function testInvalidSelector(): void
    {
        $rect = new RectElement($this->doc->getRoot());

        // Invalid selectors should not match
        $this->assertFalse($this->matcher->matches($rect, ''));
    }

    public function testAttributeSelectorNumericValues(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('width', '100');

        $this->assertTrue($this->matcher->matches($rect, '[width="100"]'));
        $this->assertFalse($this->matcher->matches($rect, '[width="200"]'));
    }

    public function testWordInListWithSingleWord(): void
    {
        $rect = new RectElement($this->doc->getRoot());
        $rect->setAttribute('class', 'single');

        $this->assertTrue($this->matcher->matches($rect, '[class~="single"]'));
        $this->assertFalse($this->matcher->matches($rect, '[class~="other"]'));
    }
}
