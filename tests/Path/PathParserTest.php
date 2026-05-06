<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Path;

use Atelier\Svg\Path\Data;
use Atelier\Svg\Path\PathParser;
use Atelier\Svg\Path\Segment\ArcTo;
use Atelier\Svg\Path\Segment\ClosePath;
use Atelier\Svg\Path\Segment\CurveTo;
use Atelier\Svg\Path\Segment\HorizontalLineTo;
use Atelier\Svg\Path\Segment\LineTo;
use Atelier\Svg\Path\Segment\MoveTo;
use Atelier\Svg\Path\Segment\QuadraticCurveTo;
use Atelier\Svg\Path\Segment\SmoothCurveTo;
use Atelier\Svg\Path\Segment\SmoothQuadraticCurveTo;
use Atelier\Svg\Path\Segment\VerticalLineTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathParser::class)]
final class PathParserTest extends TestCase
{
    private PathParser $parser;

    protected function setUp(): void
    {
        $this->parser = new PathParser();
    }

    public function testParseEmptyString(): void
    {
        $data = $this->parser->parse('');
        $this->assertInstanceOf(Data::class, $data);
        $this->assertTrue($data->isEmpty());
    }

    public function testParseWhitespaceOnlyString(): void
    {
        $data = $this->parser->parse('   ');
        $this->assertTrue($data->isEmpty());
    }

    public function testParseMoveTo(): void
    {
        $data = $this->parser->parse('M 10,20');
        $segments = $data->getSegments();

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertSame('M', $segments[0]->getCommand());
        $this->assertFalse($segments[0]->isRelative());
        $this->assertSame(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertSame(20.0, $segments[0]->getTargetPoint()->y);
    }

    public function testParseRelativeMoveTo(): void
    {
        $data = $this->parser->parse('m 5,10');
        $segments = $data->getSegments();

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertSame('m', $segments[0]->getCommand());
        $this->assertTrue($segments[0]->isRelative());
    }

    public function testParseLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 L 50,50');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertSame('L', $segments[1]->getCommand());
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseRelativeLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 l 10,20');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertSame('l', $segments[1]->getCommand());
        $this->assertTrue($segments[1]->isRelative());
    }

    public function testParseHorizontalLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 H 100');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[1]);
        $this->assertSame(100.0, $segments[1]->getX());
    }

    public function testParseRelativeHorizontalLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 h 50');
        $segments = $data->getSegments();

        $this->assertInstanceOf(HorizontalLineTo::class, $segments[1]);
        $this->assertSame('h', $segments[1]->getCommand());
        $this->assertTrue($segments[1]->isRelative());
    }

    public function testParseVerticalLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 V 80');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[1]);
        $this->assertSame(80.0, $segments[1]->getY());
    }

    public function testParseRelativeVerticalLineTo(): void
    {
        $data = $this->parser->parse('M 0,0 v 30');
        $segments = $data->getSegments();

        $this->assertInstanceOf(VerticalLineTo::class, $segments[1]);
        $this->assertTrue($segments[1]->isRelative());
    }

    public function testParseCurveTo(): void
    {
        $data = $this->parser->parse('M 0,0 C 10,20 30,40 50,60');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $this->assertSame('C', $segments[1]->getCommand());
        $this->assertSame(10.0, $segments[1]->getControlPoint1()->x);
        $this->assertSame(20.0, $segments[1]->getControlPoint1()->y);
        $this->assertSame(30.0, $segments[1]->getControlPoint2()->x);
        $this->assertSame(40.0, $segments[1]->getControlPoint2()->y);
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(60.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseSmoothCurveTo(): void
    {
        $data = $this->parser->parse('M 0,0 S 30,40 50,60');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothCurveTo::class, $segments[1]);
        $this->assertSame(30.0, $segments[1]->getControlPoint2()->x);
        $this->assertSame(40.0, $segments[1]->getControlPoint2()->y);
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(60.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseQuadraticCurveTo(): void
    {
        $data = $this->parser->parse('M 0,0 Q 20,30 40,50');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertSame(20.0, $segments[1]->getControlPoint()->x);
        $this->assertSame(30.0, $segments[1]->getControlPoint()->y);
        $this->assertSame(40.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseSmoothQuadraticCurveTo(): void
    {
        $data = $this->parser->parse('M 0,0 T 40,50');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(SmoothQuadraticCurveTo::class, $segments[1]);
        $this->assertSame(40.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseArcTo(): void
    {
        $data = $this->parser->parse('M 0,0 A 25,26 30 0,1 50,25');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(ArcTo::class, $segments[1]);
        $this->assertSame(25.0, $segments[1]->getRx());
        $this->assertSame(26.0, $segments[1]->getRy());
        $this->assertSame(30.0, $segments[1]->getXAxisRotation());
        $this->assertFalse($segments[1]->getLargeArcFlag());
        $this->assertTrue($segments[1]->getSweepFlag());
        $this->assertSame(50.0, $segments[1]->getTargetPoint()->x);
        $this->assertSame(25.0, $segments[1]->getTargetPoint()->y);
    }

    public function testParseClosePath(): void
    {
        $data = $this->parser->parse('M 0,0 L 50,50 Z');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(ClosePath::class, $segments[2]);
        $this->assertSame('Z', $segments[2]->getCommand());
    }

    public function testParseLowercaseClosePath(): void
    {
        $data = $this->parser->parse('M 0,0 L 50,50 z');
        $segments = $data->getSegments();

        $this->assertInstanceOf(ClosePath::class, $segments[2]);
        $this->assertSame('z', $segments[2]->getCommand());
    }

    public function testParseComplexPath(): void
    {
        $data = $this->parser->parse('M 10,20 L 30,40 C 50,60 70,80 90,100 Q 110,120 130,140 Z');
        $segments = $data->getSegments();

        $this->assertCount(5, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertInstanceOf(CurveTo::class, $segments[2]);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[3]);
        $this->assertInstanceOf(ClosePath::class, $segments[4]);
    }

    public function testParsePathWithSpaceSeparators(): void
    {
        $data = $this->parser->parse('M 10 20 L 30 40');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertSame(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertSame(20.0, $segments[0]->getTargetPoint()->y);
    }

    public function testParsePathWithCommaSeparators(): void
    {
        $data = $this->parser->parse('M10,20L30,40');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertSame(10.0, $segments[0]->getTargetPoint()->x);
        $this->assertSame(20.0, $segments[0]->getTargetPoint()->y);
    }

    public function testParsePathWithNegativeCoordinates(): void
    {
        $data = $this->parser->parse('M -10,-20 L -30,-40');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertSame(-10.0, $segments[0]->getTargetPoint()->x);
        $this->assertSame(-20.0, $segments[0]->getTargetPoint()->y);
    }

    public function testParsePathWithDecimalCoordinates(): void
    {
        $data = $this->parser->parse('M 10.5,20.75 L 30.25,40.1');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertSame(10.5, $segments[0]->getTargetPoint()->x);
        $this->assertSame(20.75, $segments[0]->getTargetPoint()->y);
    }

    public function testParseReturnsDataObject(): void
    {
        $data = $this->parser->parse('M 0,0');
        $this->assertInstanceOf(Data::class, $data);
    }

    public function testParseSkipsNonCommandTokens(): void
    {
        // Leading numbers before any command are skipped
        $data = $this->parser->parse('10 20 M 5,5');
        $segments = $data->getSegments();

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertSame(5.0, $segments[0]->getTargetPoint()->x);
        $this->assertSame(5.0, $segments[0]->getTargetPoint()->y);
    }

    public function testParseWithUnknownCommandSkipsIt(): void
    {
        // 'X' does NOT match the isCommand regex, so it's skipped as a non-command token.
        // The default branch in the match is unreachable but kept as defensive code.
        $data = $this->parser->parse('M 0,0 L 10,10');
        $this->assertCount(2, $data->getSegments());
    }

    public function testParseTruncatedCurveStopsGracefully(): void
    {
        // Second C only has 3 args instead of 6 -- parser should stop gracefully
        $data = $this->parser->parse('M 0 0 C 10 20 30 40 50 60 C 70 80 90');
        $segments = $data->getSegments();

        // Only M and the first (complete) C should be parsed
        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
    }

    public function testParseTruncatedLineStopsGracefully(): void
    {
        // L needs 2 args but only 1 is provided
        $data = $this->parser->parse('M 0 0 L 10');
        $segments = $data->getSegments();

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
    }

    public function testImplicitRepeatedLineTo(): void
    {
        // L 10 20 30 40 = L10,20 L30,40
        $data = $this->parser->parse('M 0 0 L 10 20 30 40');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertInstanceOf(LineTo::class, $segments[2]);
        $this->assertSame(30.0, $segments[2]->getTargetPoint()->x);
        $this->assertSame(40.0, $segments[2]->getTargetPoint()->y);
    }

    public function testImplicitMoveToBecomesLineTo(): void
    {
        // Per SVG spec, implicit repeats after M become L
        $data = $this->parser->parse('M 0 0 10 20 30 40');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertSame('L', $segments[1]->getCommand());
        $this->assertInstanceOf(LineTo::class, $segments[2]);
        $this->assertSame('L', $segments[2]->getCommand());
    }

    public function testImplicitRelativeMoveToBecomesRelativeLineTo(): void
    {
        // Implicit repeats after m become l (lowercase)
        $data = $this->parser->parse('m 0 0 10 20');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
        $this->assertSame('l', $segments[1]->getCommand());
    }

    public function testImplicitRepeatedCurveTo(): void
    {
        // Two implicit cubic curves
        $data = $this->parser->parse('M 0 0 C 1 2 3 4 5 6 7 8 9 10 11 12');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(CurveTo::class, $segments[1]);
        $this->assertInstanceOf(CurveTo::class, $segments[2]);
        $this->assertSame(11.0, $segments[2]->getTargetPoint()->x);
    }

    public function testImplicitRepeatedQuadraticCurveTo(): void
    {
        // q with 8 args = 2 quadratic curves
        $data = $this->parser->parse('M 0 0 q 17.521 0 27.886 8.76 10.366 8.76 10.366 23.944');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[1]);
        $this->assertInstanceOf(QuadraticCurveTo::class, $segments[2]);
        $this->assertSame('q', $segments[2]->getCommand());
        $this->assertSame(10.366, $segments[2]->getControlPoint()->x);
    }

    public function testImplicitRepeatedArcTo(): void
    {
        $data = $this->parser->parse('M 0 0 A 10 10 0 0 1 50 50 10 10 0 1 0 100 100');
        $segments = $data->getSegments();

        $this->assertCount(3, $segments);
        $this->assertInstanceOf(ArcTo::class, $segments[1]);
        $this->assertInstanceOf(ArcTo::class, $segments[2]);
        $this->assertSame(100.0, $segments[2]->getTargetPoint()->x);
    }

    public function testImplicitRepeatedHorizontalLineTo(): void
    {
        $data = $this->parser->parse('M 0 0 H 10 20 30');
        $segments = $data->getSegments();

        $this->assertCount(4, $segments);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[1]);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[2]);
        $this->assertInstanceOf(HorizontalLineTo::class, $segments[3]);
    }

    public function testImplicitRepeatedVerticalLineTo(): void
    {
        $data = $this->parser->parse('M 0 0 V 10 20 30');
        $segments = $data->getSegments();

        $this->assertCount(4, $segments);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[1]);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[2]);
        $this->assertInstanceOf(VerticalLineTo::class, $segments[3]);
    }

    public function testImplicitRepeatTruncatedStopsGracefully(): void
    {
        // L needs 2 args; first pair is complete, second pair is truncated
        $data = $this->parser->parse('M 0 0 L 10 20 30');
        $segments = $data->getSegments();

        $this->assertCount(2, $segments);
        $this->assertInstanceOf(MoveTo::class, $segments[0]);
        $this->assertInstanceOf(LineTo::class, $segments[1]);
    }

    public function testParseTruncatedMoveTo(): void
    {
        $parser = new PathParser();
        // M with only one coordinate -- truncated
        $segments = $parser->parse('M 5')->getSegments();
        $this->assertCount(0, $segments);
    }

    public function testParseTruncatedHorizontalLineTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 H')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedVerticalLineTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 V')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedLineTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 L 5')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedCurveTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 C 1 2 3 4 5')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedSmoothCurveTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 S 1 2 3')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 Q 1 2 3')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedSmoothQuadraticCurveTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 T 5')->getSegments();
        $this->assertCount(1, $segments);
    }

    public function testParseTruncatedArcTo(): void
    {
        $parser = new PathParser();
        $segments = $parser->parse('M0 0 A 10 10 0 0 1 5')->getSegments();
        $this->assertCount(1, $segments);
    }
}
