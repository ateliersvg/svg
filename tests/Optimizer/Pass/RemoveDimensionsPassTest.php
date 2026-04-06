<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveDimensionsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveDimensionsPass::class)]
final class RemoveDimensionsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveDimensionsPass();
        $this->assertSame('remove-dimensions', $pass->getName());
    }

    public function testRemovesDimensionsWhenViewBoxPresent(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');
        $svg->setAttribute('viewBox', '0 0 100 100');

        $document = new Document($svg);

        $pass = new RemoveDimensionsPass();
        $pass->optimize($document);

        $this->assertFalse($svg->hasAttribute('width'));
        $this->assertFalse($svg->hasAttribute('height'));
        $this->assertTrue($svg->hasAttribute('viewBox'), 'Should preserve viewBox');
    }

    public function testPreservesDimensionsWithoutViewBox(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');

        $document = new Document($svg);

        $pass = new RemoveDimensionsPass();
        $pass->optimize($document);

        $this->assertTrue($svg->hasAttribute('width'), 'Should preserve width without viewBox');
        $this->assertTrue($svg->hasAttribute('height'), 'Should preserve height without viewBox');
    }

    public function testHandlesSvgWithOnlyViewBox(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0 0 100 100');

        $document = new Document($svg);

        $pass = new RemoveDimensionsPass();
        $pass->optimize($document);

        $this->assertFalse($svg->hasAttribute('width'));
        $this->assertFalse($svg->hasAttribute('height'));
        $this->assertTrue($svg->hasAttribute('viewBox'));
    }

    public function testPreservesOtherAttributes(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');
        $svg->setAttribute('viewBox', '0 0 100 100');
        $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $svg->setAttribute('id', 'my-svg');

        $document = new Document($svg);

        $pass = new RemoveDimensionsPass();
        $pass->optimize($document);

        $this->assertTrue($svg->hasAttribute('xmlns'));
        $this->assertTrue($svg->hasAttribute('id'));
        $this->assertTrue($svg->hasAttribute('viewBox'));
    }

    public function testHandlesEmptyDocument(): void
    {
        $document = new Document();
        $pass = new RemoveDimensionsPass();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }
}
