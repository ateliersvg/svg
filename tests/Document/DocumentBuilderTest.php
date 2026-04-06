<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Document\DocumentBuilder;
use Atelier\Svg\Element\SvgElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentBuilder::class)]
final class DocumentBuilderTest extends TestCase
{
    private DocumentBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new DocumentBuilder();
    }

    public function testGetSvgReturnsNewSvgElement(): void
    {
        $svg = $this->builder->getSvg();

        $this->assertInstanceOf(SvgElement::class, $svg);
        $this->assertEquals('http://www.w3.org/2000/svg', $svg->getAttribute('xmlns'));
        $this->assertEquals('1.1', $svg->getAttribute('version'));
    }

    public function testGetSvgDocumentReturnsDocumentWithSvgRoot(): void
    {
        $doc = $this->builder->getSvgDocument();

        $this->assertInstanceOf(Document::class, $doc);
        $root = $doc->getRootElement();
        $this->assertInstanceOf(SvgElement::class, $root);
    }

    public function testCreateDocumentWithDefaultDimensions(): void
    {
        $doc = $this->builder->createDocument();

        $this->assertInstanceOf(Document::class, $doc);
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $this->assertEquals('300', $root->getAttribute('width'));
        $this->assertEquals('150', $root->getAttribute('height'));
    }

    public function testCreateDocumentWithCustomDimensions(): void
    {
        $doc = $this->builder->createDocument(800, 600);

        $root = $doc->getRootElement();
        $this->assertEquals('800', $root->getAttribute('width'));
        $this->assertEquals('600', $root->getAttribute('height'));
    }

    public function testCreateDocumentWithFloatDimensions(): void
    {
        $doc = $this->builder->createDocument(800.5, 600.25);

        $root = $doc->getRootElement();
        $this->assertEquals('800.5', $root->getAttribute('width'));
        $this->assertEquals('600.25', $root->getAttribute('height'));
    }

    public function testFromSvgElementCreatesDocument(): void
    {
        $svg = new SvgElement();
        $svg->setWidth(1024);
        $svg->setHeight(768);

        $doc = $this->builder->fromSvgElement($svg);

        $this->assertInstanceOf(Document::class, $doc);
        $root = $doc->getRootElement();
        $this->assertSame($svg, $root);
        $this->assertEquals('1024', $root->getAttribute('width'));
        $this->assertEquals('768', $root->getAttribute('height'));
    }

    public function testFromStringWithValidSvg(): void
    {
        $svgContent = '<svg width="100" height="200" viewBox="0 0 100 200"></svg>';

        $doc = $this->builder->fromString($svgContent);

        $this->assertInstanceOf(Document::class, $doc);
        $root = $doc->getRootElement();
        $this->assertEquals('100', $root->getAttribute('width'));
        $this->assertEquals('200', $root->getAttribute('height'));
        $this->assertEquals('0 0 100 200', $root->getAttribute('viewBox'));
    }

    public function testFromStringThrowsExceptionForEmptyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SVG content cannot be empty');

        $this->builder->fromString('');
    }

    public function testFromStringThrowsExceptionForWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SVG content cannot be empty');

        $this->builder->fromString('   ');
    }

    public function testFromStringThrowsExceptionForInvalidContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SVG content: no <svg> tag found');

        $this->builder->fromString('<div>Not SVG</div>');
    }

    public function testFromFileWithValidFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'svg_test_');
        file_put_contents($tempFile, '<svg width="500" height="400"></svg>');

        try {
            $doc = $this->builder->fromFile($tempFile);

            $this->assertInstanceOf(Document::class, $doc);
            $root = $doc->getRootElement();
            $this->assertEquals('500', $root->getAttribute('width'));
            $this->assertEquals('400', $root->getAttribute('height'));
        } finally {
            unlink($tempFile);
        }
    }

    public function testFromFileThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SVG file does not exist');

        $this->builder->fromFile('/path/to/nonexistent/file.svg');
    }

    public function testFromFileThrowsExceptionForUnreadableFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'svg_test_');
        file_put_contents($tempFile, '<svg></svg>');
        chmod($tempFile, 0000);

        try {
            // Check if file is actually unreadable (may not work on all systems)
            if (!is_readable($tempFile)) {
                $this->expectException(\InvalidArgumentException::class);
                $this->expectExceptionMessage('SVG file is not readable');

                $this->builder->fromFile($tempFile);
            } else {
                // Skip test if permission changes don't work on this system
                $this->markTestSkipped('File permission changes not supported on this system');
            }
        } finally {
            chmod($tempFile, 0644);
            unlink($tempFile);
        }
    }

    public function testFromFileThrowsExceptionForInvalidContent(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'svg_test_');
        file_put_contents($tempFile, '<div>Not SVG</div>');

        try {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid SVG file content');

            $this->builder->fromFile($tempFile);
        } finally {
            unlink($tempFile);
        }
    }

    public function testValidateReturnsTrueForValidSvg(): void
    {
        $svgContent = '<?xml version="1.0"?><svg width="100" height="100"></svg>';

        $result = $this->builder->validate($svgContent);

        $this->assertTrue($result);
    }

    public function testValidateReturnsFalseForEmptyContent(): void
    {
        $result = $this->builder->validate('');

        $this->assertFalse($result);
    }

    public function testValidateReturnsFalseForWhitespaceOnly(): void
    {
        $result = $this->builder->validate('   ');

        $this->assertFalse($result);
    }

    public function testValidateReturnsFalseForNonSvgContent(): void
    {
        $result = $this->builder->validate('<div>Not SVG</div>');

        $this->assertFalse($result);
    }

    public function testValidateReturnsFalseForMalformedXml(): void
    {
        $result = $this->builder->validate('<svg><rect></svg>');

        $this->assertFalse($result);
    }

    public function testValidateReturnsTrueForSvgWithoutXmlDeclaration(): void
    {
        $svgContent = '<svg width="100" height="100"></svg>';

        $result = $this->builder->validate($svgContent);

        $this->assertTrue($result);
    }

    public function testValidateReturnsTrueForComplexSvg(): void
    {
        $svgContent = <<<SVG
<?xml version="1.0"?>
<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg">
    <rect x="10" y="10" width="80" height="80" fill="red"/>
    <circle cx="50" cy="50" r="20" fill="blue"/>
</svg>
SVG;

        $result = $this->builder->validate($svgContent);

        $this->assertTrue($result);
    }

    public function testFromStringParsesWidthWithSingleQuotes(): void
    {
        $svgContent = "<svg width='250' height='150'></svg>";

        $doc = $this->builder->fromString($svgContent);
        $root = $doc->getRootElement();

        $this->assertEquals('250', $root->getAttribute('width'));
        $this->assertEquals('150', $root->getAttribute('height'));
    }

    public function testFromStringParsesViewBox(): void
    {
        $svgContent = '<svg viewBox="0 0 800 600"></svg>';

        $doc = $this->builder->fromString($svgContent);
        $root = $doc->getRootElement();

        $this->assertEquals('0 0 800 600', $root->getAttribute('viewBox'));
    }

    public function testMultipleDocumentCreation(): void
    {
        $doc1 = $this->builder->createDocument(100, 100);
        $doc2 = $this->builder->createDocument(200, 200);

        $this->assertNotSame($doc1, $doc2);
        $this->assertEquals('100', $doc1->getRootElement()->getAttribute('width'));
        $this->assertEquals('200', $doc2->getRootElement()->getAttribute('width'));
    }

    public function testFromSvgElementWithComplexElement(): void
    {
        $svg = new SvgElement();
        $svg->setWidth(1920);
        $svg->setHeight(1080);
        $svg->setViewbox('0 0 1920 1080');

        $doc = $this->builder->fromSvgElement($svg);
        $root = $doc->getRootElement();

        $this->assertEquals('1920', $root->getAttribute('width'));
        $this->assertEquals('1080', $root->getAttribute('height'));
        $this->assertEquals('0 0 1920 1080', $root->getAttribute('viewBox'));
    }

    public function testFromStringWithMinimalSvg(): void
    {
        $doc = $this->builder->fromString('<svg></svg>');

        $this->assertInstanceOf(Document::class, $doc);
    }
}
