<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\SimplifyPathPass;
use Atelier\Svg\Path\Simplifier\SimplifierInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimplifyPathPass::class)]
final class SimplifyPathPassTest extends TestCase
{
    public function testGetName(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier);

        $this->assertSame('simplify-path', $pass->getName());
    }

    public function testConstructorWithDefaults(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier);

        $this->assertSame(1.0, $pass->getTolerance());
    }

    public function testConstructorWithCustomTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier, 2.5);

        $this->assertSame(2.5, $pass->getTolerance());
    }

    public function testSetTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier);

        $result = $pass->setTolerance(3.0);

        $this->assertSame($pass, $result);
        $this->assertSame(3.0, $pass->getTolerance());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier);
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testOptimizeWithoutPathElements(): void
    {
        $simplifier = $this->createMock(SimplifierInterface::class);
        $simplifier->expects($this->never())
            ->method('simplify');

        $pass = new SimplifyPathPass($simplifier);
        $svg = new SvgElement();
        $group = new GroupElement();
        $svg->appendChild($group);

        $document = new Document($svg);

        $pass->optimize($document);

        // Should complete without calling simplifier
        $this->assertTrue(true);
    }

    public function testOptimizeSkipsPathsWithoutData(): void
    {
        $simplifier = $this->createMock(SimplifierInterface::class);
        $simplifier->expects($this->never())
            ->method('simplify');

        $pass = new SimplifyPathPass($simplifier);
        $svg = new SvgElement();
        $path = new PathElement();
        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // Path::getData() returns null (parsing not implemented),
        // so simplifier should not be called
        $this->assertTrue(true);
    }

    public function testOptimizeProcessesNestedPaths(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);

        $pass = new SimplifyPathPass($simplifier);
        $svg = new SvgElement();
        $group1 = new GroupElement();
        $group2 = new GroupElement();
        $path1 = new PathElement();
        $path2 = new PathElement();
        $path3 = new PathElement();

        $svg->appendChild($group1);
        $svg->appendChild($path1);
        $group1->appendChild($group2);
        $group2->appendChild($path2);
        $group2->appendChild($path3);

        $document = new Document($svg);

        $pass->optimize($document);

        // Even though paths won't be simplified (getData returns null),
        // the traversal should work correctly
        $this->assertTrue(true);
    }

    public function testGetTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier, 5.0);

        $this->assertSame(5.0, $pass->getTolerance());
    }

    public function testOptimizePathWithDAttributeCallsSimplifier(): void
    {
        $simplifiedData = new \Atelier\Svg\Path\Data([]);

        $simplifier = $this->createMock(SimplifierInterface::class);
        $simplifier->expects($this->once())
            ->method('simplify')
            ->willReturn($simplifiedData);

        $pass = new SimplifyPathPass($simplifier);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('M0 0 L10 10 L20 0');
        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // PathElement::getData() now returns parsed Data, so simplifier is called
        $this->assertNotNull($path->getPathData());
    }

    public function testOptimizePathSetsSimplifiedDataOnElement(): void
    {
        $inputData = (new \Atelier\Svg\Path\PathParser())->parse('M0 0 L10 10 L20 0 Z');
        $simplifiedData = (new \Atelier\Svg\Path\PathParser())->parse('M0 0 L20 0 Z');

        $simplifier = $this->createMock(SimplifierInterface::class);
        $simplifier->expects($this->once())
            ->method('simplify')
            ->willReturn($simplifiedData);

        $pass = new SimplifyPathPass($simplifier, 2.0);
        $svg = new SvgElement();
        $path = new PathElement();
        $path->setPathData('M0 0 L10 10 L20 0 Z');
        $svg->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        // The path data should reflect the simplified result
        $data = $path->getData();
        $this->assertNotNull($data);
        $this->assertCount(3, $data->getSegments());
    }
}
