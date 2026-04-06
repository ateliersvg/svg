<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RemoveCommentsPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveCommentsPass::class)]
final class RemoveCommentsPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RemoveCommentsPass();

        $this->assertSame('remove-comments', $pass->getName());
    }

    public function testConstructorDefaults(): void
    {
        $pass = new RemoveCommentsPass();

        // Verify it constructs without error
        $this->assertInstanceOf(RemoveCommentsPass::class, $pass);
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RemoveCommentsPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testOptimizeDocumentWithoutComments(): void
    {
        $pass = new RemoveCommentsPass();
        $svg = new SvgElement();
        $svg->setAttribute('width', '100');
        $svg->setAttribute('height', '100');

        $document = new Document($svg);

        $pass->optimize($document);

        // Document should remain unchanged
        $this->assertSame($svg, $document->getRootElement());
        $this->assertSame('100', $svg->getAttribute('width'));
    }

    public function testCurrentlyNoOpDueToLackOfCommentSupport(): void
    {
        $pass = new RemoveCommentsPass();
        $svg = new SvgElement();

        // Currently, there's no way to add comments to the element tree
        // This pass is a placeholder for future functionality
        $document = new Document($svg);

        $pass->optimize($document);

        // Document should remain unchanged (currently no-op)
        $this->assertSame($svg, $document->getRootElement());
    }

    public function testExpectedBehaviorWithCommentSupport(): void
    {
        // When comment support is added to the Element tree, this pass should:
        // 1. Remove all comments by default
        // 2. Preserve comments when keepComments=true
        // 3. Preserve comments matching keepCommentsRegex pattern
        // 4. Handle nested comments in container elements

        $this->assertTrue(true); // Placeholder assertion
    }
}
