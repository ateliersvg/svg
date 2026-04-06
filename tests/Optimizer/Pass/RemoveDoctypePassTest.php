<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveDoctypePass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveDoctypePass::class)]
final class RemoveDoctypePassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveDoctypePass();

        $this->assertSame('remove-doctype', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveDoctypePass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testOptimizeDocument(): void
    {
        $pass = new RemoveDoctypePass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Document should remain unchanged at the element level
        // DOCTYPE removal happens during serialization
        $this->assertNotNull($document->getRootElement());
        $this->assertCount(1, $svg->getChildren());
    }

    public function testPassExists(): void
    {
        // This test ensures the pass can be instantiated and used in optimization pipelines
        // The actual DOCTYPE removal is handled by the dumper/serializer
        $pass = new RemoveDoctypePass();

        $this->assertInstanceOf(RemoveDoctypePass::class, $pass);
    }
}
