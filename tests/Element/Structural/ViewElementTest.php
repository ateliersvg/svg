<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Structural;

use Atelier\Svg\Element\Structural\ViewElement;
use Atelier\Svg\Value\PreserveAspectRatio;
use Atelier\Svg\Value\Viewbox;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ViewElement::class)]
final class ViewElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $view = new ViewElement();

        $this->assertSame('view', $view->getTagName());
    }

    public function testSetAndGetViewboxWithString(): void
    {
        $view = new ViewElement();
        $result = $view->setViewbox('0 0 100 100');

        $this->assertSame($view, $result, 'setViewbox should return self for chaining');

        $viewbox = $view->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $viewbox);
        $this->assertSame(0.0, $viewbox->getMinX());
        $this->assertSame(0.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(100.0, $viewbox->getHeight());
    }

    public function testSetAndGetViewboxWithObject(): void
    {
        $view = new ViewElement();
        $viewbox = new Viewbox(10, 20, 200, 300);
        $view->setViewbox($viewbox);

        $retrieved = $view->getViewbox();
        $this->assertInstanceOf(Viewbox::class, $retrieved);
        $this->assertSame(10.0, $retrieved->getMinX());
        $this->assertSame(20.0, $retrieved->getMinY());
        $this->assertSame(200.0, $retrieved->getWidth());
        $this->assertSame(300.0, $retrieved->getHeight());
    }

    public function testGetViewboxReturnsNullWhenNotSet(): void
    {
        $view = new ViewElement();

        $this->assertNull($view->getViewbox());
    }

    public function testSetAndGetPreserveAspectRatioWithString(): void
    {
        $view = new ViewElement();
        $result = $view->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($view, $result, 'setPreserveAspectRatio should return self for chaining');

        $par = $view->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $par);
        $this->assertSame('xMidYMid', $par->getAlign());
        $this->assertSame('meet', $par->getMeetOrSlice());
    }

    public function testSetAndGetPreserveAspectRatioWithObject(): void
    {
        $view = new ViewElement();
        $par = PreserveAspectRatio::fromAlignment('xMax', 'YMax', 'slice');
        $view->setPreserveAspectRatio($par);

        $retrieved = $view->getPreserveAspectRatio();
        $this->assertInstanceOf(PreserveAspectRatio::class, $retrieved);
        $this->assertSame('xMaxYMax', $retrieved->getAlign());
        $this->assertSame('slice', $retrieved->getMeetOrSlice());
    }

    public function testGetPreserveAspectRatioReturnsNullWhenNotSet(): void
    {
        $view = new ViewElement();

        $this->assertNull($view->getPreserveAspectRatio());
    }

    public function testMethodChaining(): void
    {
        $view = new ViewElement();
        $result = $view
            ->setAttribute('id', 'mainView')
            ->setViewbox('0 0 100 100')
            ->setPreserveAspectRatio('xMidYMid meet');

        $this->assertSame($view, $result);
        $this->assertSame('mainView', $view->getAttribute('id'));
        $this->assertInstanceOf(Viewbox::class, $view->getViewbox());
        $this->assertInstanceOf(PreserveAspectRatio::class, $view->getPreserveAspectRatio());
    }

    public function testCompleteViewConfiguration(): void
    {
        $view = new ViewElement();
        $view
            ->setAttribute('id', 'zoomedView')
            ->setViewbox('50 50 100 100')
            ->setPreserveAspectRatio('xMinYMin slice');

        $this->assertSame('zoomedView', $view->getAttribute('id'));
        $this->assertSame('50 50 100 100', $view->getAttribute('viewBox'));
        $this->assertSame('xMinYMin slice', $view->getAttribute('preserveAspectRatio'));

        $viewbox = $view->getViewbox();
        $this->assertSame(50.0, $viewbox->getMinX());
        $this->assertSame(50.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(100.0, $viewbox->getHeight());

        $par = $view->getPreserveAspectRatio();
        $this->assertSame('xMinYMin', $par->getAlign());
        $this->assertSame('slice', $par->getMeetOrSlice());
    }

    public function testViewWithDifferentViewBoxes(): void
    {
        $view = new ViewElement();

        // First viewbox
        $view->setViewbox('0 0 200 200');
        $viewbox1 = $view->getViewbox();
        $this->assertSame(200.0, $viewbox1->getWidth());

        // Update viewbox
        $view->setViewbox('0 0 400 400');
        $viewbox2 = $view->getViewbox();
        $this->assertSame(400.0, $viewbox2->getWidth());
    }

    public function testViewWithNegativeViewBox(): void
    {
        $view = new ViewElement();
        $view->setViewbox('-50 -50 100 100');

        $viewbox = $view->getViewbox();
        $this->assertSame(-50.0, $viewbox->getMinX());
        $this->assertSame(-50.0, $viewbox->getMinY());
        $this->assertSame(100.0, $viewbox->getWidth());
        $this->assertSame(100.0, $viewbox->getHeight());
    }

    public function testViewWithDecimalViewBox(): void
    {
        $view = new ViewElement();
        $view->setViewbox('0.5 10.25 100.75 200.5');

        $viewbox = $view->getViewbox();
        $this->assertSame(0.5, $viewbox->getMinX());
        $this->assertSame(10.25, $viewbox->getMinY());
        $this->assertSame(100.75, $viewbox->getWidth());
        $this->assertSame(200.5, $viewbox->getHeight());
    }

    public function testViewWithDifferentPreserveAspectRatioOptions(): void
    {
        $view = new ViewElement();

        // Test meet
        $view->setPreserveAspectRatio('xMidYMid meet');
        $par1 = $view->getPreserveAspectRatio();
        $this->assertSame('meet', $par1->getMeetOrSlice());

        // Test slice
        $view->setPreserveAspectRatio('xMaxYMax slice');
        $par2 = $view->getPreserveAspectRatio();
        $this->assertSame('slice', $par2->getMeetOrSlice());
        $this->assertSame('xMaxYMax', $par2->getAlign());
    }

    public function testViewAsNavigationTarget(): void
    {
        $view = new ViewElement();
        $view
            ->setAttribute('id', 'detailView')
            ->setViewbox('100 100 50 50')
            ->setPreserveAspectRatio('xMidYMid meet');

        // Verify it can be used as a fragment identifier target
        $this->assertSame('detailView', $view->getAttribute('id'));

        $viewbox = $view->getViewbox();
        $this->assertSame(100.0, $viewbox->getMinX());
        $this->assertSame(100.0, $viewbox->getMinY());
        $this->assertSame(50.0, $viewbox->getWidth());
        $this->assertSame(50.0, $viewbox->getHeight());
    }
}
