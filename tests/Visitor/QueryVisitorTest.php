<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Visitor;

use Atelier\Svg\Element\PathElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Selector\SelectorMatcher;
use Atelier\Svg\Visitor\QueryVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QueryVisitor::class)]
final class QueryVisitorTest extends TestCase
{
    private SelectorMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new SelectorMatcher();
    }

    public function testGetMatchesReturnsEmptyArrayInitially(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);

        $this->assertSame([], $visitor->getMatches());
    }

    public function testHasMatchesReturnsFalseInitially(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);

        $this->assertFalse($visitor->hasMatches());
    }

    public function testGetMatchCountReturnsZeroInitially(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);

        $this->assertSame(0, $visitor->getMatchCount());
    }

    public function testGetFirstMatchReturnsNullInitially(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);

        $this->assertNull($visitor->getFirstMatch());
    }

    public function testVisitMatchesElementByTagName(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);
        $rect = new RectElement();

        $visitor->visit($rect);

        $this->assertTrue($visitor->hasMatches());
        $this->assertSame(1, $visitor->getMatchCount());
        $this->assertSame($rect, $visitor->getFirstMatch());
    }

    public function testVisitDoesNotMatchDifferentTag(): void
    {
        $visitor = new QueryVisitor('circle', $this->matcher);
        $rect = new RectElement();

        $visitor->visit($rect);

        $this->assertFalse($visitor->hasMatches());
        $this->assertSame(0, $visitor->getMatchCount());
    }

    public function testVisitCollectsMultipleMatches(): void
    {
        $visitor = new QueryVisitor('rect', $this->matcher);
        $rect1 = new RectElement();
        $rect2 = new RectElement();

        $visitor->visit($rect1);
        $visitor->visit($rect2);

        $this->assertSame(2, $visitor->getMatchCount());
        $this->assertSame([$rect1, $rect2], $visitor->getMatches());
    }

    public function testVisitMatchesById(): void
    {
        $visitor = new QueryVisitor('#myRect', $this->matcher);
        $rect = new RectElement();
        $rect->setAttribute('id', 'myRect');

        $visitor->visit($rect);

        $this->assertTrue($visitor->hasMatches());
        $this->assertSame($rect, $visitor->getFirstMatch());
    }

    public function testVisitMatchesByClass(): void
    {
        $visitor = new QueryVisitor('.highlight', $this->matcher);
        $rect = new RectElement();
        $rect->addClass('highlight');

        $visitor->visit($rect);

        $this->assertTrue($visitor->hasMatches());
        $this->assertSame($rect, $visitor->getFirstMatch());
    }

    public function testVisitMatchesUniversalSelector(): void
    {
        $visitor = new QueryVisitor('*', $this->matcher);
        $rect = new RectElement();
        $circle = new CircleElement();

        $visitor->visit($rect);
        $visitor->visit($circle);

        $this->assertSame(2, $visitor->getMatchCount());
    }

    public function testFindFirstStopsAfterFirstMatch(): void
    {
        $visitor = new QueryVisitor('*', $this->matcher, findFirst: true);
        $rect = new RectElement();
        $circle = new CircleElement();
        $path = new PathElement();

        $visitor->visit($rect);
        $visitor->visit($circle);
        $visitor->visit($path);

        $this->assertSame(1, $visitor->getMatchCount());
        $this->assertSame($rect, $visitor->getFirstMatch());
    }

    public function testGetFirstMatchReturnsFirstElement(): void
    {
        $visitor = new QueryVisitor('*', $this->matcher);
        $rect = new RectElement();
        $circle = new CircleElement();

        $visitor->visit($rect);
        $visitor->visit($circle);

        $this->assertSame($rect, $visitor->getFirstMatch());
    }

    public function testVisitMatchesByAttribute(): void
    {
        $visitor = new QueryVisitor('[fill="red"]', $this->matcher);
        $rect = new RectElement();
        $rect->setAttribute('fill', 'red');

        $visitor->visit($rect);

        $this->assertTrue($visitor->hasMatches());
    }

    public function testVisitDoesNotMatchByAttributeWithDifferentValue(): void
    {
        $visitor = new QueryVisitor('[fill="red"]', $this->matcher);
        $rect = new RectElement();
        $rect->setAttribute('fill', 'blue');

        $visitor->visit($rect);

        $this->assertFalse($visitor->hasMatches());
    }

    public function testVisitMixedMatchAndNonMatch(): void
    {
        $visitor = new QueryVisitor('circle', $this->matcher);
        $rect = new RectElement();
        $circle = new CircleElement();
        $path = new PathElement();

        $visitor->visit($rect);
        $visitor->visit($circle);
        $visitor->visit($path);

        $this->assertSame(1, $visitor->getMatchCount());
        $this->assertSame($circle, $visitor->getFirstMatch());
    }
}
