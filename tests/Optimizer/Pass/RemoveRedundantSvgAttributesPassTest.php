<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveRedundantSvgAttributesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveRedundantSvgAttributesPass::class)]
final class RemoveRedundantSvgAttributesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveRedundantSvgAttributesPass();

        $this->assertSame('remove-redundant-svg-attributes', $pass->getName());
    }

    public function testRemovesVersionAttributeFromSvgElements(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('version', '1.1');
        $nested = new SvgElement();
        $nested->setAttribute('version', '1.2');
        $svg->appendChild($nested);

        $document = new Document($svg);
        $pass = new RemoveRedundantSvgAttributesPass();
        $pass->optimize($document);

        $this->assertFalse($svg->hasAttribute('version'));
        $this->assertFalse($nested->hasAttribute('version'));
    }

    public function testRemovesXmlSpacePreserveAttributes(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('xml:space', 'preserve');
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveRedundantSvgAttributesPass();
        $pass->optimize($document);

        $this->assertFalse($group->hasAttribute('xml:space'));
    }

    public function testKeepsXmlSpaceWhenNotPreserve(): void
    {
        $svg = new SvgElement();
        $group = new GroupElement();
        $group->setAttribute('xml:space', 'default');
        $svg->appendChild($group);

        $document = new Document($svg);
        $pass = new RemoveRedundantSvgAttributesPass();
        $pass->optimize($document);

        $this->assertTrue($group->hasAttribute('xml:space'));
    }
}
