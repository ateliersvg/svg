<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Descriptive;

use Atelier\Svg\Element\Descriptive\TitleElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TitleElement::class)]
final class TitleElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $title = new TitleElement();

        $this->assertSame('title', $title->getTagName());
    }

    public function testConstructorWithAttributes(): void
    {
        $title = new TitleElement(['id' => 'myTitle', 'class' => 'heading']);

        $this->assertSame('myTitle', $title->getAttribute('id'));
        $this->assertSame('heading', $title->getAttribute('class'));
    }

    public function testSetAndGetContent(): void
    {
        $title = new TitleElement();
        $result = $title->setContent('My SVG Chart');

        $this->assertSame($title, $result, 'setContent should return self for chaining');
        $this->assertSame('My SVG Chart', $title->getContent());
    }

    public function testGetContentReturnsNullWhenNotSet(): void
    {
        $title = new TitleElement();

        $this->assertNull($title->getContent());
    }

    public function testSetContentWithEmptyString(): void
    {
        $title = new TitleElement();
        $title->setContent('');

        $this->assertSame('', $title->getContent());
    }

    public function testSetContentWithMultilineText(): void
    {
        $title = new TitleElement();
        $content = "This is a multi-line\ntitle for\nthe SVG element";
        $title->setContent($content);

        $this->assertSame($content, $title->getContent());
    }

    public function testSetContentOverwritesPreviousContent(): void
    {
        $title = new TitleElement();
        $title->setContent('First title');
        $title->setContent('Second title');

        $this->assertSame('Second title', $title->getContent());
    }

    public function testMethodChaining(): void
    {
        $title = new TitleElement();
        $result = $title
            ->setAttribute('id', 'title1')
            ->setContent('Chart Title');

        $this->assertSame($title, $result);
        $this->assertSame('title1', $title->getAttribute('id'));
        $this->assertSame('Chart Title', $title->getContent());
    }

    public function testCompleteTitleConfiguration(): void
    {
        $title = new TitleElement();
        $title
            ->setAttribute('id', 'chartTitle')
            ->setAttribute('lang', 'en')
            ->setContent('Quarterly Sales Report');

        $this->assertSame('chartTitle', $title->getAttribute('id'));
        $this->assertSame('en', $title->getAttribute('lang'));
        $this->assertSame('Quarterly Sales Report', $title->getContent());
    }

    public function testTitleWithSpecialCharacters(): void
    {
        $title = new TitleElement();
        $content = 'Title with <special> & "characters"';
        $title->setContent($content);

        $this->assertSame($content, $title->getContent());
    }

    public function testTitleWithUnicodeCharacters(): void
    {
        $title = new TitleElement();
        $content = 'Titre avec des caractères accentués: é, è, ê, à, ç';
        $title->setContent($content);

        $this->assertSame($content, $title->getContent());
    }
}
