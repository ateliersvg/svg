<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Document\DocumentCropper;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentCropper::class)]
final class DocumentCropperTest extends TestCase
{
    private function createDocumentWithRect(): Document
    {
        $doc = Document::create(200, 200);
        $rect = new RectElement();
        $rect->setAttribute('width', '200');
        $rect->setAttribute('height', '200');
        $doc->getRootElement()->appendChild($rect);

        return $doc;
    }

    public function testToRect(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $result = $cropper->toRect(10, 20, 100, 80);

        $this->assertSame($cropper, $result, 'toRect should return self for chaining');
        $this->assertNotNull($svg->getAttribute('clip-path'));
        $this->assertMatchesRegularExpression('/url\(#clip-[a-f0-9.]+\)/', $svg->getAttribute('clip-path'));
    }

    public function testToCircle(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $result = $cropper->toCircle(100, 100, 50);

        $this->assertSame($cropper, $result, 'toCircle should return self for chaining');
        $this->assertNotNull($svg->getAttribute('clip-path'));
        $this->assertMatchesRegularExpression('/url\(#clip-[a-f0-9.]+\)/', $svg->getAttribute('clip-path'));
    }

    public function testToEllipse(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $result = $cropper->toEllipse(100, 100, 80, 40);

        $this->assertSame($cropper, $result, 'toEllipse should return self for chaining');
        $this->assertNotNull($svg->getAttribute('clip-path'));
        $this->assertMatchesRegularExpression('/url\(#clip-[a-f0-9.]+\)/', $svg->getAttribute('clip-path'));
    }

    public function testToPathWithString(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $result = $cropper->toPath('M 0 0 L 100 0 L 100 100 Z');

        $this->assertSame($cropper, $result, 'toPath should return self for chaining');
        $this->assertNotNull($svg->getAttribute('clip-path'));
    }

    public function testToPathWithDataObject(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $data = new Data([
            new MoveTo('M', new \Atelier\Svg\Geometry\Point(0, 0)),
            new LineTo('L', new \Atelier\Svg\Geometry\Point(100, 0)),
            new LineTo('L', new \Atelier\Svg\Geometry\Point(100, 100)),
        ]);

        $cropper = new DocumentCropper($svg);
        $result = $cropper->toPath($data);

        $this->assertSame($cropper, $result);
        $this->assertNotNull($svg->getAttribute('clip-path'));
    }

    public function testClear(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $cropper->toRect(0, 0, 100, 100);
        $this->assertNotNull($svg->getAttribute('clip-path'));

        $result = $cropper->clear();

        $this->assertSame($cropper, $result, 'clear should return self for chaining');
        $this->assertNull($svg->getAttribute('clip-path'));
    }

    public function testGetClipPathIdReturnsNullWhenNotSet(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);

        $this->assertNull($cropper->getClipPathId());
    }

    public function testGetClipPathIdReturnsIdAfterCropping(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $cropper->toRect(0, 0, 100, 100);

        $clipPathId = $cropper->getClipPathId();
        $this->assertNotNull($clipPathId);
        $this->assertStringStartsWith('clip-', $clipPathId);
    }

    public function testClearAndGetClipPathIdReturnsNull(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $cropper->toRect(0, 0, 100, 100);
        $cropper->clear();

        $this->assertNull($cropper->getClipPathId());
    }

    public function testCroppingOnChildElement(): void
    {
        $doc = $this->createDocumentWithRect();
        $rect = new RectElement();
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '50');
        $doc->getRootElement()->appendChild($rect);

        $cropper = new DocumentCropper($rect);
        $cropper->toCircle(25, 25, 20);

        $this->assertNotNull($rect->getAttribute('clip-path'));
        $this->assertNotNull($cropper->getClipPathId());
    }

    public function testMultipleCropsOverwriteClipPath(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        $cropper = new DocumentCropper($svg);
        $cropper->toRect(0, 0, 50, 50);
        $firstId = $cropper->getClipPathId();

        $cropper->toCircle(50, 50, 30);
        $secondId = $cropper->getClipPathId();

        $this->assertNotSame($firstId, $secondId);
    }

    public function testGetClipPathIdReturnsNullForNonUrlClipPath(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        // Set a clip-path attribute that does not match url(#id) format
        $svg->setAttribute('clip-path', 'inherit');

        $cropper = new DocumentCropper($svg);

        $this->assertNull($cropper->getClipPathId());
    }

    public function testCroppingChildElementReusesExistingDefs(): void
    {
        $doc = $this->createDocumentWithRect();
        $svg = $doc->getRootElement();

        // Pre-create a defs element in the root
        $defs = new \Atelier\Svg\Element\Structural\DefsElement();
        $svg->appendChild($defs);

        // Create a child element and crop it
        $rect = new RectElement();
        $rect->setAttribute('width', '50');
        $rect->setAttribute('height', '50');
        $svg->appendChild($rect);

        $cropper = new DocumentCropper($rect);
        $cropper->toRect(0, 0, 40, 40);

        // The existing defs should be reused (should have a clipPath child now)
        $this->assertCount(1, $defs->getChildren());
        $this->assertNotNull($rect->getAttribute('clip-path'));
    }
}
