<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Optimizer\Pass;

use Atelier\Svg\Document;
use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\SvgElement;
use Atelier\Svg\Optimizer\Pass\RoundValuesPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoundValuesPass::class)]
final class RoundValuesPassTest extends TestCase
{
    public function testGetName(): void
    {
        $pass = new RoundValuesPass();

        $this->assertSame('round-values', $pass->getName());
    }

    public function testConstructorWithDefaultPrecision(): void
    {
        $pass = new RoundValuesPass();

        $this->assertSame(2, $pass->getPrecision());
    }

    public function testConstructorWithCustomPrecision(): void
    {
        $pass = new RoundValuesPass(3);

        $this->assertSame(3, $pass->getPrecision());
    }

    public function testSetPrecision(): void
    {
        $pass = new RoundValuesPass();

        $result = $pass->setPrecision(4);

        $this->assertSame($pass, $result);
        $this->assertSame(4, $pass->getPrecision());
    }

    public function testSetPrecisionWithNegativeValueSetsZero(): void
    {
        $pass = new RoundValuesPass();

        $pass->setPrecision(-5);

        $this->assertSame(0, $pass->getPrecision());
    }

    public function testOptimizeEmptyDocument(): void
    {
        $pass = new RoundValuesPass();
        $document = new Document();

        $pass->optimize($document);

        $this->assertNull($document->getRootElement());
    }

    public function testRoundNumericAttributes(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('x', '10.12345');
        $svg->setAttribute('y', '20.98765');
        $svg->setAttribute('width', '100.999');
        $svg->setAttribute('height', '50.001');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('10.12', $svg->getAttribute('x'));
        $this->assertSame('20.99', $svg->getAttribute('y'));
        $this->assertSame('101', $svg->getAttribute('width'));
        $this->assertSame('50', $svg->getAttribute('height'));
    }

    public function testRoundWithZeroPrecision(): void
    {
        $pass = new RoundValuesPass(0);
        $svg = new SvgElement();
        $svg->setAttribute('x', '10.5');
        $svg->setAttribute('y', '20.4');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('11', $svg->getAttribute('x'));
        $this->assertSame('20', $svg->getAttribute('y'));
    }

    public function testRoundCircleAttributes(): void
    {
        $pass = new RoundValuesPass(1);
        $svg = new SvgElement();
        $svg->setAttribute('cx', '15.555');
        $svg->setAttribute('cy', '25.444');
        $svg->setAttribute('r', '10.888');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('15.6', $svg->getAttribute('cx'));
        $this->assertSame('25.4', $svg->getAttribute('cy'));
        $this->assertSame('10.9', $svg->getAttribute('r'));
    }

    public function testRoundTransformAttribute(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('transform', 'translate(10.12345 20.98765)');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('translate(10.12 20.99)', $svg->getAttribute('transform'));
    }

    public function testRoundTransformMatrixAttribute(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('transform', 'matrix(1.11111 2.22222 3.33333 4.44444 5.55555 6.66666)');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('matrix(1.11 2.22 3.33 4.44 5.56 6.67)', $svg->getAttribute('transform'));
    }

    public function testRoundPathDataAttribute(): void
    {
        $pass = new RoundValuesPass(1);
        $path = new PathElement();
        $path->setPathData('M 10.555 20.444 L 30.999 40.111');

        $svg = new SvgElement();
        $svg->appendChild($path);
        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('M 10.6 20.4 L 31 40.1', $path->getPathData());
    }

    public function testRoundViewBoxAttribute(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('viewBox', '0.123 0.456 100.789 200.111');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('0.12 0.46 100.79 200.11', $svg->getAttribute('viewBox'));
    }

    public function testRoundNestedElements(): void
    {
        $pass = new RoundValuesPass(1);
        $svg = new SvgElement();
        $group = new GroupElement();
        $path = new PathElement();

        $svg->setAttribute('x', '10.555');
        $group->setAttribute('y', '20.444');
        $path->setAttribute('stroke-width', '1.999');

        $svg->appendChild($group);
        $group->appendChild($path);

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('10.6', $svg->getAttribute('x'));
        $this->assertSame('20.4', $group->getAttribute('y'));
        $this->assertSame('2', $path->getAttribute('stroke-width'));
    }

    public function testRemoveTrailingZeros(): void
    {
        $pass = new RoundValuesPass(3);
        $svg = new SvgElement();
        $svg->setAttribute('x', '10.500');
        $svg->setAttribute('y', '20.100');
        $svg->setAttribute('width', '30.000');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('10.5', $svg->getAttribute('x'));
        $this->assertSame('20.1', $svg->getAttribute('y'));
        $this->assertSame('30', $svg->getAttribute('width'));
    }

    public function testPreserveNonNumericAttributes(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('id', 'my-svg');
        $svg->setAttribute('class', 'test-class');
        $svg->setAttribute('fill', 'red');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('my-svg', $svg->getAttribute('id'));
        $this->assertSame('test-class', $svg->getAttribute('class'));
        $this->assertSame('red', $svg->getAttribute('fill'));
    }

    public function testRoundNegativeValues(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        $svg->setAttribute('x', '-10.12345');
        $svg->setAttribute('y', '-20.98765');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertSame('-10.12', $svg->getAttribute('x'));
        $this->assertSame('-20.99', $svg->getAttribute('y'));
    }

    public function testRoundCompoundAttributeNullValue(): void
    {
        $pass = new RoundValuesPass(2);
        $svg = new SvgElement();
        // Element has transform attribute set then removed
        $svg->setAttribute('transform', 'translate(10, 20)');
        $svg->removeAttribute('transform');

        $document = new Document($svg);

        $pass->optimize($document);

        $this->assertNull($svg->getAttribute('transform'));
    }
}
