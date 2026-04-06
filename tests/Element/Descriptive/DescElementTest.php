<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Descriptive;

use Atelier\Svg\Element\Descriptive\DescElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DescElement::class)]
final class DescElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $desc = new DescElement();

        $this->assertSame('desc', $desc->getTagName());
    }

    public function testConstructorWithAttributes(): void
    {
        $desc = new DescElement(['id' => 'myDesc', 'class' => 'description']);

        $this->assertSame('myDesc', $desc->getAttribute('id'));
        $this->assertSame('description', $desc->getAttribute('class'));
    }

    public function testSetAndGetContent(): void
    {
        $desc = new DescElement();
        $result = $desc->setContent('This is a description of the SVG element');

        $this->assertSame($desc, $result, 'setContent should return self for chaining');
        $this->assertSame('This is a description of the SVG element', $desc->getContent());
    }

    public function testGetContentReturnsNullWhenNotSet(): void
    {
        $desc = new DescElement();

        $this->assertNull($desc->getContent());
    }

    public function testSetContentWithEmptyString(): void
    {
        $desc = new DescElement();
        $desc->setContent('');

        $this->assertSame('', $desc->getContent());
    }

    public function testSetContentWithMultilineText(): void
    {
        $desc = new DescElement();
        $content = "This is a multi-line\ndescription for\nthe SVG element";
        $desc->setContent($content);

        $this->assertSame($content, $desc->getContent());
    }

    public function testSetContentOverwritesPreviousContent(): void
    {
        $desc = new DescElement();
        $desc->setContent('First description');
        $desc->setContent('Second description');

        $this->assertSame('Second description', $desc->getContent());
    }

    public function testMethodChaining(): void
    {
        $desc = new DescElement();
        $result = $desc
            ->setAttribute('id', 'desc1')
            ->setContent('Description text');

        $this->assertSame($desc, $result);
        $this->assertSame('desc1', $desc->getAttribute('id'));
        $this->assertSame('Description text', $desc->getContent());
    }

    public function testCompleteDescConfiguration(): void
    {
        $desc = new DescElement();
        $desc
            ->setAttribute('id', 'chartDesc')
            ->setAttribute('lang', 'en')
            ->setContent('A bar chart showing quarterly sales data');

        $this->assertSame('chartDesc', $desc->getAttribute('id'));
        $this->assertSame('en', $desc->getAttribute('lang'));
        $this->assertSame('A bar chart showing quarterly sales data', $desc->getContent());
    }

    public function testDescWithSpecialCharacters(): void
    {
        $desc = new DescElement();
        $content = 'Description with <special> & "characters"';
        $desc->setContent($content);

        $this->assertSame($content, $desc->getContent());
    }

    public function testDescWithUnicodeCharacters(): void
    {
        $desc = new DescElement();
        $content = 'Description avec des caractères accentués: é, è, ê, à, ç';
        $desc->setContent($content);

        $this->assertSame($content, $desc->getContent());
    }
}
