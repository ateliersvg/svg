<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Descriptive;

use Atelier\Svg\Element\Descriptive\MetadataElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetadataElement::class)]
final class MetadataElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $metadata = new MetadataElement();

        $this->assertSame('metadata', $metadata->getTagName());
    }

    public function testSetAndGetContent(): void
    {
        $metadata = new MetadataElement();
        $content = '<dc:creator>John Doe</dc:creator>';
        $result = $metadata->setContent($content);

        $this->assertSame($metadata, $result, 'setContent should return self for chaining');
        $this->assertSame($content, $metadata->getContent());
    }

    public function testGetContentReturnsNullWhenNotSet(): void
    {
        $metadata = new MetadataElement();

        $this->assertNull($metadata->getContent());
    }

    public function testSetContentWithEmptyString(): void
    {
        $metadata = new MetadataElement();
        $metadata->setContent('');

        $this->assertSame('', $metadata->getContent());
    }

    public function testSetContentWithRDFMetadata(): void
    {
        $metadata = new MetadataElement();
        $rdf = <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:dc="http://purl.org/dc/elements/1.1/">
  <rdf:Description about="http://example.org/image.svg">
    <dc:title>My SVG Image</dc:title>
    <dc:creator>Jane Smith</dc:creator>
    <dc:date>2024-01-15</dc:date>
  </rdf:Description>
</rdf:RDF>
XML;

        $metadata->setContent($rdf);

        $this->assertSame($rdf, $metadata->getContent());
    }

    public function testSetContentOverwritesPreviousContent(): void
    {
        $metadata = new MetadataElement();
        $metadata->setContent('<dc:creator>First Author</dc:creator>');
        $metadata->setContent('<dc:creator>Second Author</dc:creator>');

        $this->assertSame('<dc:creator>Second Author</dc:creator>', $metadata->getContent());
    }

    public function testMethodChaining(): void
    {
        $metadata = new MetadataElement();
        $result = $metadata
            ->setAttribute('id', 'svg-metadata')
            ->setContent('<dc:title>Chart</dc:title>');

        $this->assertSame($metadata, $result);
        $this->assertSame('svg-metadata', $metadata->getAttribute('id'));
        $this->assertSame('<dc:title>Chart</dc:title>', $metadata->getContent());
    }

    public function testCompleteMetadataConfiguration(): void
    {
        $metadata = new MetadataElement();
        $content = <<<XML
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:cc="http://creativecommons.org/ns#">
  <cc:Work rdf:about="">
    <dc:format>image/svg+xml</dc:format>
    <dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
    <dc:title>Data Visualization</dc:title>
    <dc:creator>
      <cc:Agent>
        <dc:title>Data Team</dc:title>
      </cc:Agent>
    </dc:creator>
    <dc:rights>
      <cc:Agent>
        <dc:title>Company Inc.</dc:title>
      </cc:Agent>
    </dc:rights>
    <dc:date>2024</dc:date>
    <cc:license rdf:resource="http://creativecommons.org/licenses/by/4.0/" />
  </cc:Work>
</rdf:RDF>
XML;

        $metadata
            ->setAttribute('id', 'image-metadata')
            ->setContent($content);

        $this->assertSame('image-metadata', $metadata->getAttribute('id'));
        $this->assertSame($content, $metadata->getContent());
    }

    public function testMetadataWithDublinCore(): void
    {
        $metadata = new MetadataElement();
        $dc = <<<XML
<dc:metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
  <dc:title>Sales Chart Q4 2023</dc:title>
  <dc:description>Quarterly sales data visualization</dc:description>
  <dc:subject>Sales, Analytics, Business Intelligence</dc:subject>
  <dc:creator>Analytics Department</dc:creator>
  <dc:publisher>Company Inc.</dc:publisher>
  <dc:date>2024-01-15</dc:date>
  <dc:format>image/svg+xml</dc:format>
  <dc:language>en</dc:language>
</dc:metadata>
XML;

        $metadata->setContent($dc);

        $this->assertSame($dc, $metadata->getContent());
    }

    public function testMetadataWithCustomNamespace(): void
    {
        $metadata = new MetadataElement();
        $custom = <<<XML
<custom:info xmlns:custom="http://example.com/metadata">
  <custom:version>1.0</custom:version>
  <custom:department>Marketing</custom:department>
  <custom:project>Campaign 2024</custom:project>
</custom:info>
XML;

        $metadata->setContent($custom);

        $this->assertSame($custom, $metadata->getContent());
    }

    public function testMetadataWithSpecialCharacters(): void
    {
        $metadata = new MetadataElement();
        $content = '<dc:title>Chart with &lt;special&gt; characters &amp; symbols</dc:title>';
        $metadata->setContent($content);

        $this->assertSame($content, $metadata->getContent());
    }

    public function testMetadataWithMultilineContent(): void
    {
        $metadata = new MetadataElement();
        $content = "<dc:description>\n  Multi-line\n  description\n</dc:description>";
        $metadata->setContent($content);

        $this->assertSame($content, $metadata->getContent());
    }
}
