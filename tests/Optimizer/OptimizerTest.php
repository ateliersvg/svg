<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer;

use Atelier\Svg\Document;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Optimizer;
use Atelier\Svg\Optimizer\Pass\OptimizerPassInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Optimizer::class)]
final class OptimizerTest extends TestCase
{
    public function testConstructorWithNoPasses(): void
    {
        $optimizer = new Optimizer();

        $this->assertInstanceOf(Optimizer::class, $optimizer);
        $this->assertSame([], $optimizer->getPasses());
    }

    public function testConstructorWithPasses(): void
    {
        $pass1 = $this->createStub(OptimizerPassInterface::class);
        $pass2 = $this->createStub(OptimizerPassInterface::class);

        $optimizer = new Optimizer([$pass1, $pass2]);

        $this->assertCount(2, $optimizer->getPasses());
        $this->assertSame([$pass1, $pass2], $optimizer->getPasses());
    }

    public function testAddPass(): void
    {
        $optimizer = new Optimizer();
        $pass = $this->createStub(OptimizerPassInterface::class);

        $result = $optimizer->addPass($pass);

        $this->assertSame($optimizer, $result);
        $this->assertCount(1, $optimizer->getPasses());
        $this->assertSame([$pass], $optimizer->getPasses());
    }

    public function testAddMultiplePasses(): void
    {
        $optimizer = new Optimizer();
        $pass1 = $this->createStub(OptimizerPassInterface::class);
        $pass2 = $this->createStub(OptimizerPassInterface::class);

        $optimizer->addPass($pass1);
        $optimizer->addPass($pass2);

        $this->assertCount(2, $optimizer->getPasses());
        $this->assertSame([$pass1, $pass2], $optimizer->getPasses());
    }

    public function testOptimizeWithNoPasses(): void
    {
        $optimizer = new Optimizer();
        $document = new Document(new SvgElement());

        $result = $optimizer->optimize($document);

        $this->assertSame($document, $result);
    }

    public function testOptimizeRunsAllPassesInSequence(): void
    {
        $document = new Document(new SvgElement());
        $executionOrder = [];

        $pass1 = $this->createMock(OptimizerPassInterface::class);
        $pass1->expects($this->once())
            ->method('optimize')
            ->with($document)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'pass1';
            });

        $pass2 = $this->createMock(OptimizerPassInterface::class);
        $pass2->expects($this->once())
            ->method('optimize')
            ->with($document)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'pass2';
            });

        $pass3 = $this->createMock(OptimizerPassInterface::class);
        $pass3->expects($this->once())
            ->method('optimize')
            ->with($document)
            ->willReturnCallback(function () use (&$executionOrder) {
                $executionOrder[] = 'pass3';
            });

        $optimizer = new Optimizer([$pass1, $pass2, $pass3]);
        $result = $optimizer->optimize($document);

        $this->assertSame($document, $result);
        $this->assertSame(['pass1', 'pass2', 'pass3'], $executionOrder);
    }

    public function testOptimizeReturnsDocument(): void
    {
        $document = new Document(new SvgElement());
        $pass = $this->createStub(OptimizerPassInterface::class);

        $optimizer = new Optimizer([$pass]);
        $result = $optimizer->optimize($document);

        $this->assertSame($document, $result);
    }

    public function testGetPasses(): void
    {
        $pass1 = $this->createStub(OptimizerPassInterface::class);
        $pass2 = $this->createStub(OptimizerPassInterface::class);

        $optimizer = new Optimizer([$pass1, $pass2]);

        $passes = $optimizer->getPasses();

        $this->assertIsArray($passes);
        $this->assertCount(2, $passes);
        $this->assertSame($pass1, $passes[0]);
        $this->assertSame($pass2, $passes[1]);
    }

    public function testPassesCanModifyDocument(): void
    {
        $svg = new SvgElement();
        $svg->setAttribute('test', 'original');
        $document = new Document($svg);

        $pass = $this->createMock(OptimizerPassInterface::class);
        $pass->expects($this->once())
            ->method('optimize')
            ->with($document)
            ->willReturnCallback(function (Document $doc) {
                $root = $doc->getRootElement();
                if (null !== $root) {
                    $root->setAttribute('test', 'modified');
                }
            });

        $optimizer = new Optimizer([$pass]);
        $optimizer->optimize($document);

        $this->assertSame('modified', $svg->getAttribute('test'));
    }
}
