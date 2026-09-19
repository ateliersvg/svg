<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Geometry\Point;
use Atelier\Svg\Loader\DomLoader;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\OptimizerPresets;
use Atelier\Svg\Optimizer\Pass\CleanupIdsPass;
use Atelier\Svg\Optimizer\Pass\ConvertEllipseToCirclePass;
use Atelier\Svg\Optimizer\Pass\ConvertPathDataPass;
use Atelier\Svg\Optimizer\Pass\ConvertShapeToPathPass;
use Atelier\Svg\Optimizer\Pass\InlineStylesPass;
use Atelier\Svg\Optimizer\Pass\MergePathsPass;
use Atelier\Svg\Optimizer\Pass\MergeStylesPass;
use Atelier\Svg\Optimizer\Pass\MoveGroupAttrsToElemsPass;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;
use Atelier\Svg\Optimizer\Pass\RemoveCommentsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDescPass;
use Atelier\Svg\Optimizer\Pass\RemoveDimensionsPass;
use Atelier\Svg\Optimizer\Pass\RemoveDoctypePass;
use Atelier\Svg\Optimizer\Pass\RemoveTitlePass;
use Atelier\Svg\Optimizer\Pass\RemoveXMLProcInstPass;
use Atelier\Svg\Optimizer\Pass\SortAttributesPass;
use Atelier\Svg\Optimizer\Pass\SortDefsChildrenPass;
use Atelier\Svg\Path\PathAnalyzer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OptimizerPresets::class)]
final class OptimizerPresetsTest extends TestCase
{
    public function testDefaultPresetReturnsArray(): void
    {
        $passes = OptimizerPresets::default();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testAggressivePresetReturnsArray(): void
    {
        $passes = OptimizerPresets::aggressive();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testSafePresetReturnsArray(): void
    {
        $passes = OptimizerPresets::safe();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testAggressiveHasMorePassesThanSafe(): void
    {
        $aggressive = OptimizerPresets::aggressive();
        $safe = OptimizerPresets::safe();

        $this->assertGreaterThanOrEqual(count($safe), count($aggressive));
    }

    public function testGetDefaultPreset(): void
    {
        $passes = OptimizerPresets::get('default');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetAggressivePreset(): void
    {
        $passes = OptimizerPresets::get('aggressive');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetSafePreset(): void
    {
        $passes = OptimizerPresets::get('safe');

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);
    }

    public function testGetWithUnknownPresetThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unknown preset 'unknown'");

        OptimizerPresets::get('unknown');
    }

    public function testGetIsCaseInsensitive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Should be case-sensitive
        OptimizerPresets::get('DEFAULT');
    }

    public function testAllPresetsReturnDifferentInstances(): void
    {
        $default1 = OptimizerPresets::default();
        $default2 = OptimizerPresets::default();

        // Should return new instances each time
        $this->assertNotSame($default1, $default2);
        $this->assertNotSame($default1[0], $default2[0]);
    }

    // -- Pass membership assertions --

    public function testDefaultPresetIncludesSvgoBaseline(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::default());

        $this->assertContains(RemoveDoctypePass::class, $classNames);
        $this->assertContains(RemoveXMLProcInstPass::class, $classNames);
        $this->assertContains(InlineStylesPass::class, $classNames);
        $this->assertContains(ConvertEllipseToCirclePass::class, $classNames);
        $this->assertContains(RemoveDescPass::class, $classNames);
        $this->assertContains(SortAttributesPass::class, $classNames);
    }

    public function testDefaultPresetPreservesTitle(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::default());

        $this->assertNotContains(RemoveTitlePass::class, $classNames);
    }

    public function testAggressivePresetIncludesMaxCompression(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::aggressive());

        $this->assertContains(RemoveTitlePass::class, $classNames);
        $this->assertContains(RemoveDescPass::class, $classNames);
        $this->assertContains(RemoveDimensionsPass::class, $classNames);
        $this->assertContains(ConvertShapeToPathPass::class, $classNames);
        $this->assertContains(MergePathsPass::class, $classNames);
    }

    public function testAggressivePresetMinifiesIds(): void
    {
        $passes = OptimizerPresets::aggressive();

        foreach ($passes as $pass) {
            if ($pass instanceof CleanupIdsPass) {
                // Verify minify is enabled via reflection
                $r = new \ReflectionProperty($pass, 'minify');
                $this->assertTrue($r->getValue($pass), 'Aggressive preset should minify IDs');

                return;
            }
        }

        $this->fail('Aggressive preset should include CleanupIdsPass');
    }

    public function testSafePresetDoesNotRemoveMetadata(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::safe());

        $this->assertNotContains(RemoveDescPass::class, $classNames);
        $this->assertNotContains(RemoveTitlePass::class, $classNames);
        $this->assertNotContains(RemoveDimensionsPass::class, $classNames);
        $this->assertNotContains(CleanupIdsPass::class, $classNames);
    }

    public function testSafePresetIncludesSafeOptimizations(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::safe());

        $this->assertContains(RemoveDoctypePass::class, $classNames);
        $this->assertContains(RemoveXMLProcInstPass::class, $classNames);
        $this->assertContains(ConvertEllipseToCirclePass::class, $classNames);
        $this->assertContains(SortAttributesPass::class, $classNames);
        $this->assertContains(ConvertPathDataPass::class, $classNames);
    }

    public function testWebPresetReturnsArray(): void
    {
        $passes = OptimizerPresets::web();

        $this->assertIsArray($passes);
        $this->assertNotEmpty($passes);

        foreach ($passes as $pass) {
            $this->assertInstanceOf(OptimizerPassInterface::class, $pass);
        }
    }

    public function testGetWebPreset(): void
    {
        $passes = OptimizerPresets::get('web');
        $this->assertSame(
            self::getPassClassNames(OptimizerPresets::web()),
            self::getPassClassNames($passes),
        );
    }

    public function testWebPresetIncludesAggressiveFeatures(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::web());

        $this->assertContains(RemoveTitlePass::class, $classNames);
        $this->assertContains(RemoveDescPass::class, $classNames);
        $this->assertContains(RemoveDimensionsPass::class, $classNames);
        $this->assertContains(ConvertShapeToPathPass::class, $classNames);
        $this->assertContains(MergePathsPass::class, $classNames);
        // MoveGroupAttrsToElemsPass is deliberately absent: pushing a transform onto
        // every child costs more than the group it lets CollapseGroupsPass remove.
        $this->assertNotContains(MoveGroupAttrsToElemsPass::class, $classNames);
    }

    public function testWebPresetMinifiesIds(): void
    {
        $passes = OptimizerPresets::web();
        $classNames = self::getPassClassNames($passes);

        $this->assertContains(CleanupIdsPass::class, $classNames);
        $this->assertContains(MergeStylesPass::class, $classNames);
        $this->assertContains(SortDefsChildrenPass::class, $classNames);
    }

    public function testSafeHasFewerPassesThanDefault(): void
    {
        $safe = OptimizerPresets::safe();
        $default = OptimizerPresets::default();

        $this->assertLessThan(count($default), count($safe));
    }

    // -- Pass ordering assertions --

    public function testDefaultPresetInlineStylesBeforeConvertStyleToAttrs(): void
    {
        $classNames = self::getPassClassNames(OptimizerPresets::default());

        $inlinePos = array_search(InlineStylesPass::class, $classNames, true);
        $convertPos = array_search(\Atelier\Svg\Optimizer\Pass\ConvertStyleToAttrsPass::class, $classNames, true);

        $this->assertNotFalse($inlinePos, 'InlineStylesPass should be in default preset');
        $this->assertNotFalse($convertPos, 'ConvertStyleToAttrsPass should be in default preset');
        $this->assertLessThan($convertPos, $inlinePos, 'InlineStylesPass should run before ConvertStyleToAttrsPass');
    }

    public function testAllPresetsStartWithCleanup(): void
    {
        foreach (['default', 'aggressive', 'safe', 'web'] as $presetName) {
            $classNames = self::getPassClassNames(OptimizerPresets::get($presetName));
            $firstPass = $classNames[0];

            $this->assertContains($firstPass, [
                RemoveDoctypePass::class,
                RemoveXMLProcInstPass::class,
                RemoveCommentsPass::class,
            ], "Preset '$presetName' should start with a cleanup pass");
        }
    }

    public function testGetUnknownPresetThrowsForRemovedPresets(): void
    {
        foreach (['accessible', 'icon', 'design'] as $removed) {
            try {
                OptimizerPresets::get($removed);
                $this->fail("Expected InvalidArgumentException for removed preset '$removed'");
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testEveryPresetKeepsAOneUnitShapeInASmallViewbox(): void
    {
        // One module of a barcode: a rectangle one user unit tall on a 33-unit grid.
        $inputs = [
            'rect' => '<rect x="2" y="2" width="7" height="1" fill="#000"/>',
            'path' => '<path d="M2,2 L9,2 L9,3 L2,3 Z" fill="#000"/>',
        ];

        foreach ($inputs as $shape => $markup) {
            foreach (['default', 'aggressive', 'safe', 'web'] as $preset) {
                $document = (new DomLoader())->loadFromString(
                    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33 33">'.$markup.'</svg>'
                );

                (new Optimizer(OptimizerPresets::get($preset)))->optimize($document);

                self::assertShapeStillCoversItsCorners($document, sprintf("preset '%s' on a <%s>", $preset, $shape));
            }
        }
    }

    /**
     * Asserts the optimized document still draws the whole 7x1 rectangle.
     *
     * A point near the right end of the rectangle is inside it and outside every
     * truncated version of it, so it separates the intact shape from the triangle
     * an oversized simplification tolerance leaves behind.
     */
    private static function assertShapeStillCoversItsCorners(Document $document, string $message): void
    {
        $rect = $document->querySelector('rect');

        if (null !== $rect) {
            self::assertSame('7', $rect->getAttribute('width'), $message);
            self::assertSame('1', $rect->getAttribute('height'), $message);

            return;
        }

        $path = $document->querySelector('path');
        self::assertInstanceOf(PathElement::class, $path, $message);

        $data = $path->getData();
        self::assertNotNull($data, $message);
        self::assertTrue((new PathAnalyzer($data))->containsPoint(new Point(8.5, 2.5)), $message);
    }

    /**
     * @param array<OptimizerPassInterface> $passes
     *
     * @return list<class-string>
     */
    private static function getPassClassNames(array $passes): array
    {
        return array_values(array_map(static fn (OptimizerPassInterface $p): string => $p::class, $passes));
    }
}
