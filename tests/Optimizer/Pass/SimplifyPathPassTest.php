<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Exception\InvalidArgumentException;
use Atelier\Svg\Optimizer\Pass\SimplifyPathPass;
use Atelier\Svg\Path\Simplifier\Simplifier;
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

    public function testConstructorDefaultsToAnAbsoluteTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier);

        $this->assertNull($pass->getRelativeTolerance());
    }

    public function testConstructorWithRelativeTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);
        $pass = new SimplifyPathPass($simplifier, 1.0, 0.002);

        $this->assertSame(0.002, $pass->getRelativeTolerance());
    }

    public function testConstructorRejectsZeroRelativeTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relative tolerance must be greater than zero, got 0.');

        new SimplifyPathPass($simplifier, 1.0, 0.0);
    }

    public function testConstructorRejectsNegativeRelativeTolerance(): void
    {
        $simplifier = $this->createStub(SimplifierInterface::class);

        $this->expectException(InvalidArgumentException::class);

        new SimplifyPathPass($simplifier, 1.0, -0.5);
    }

    public function testResolveToleranceIsAbsoluteWithoutRelativeTolerance(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0);

        $this->assertSame(1.0, $pass->resolveTolerance(self::documentWith(['viewBox' => '0 0 33 33'])));
    }

    public function testResolveToleranceFollowsTheViewbox(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $this->assertEqualsWithDelta(
            0.002 * hypot(33.0, 33.0),
            $pass->resolveTolerance(self::documentWith(['viewBox' => '0 0 33 33'])),
            1e-12
        );
    }

    public function testResolveToleranceKeepsTheAbsoluteValueOnLargeDocuments(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        // 0.002 * 1414 is well above 1.0, so the absolute tolerance still applies.
        $this->assertSame(1.0, $pass->resolveTolerance(self::documentWith(['viewBox' => '0 0 1000 1000'])));
    }

    public function testResolveToleranceUsesDimensionsWithoutAViewbox(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $this->assertEqualsWithDelta(
            0.002 * hypot(33.0, 33.0),
            $pass->resolveTolerance(self::documentWith(['width' => '33', 'height' => '33'])),
            1e-12
        );
    }

    public function testResolveToleranceUsesDimensionsWhenTheViewboxIsMalformed(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $document = self::documentWith(['viewBox' => 'not a viewBox', 'width' => '33', 'height' => '33']);

        $this->assertEqualsWithDelta(0.002 * hypot(33.0, 33.0), $pass->resolveTolerance($document), 1e-12);
    }

    public function testResolveToleranceUsesDimensionsWhenTheViewboxHasNoArea(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $document = self::documentWith(['viewBox' => '0 0 0 0', 'width' => '33', 'height' => '33']);

        $this->assertEqualsWithDelta(0.002 * hypot(33.0, 33.0), $pass->resolveTolerance($document), 1e-12);
    }

    public function testResolveToleranceIsAbsoluteWhenNothingDescribesTheScale(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $this->assertSame(1.0, $pass->resolveTolerance(self::documentWith([])));
    }

    public function testResolveToleranceIsAbsoluteWithPercentageDimensions(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $document = self::documentWith(['width' => '100%', 'height' => '100%']);

        $this->assertSame(1.0, $pass->resolveTolerance($document));
    }

    public function testResolveToleranceIsAbsoluteWithMalformedDimensions(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $document = self::documentWith(['width' => 'wide', 'height' => 'tall']);

        $this->assertSame(1.0, $pass->resolveTolerance($document));
    }

    public function testResolveToleranceIsAbsoluteWithOnlyOneDimension(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $this->assertSame(1.0, $pass->resolveTolerance(self::documentWith(['width' => '33'])));
    }

    public function testResolveToleranceIsAbsoluteWithZeroDimensions(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $document = self::documentWith(['width' => '0', 'height' => '0']);

        $this->assertSame(1.0, $pass->resolveTolerance($document));
    }

    public function testResolveToleranceIsAbsoluteOnAnEmptyDocument(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        $this->assertSame(1.0, $pass->resolveTolerance(new Document()));
    }

    public function testResolveToleranceReadsDimensionsInAnyUnit(): void
    {
        $pass = new SimplifyPathPass($this->createStub(SimplifierInterface::class), 1.0, 0.002);

        // Without a viewBox, one user unit is one mm: the drawing spans 33 units.
        $document = self::documentWith(['width' => '33mm', 'height' => '33mm']);

        $this->assertEqualsWithDelta(0.002 * hypot(33.0, 33.0), $pass->resolveTolerance($document), 1e-12);
    }

    public function testOptimizePassesTheResolvedToleranceToTheSimplifier(): void
    {
        $simplifier = $this->createMock(SimplifierInterface::class);
        $simplifier->expects($this->once())
            ->method('simplify')
            ->with(
                $this->anything(),
                $this->equalToWithDelta(0.002 * hypot(33.0, 33.0), 1e-12)
            )
            ->willReturn(new \Atelier\Svg\Path\Data([]));

        $pass = new SimplifyPathPass($simplifier, 1.0, 0.002);

        $pass->optimize(self::documentWith(['viewBox' => '0 0 33 33'], 'M2,2 L9,2 L9,3 L2,3 Z'));
    }

    public function testRelativeToleranceKeepsAOneUnitRectangleIntact(): void
    {
        $document = self::documentWith(['viewBox' => '0 0 33 33'], 'M2,2 L9,2 L9,3 L2,3 Z');

        $pass = new SimplifyPathPass(new Simplifier(), 1.0, SimplifyPathPass::RELATIVE_TOLERANCE_WEB);
        $pass->optimize($document);

        $this->assertSame('M2,2L9,2L9,3L2,3Z', self::pathDataOf($document));
    }

    public function testAbsoluteToleranceStillFlattensAOneUnitRectangle(): void
    {
        // A tolerance of 1.0 user unit is a whole module on a 33-unit grid: the corner
        // at (9,3) sits 0.99 from the closing line, so it goes and a triangle is left.
        // Callers who pass an absolute tolerance keep this behaviour.
        $document = self::documentWith(['viewBox' => '0 0 33 33'], 'M2,2 L9,2 L9,3 L2,3 Z');

        $pass = new SimplifyPathPass(new Simplifier(), 1.0);
        $pass->optimize($document);

        $this->assertSame('M2,2L9,2L2,3Z', self::pathDataOf($document));
    }

    /**
     * @param array<string, string> $attributes Attributes to set on the root element
     */
    private static function documentWith(array $attributes, ?string $pathData = null): Document
    {
        $svg = new SvgElement();

        foreach ($attributes as $name => $value) {
            $svg->setAttribute($name, $value);
        }

        if (null !== $pathData) {
            $path = new PathElement();
            $path->setPathData($pathData);
            $svg->appendChild($path);
        }

        return new Document($svg);
    }

    private static function pathDataOf(Document $document): ?string
    {
        $path = $document->querySelector('path');
        self::assertInstanceOf(PathElement::class, $path);

        return $path->getPathData();
    }
}
