<?php

namespace Atelier\Svg\Tests\Document;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Document::class)]
final class DocumentAccessibilityTest extends TestCase
{
    public function testSetTitleConvenienceMethod(): void
    {
        $doc = Document::create();
        $result = $doc->setTitle('Test Title');

        $this->assertSame($doc, $result);

        $children = $doc->getRootElement()->getChildren();
        $titleElement = null;
        foreach ($children as $child) {
            if ($child instanceof TitleElement) {
                $titleElement = $child;
                break;
            }
        }

        $this->assertNotNull($titleElement);
        $this->assertEquals('Test Title', $titleElement->getContent());
    }

    public function testSetDescriptionConvenienceMethod(): void
    {
        $doc = Document::create();
        $result = $doc->setDescription('Test Description');

        $this->assertSame($doc, $result);

        $children = $doc->getRootElement()->getChildren();
        $descElement = null;
        foreach ($children as $child) {
            if ($child instanceof DescElement) {
                $descElement = $child;
                break;
            }
        }

        $this->assertNotNull($descElement);
        $this->assertEquals('Test Description', $descElement->getContent());
    }

    public function testCheckAccessibilityConvenienceMethod(): void
    {
        $doc = Document::create();

        $issues = $doc->checkAccessibility();

        $this->assertIsArray($issues);
    }

    public function testImproveAccessibilityConvenienceMethod(): void
    {
        $doc = Document::create();
        $result = $doc->improveAccessibility();

        $this->assertSame($doc, $result);

        $children = $doc->getRootElement()->getChildren();
        $hasTitleElement = false;
        foreach ($children as $child) {
            if ($child instanceof TitleElement) {
                $hasTitleElement = true;
                break;
            }
        }
        $this->assertTrue($hasTitleElement);
    }

    public function testMakeResponsiveConvenienceMethod(): void
    {
        $doc = Document::create(800, 600);
        $result = $doc->makeResponsive();

        $this->assertSame($doc, $result);

        $root = $doc->getRootElement();
        $this->assertNull($root->getAttribute('width'));
        $this->assertNull($root->getAttribute('height'));
        $this->assertEquals('0 0 800 600', $root->getAttribute('viewBox'));
    }
}
