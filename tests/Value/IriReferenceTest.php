<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Value;

use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Value\IriReference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IriReference::class)]
final class IriReferenceTest extends TestCase
{
    public function testParseFragmentReference(): void
    {
        $iri = IriReference::parse('#myId');
        $this->assertSame('#myId', $iri->getIri());
        $this->assertSame('myId', $iri->getFragment());
        $this->assertTrue($iri->hasFragment());
        $this->assertTrue($iri->isFragmentOnly());
        $this->assertFalse($iri->hasUrlFunction());
    }

    public function testParseUrlFunctionWithFragment(): void
    {
        $iri = IriReference::parse('url(#gradient1)');
        $this->assertSame('#gradient1', $iri->getIri());
        $this->assertSame('gradient1', $iri->getFragment());
        $this->assertTrue($iri->hasFragment());
        $this->assertTrue($iri->isFragmentOnly());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testParseUrlFunctionWithPathAndFragment(): void
    {
        $iri = IriReference::parse('url(path/to/file.svg#elementId)');
        $this->assertSame('path/to/file.svg#elementId', $iri->getIri());
        $this->assertSame('elementId', $iri->getFragment());
        $this->assertTrue($iri->hasFragment());
        $this->assertFalse($iri->isFragmentOnly());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testParseUrlFunctionWithAbsoluteUri(): void
    {
        $iri = IriReference::parse('url(http://example.com/file.svg#id)');
        $this->assertSame('http://example.com/file.svg#id', $iri->getIri());
        $this->assertSame('id', $iri->getFragment());
        $this->assertTrue($iri->hasFragment());
        $this->assertFalse($iri->isFragmentOnly());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testParseUrlFunctionWithSpaces(): void
    {
        $iri = IriReference::parse('url(  #spaced  )');
        $this->assertSame('#spaced', $iri->getIri());
        $this->assertSame('spaced', $iri->getFragment());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testParseThrowsOnNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IriReference::parse(null);
    }

    public function testParseThrowsOnEmptyString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IriReference::parse('');
    }

    public function testParseThrowsOnWhitespaceOnly(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IriReference::parse('   ');
    }

    public function testFromElementId(): void
    {
        $iri = IriReference::fromElementId('myFilter');
        $this->assertSame('#myFilter', $iri->getIri());
        $this->assertSame('myFilter', $iri->getFragment());
        $this->assertTrue($iri->hasFragment());
        $this->assertTrue($iri->isFragmentOnly());
        $this->assertFalse($iri->hasUrlFunction());
    }

    public function testFromElementIdWithUrlFunction(): void
    {
        $iri = IriReference::fromElementId('myFilter', true);
        $this->assertSame('#myFilter', $iri->getIri());
        $this->assertSame('myFilter', $iri->getFragment());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testFromElementIdThrowsOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IriReference::fromElementId('');
    }

    public function testFromUri(): void
    {
        $iri = IriReference::fromUri('http://example.com/file.svg#id');
        $this->assertSame('http://example.com/file.svg#id', $iri->getIri());
        $this->assertSame('id', $iri->getFragment());
        $this->assertTrue($iri->hasUrlFunction());
    }

    public function testFromUriWithoutFragment(): void
    {
        $iri = IriReference::fromUri('http://example.com/file.svg');
        $this->assertSame('http://example.com/file.svg', $iri->getIri());
        $this->assertNull($iri->getFragment());
        $this->assertFalse($iri->hasFragment());
    }

    public function testFromUriWithoutUrlFunction(): void
    {
        $iri = IriReference::fromUri('path/file.svg#id', false);
        $this->assertFalse($iri->hasUrlFunction());
    }

    public function testFromUriThrowsOnEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IriReference::fromUri('');
    }

    public function testToStringWithUrlFunction(): void
    {
        $iri = IriReference::parse('url(#id)');
        $this->assertSame('url(#id)', $iri->toString());
    }

    public function testToStringWithoutUrlFunction(): void
    {
        $iri = IriReference::parse('#id');
        $this->assertSame('#id', $iri->toString());
    }

    public function testToUrlFunction(): void
    {
        $iri = IriReference::parse('#myId');
        $this->assertSame('url(#myId)', $iri->toUrlFunction());
    }

    public function testToUrlFunctionAlreadyWrapped(): void
    {
        $iri = IriReference::parse('url(#myId)');
        $this->assertSame('url(#myId)', $iri->toUrlFunction());
    }

    public function testMagicToString(): void
    {
        $iri = IriReference::parse('url(#test)');
        $this->assertSame($iri->toString(), (string) $iri);
    }

    public function testMagicToStringFragment(): void
    {
        $iri = IriReference::parse('#test');
        $this->assertSame('#test', (string) $iri);
    }
}
