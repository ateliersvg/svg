<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveXMLProcInstPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveXMLProcInstPass::class)]
final class RemoveXMLProcInstPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveXMLProcInstPass();

        $this->assertSame('remove-xml-proc-inst', $pass->getName());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveXMLProcInstPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testOptimizeDocument(): void
    {
        $pass = new RemoveXMLProcInstPass();
        $svg = new SvgElement();

        $rect = new RectElement();
        $rect->setX(10)->setY(10)->setWidth(50)->setHeight(50);
        $svg->appendChild($rect);

        $document = new Document($svg);
        $pass->optimize($document);

        // Document should remain unchanged at the element level
        // XML processing instruction removal happens during serialization
        $this->assertNotNull($document->getRootElement());
        $this->assertCount(1, $svg->getChildren());
    }

    public function testPassExists(): void
    {
        // This test ensures the pass can be instantiated and used in optimization pipelines
        // The actual XML PI removal is handled by the dumper/serializer
        $pass = new RemoveXMLProcInstPass();

        $this->assertInstanceOf(RemoveXMLProcInstPass::class, $pass);
    }
}
