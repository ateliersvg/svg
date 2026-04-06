<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Element\Accessibility;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Accessibility\Accessibility;
use Atelier\Svg\Element\Descriptive\DescElement;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Gradient\StopElement;
use Atelier\Svg\Element\Hyperlinking\AnchorElement;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\GroupElement;
use Atelier\Svg\Element\Structural\UseElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Accessibility::class)]
final class AccessibilityTest extends TestCase
{
    public function testSetTitleReturnsDocumentWhenNoRoot(): void
    {
        $doc = new Document();

        $result = Accessibility::setTitle($doc, 'Title');

        $this->assertSame($doc, $result);
    }

    public function testSetDescriptionReturnsDocumentWhenNoRoot(): void
    {
        $doc = new Document();

        $result = Accessibility::setDescription($doc, 'Description');

        $this->assertSame($doc, $result);
    }

    public function testCheckAccessibilityReturnsEmptyWhenNoRoot(): void
    {
        $doc = new Document();

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertEmpty($issues);
    }

    public function testCheckAccessibilityContainerUseWithTitleChildHasNoAltIssue(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $containerUse = new class extends \Atelier\Svg\Element\AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('use');
            }
        };
        $containerUse->setAttribute('role', 'img');
        $title = new TitleElement();
        $title->setContent('Use element title');
        $containerUse->appendChild($title);
        $doc->getRootElement()->appendChild($containerUse);

        $issues = Accessibility::checkAccessibility($doc);

        $hasAltIssue = false;
        foreach ($issues as $issue) {
            if ('error' === $issue['severity'] && str_contains($issue['message'], 'text alternative')) {
                $hasAltIssue = true;
                break;
            }
        }
        $this->assertFalse($hasAltIssue);
    }

    public function testImproveAccessibilityReturnsDocumentWhenNoRoot(): void
    {
        $doc = new Document();

        $result = Accessibility::improveAccessibility($doc);

        $this->assertSame($doc, $result);
    }

    public function testSetTitle(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test Title');

        $children = $doc->getRootElement()->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(TitleElement::class, $children[0]);
        $this->assertEquals('Test Title', $children[0]->getContent());
    }

    public function testSetTitleUpdatesExistingTitle(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'First Title');
        Accessibility::setTitle($doc, 'Updated Title');

        $children = $doc->getRootElement()->getChildren();
        $titleElements = array_filter($children, fn ($c) => $c instanceof TitleElement);
        $this->assertCount(1, $titleElements);
        $this->assertEquals('Updated Title', reset($titleElements)->getContent());
    }

    public function testSetDescription(): void
    {
        $doc = Document::create();
        Accessibility::setDescription($doc, 'Test Description');

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

    public function testSetDescriptionUpdatesExistingDescription(): void
    {
        $doc = Document::create();
        Accessibility::setDescription($doc, 'First Description');
        Accessibility::setDescription($doc, 'Updated Description');

        $children = $doc->getRootElement()->getChildren();
        $descElements = array_filter($children, fn ($c) => $c instanceof DescElement);
        $this->assertCount(1, $descElements);
        $this->assertEquals('Updated Description', reset($descElements)->getContent());
    }

    public function testAddTitleToElement(): void
    {
        $group = new GroupElement();
        Accessibility::addTitle($group, 'Group Title');

        $children = $group->getChildren();
        $this->assertCount(1, $children);
        $this->assertInstanceOf(TitleElement::class, $children[0]);
        $this->assertEquals('Group Title', $children[0]->getContent());
    }

    public function testAddTitleUpdatesExistingTitle(): void
    {
        $group = new GroupElement();
        Accessibility::addTitle($group, 'First Title');
        Accessibility::addTitle($group, 'Updated Title');

        $children = $group->getChildren();
        $titleElements = array_filter($children, fn ($c) => $c instanceof TitleElement);
        $this->assertCount(1, $titleElements);
        $this->assertEquals('Updated Title', reset($titleElements)->getContent());
    }

    public function testAddTitleToNonContainerUsesAriaLabel(): void
    {
        $stop = new StopElement();
        Accessibility::addTitle($stop, 'Stop Title');

        $this->assertEquals('Stop Title', $stop->getAttribute('aria-label'));
    }

    public function testAddDescriptionToElement(): void
    {
        $group = new GroupElement();
        Accessibility::addDescription($group, 'Group Description');

        $children = $group->getChildren();
        $descElement = null;
        foreach ($children as $child) {
            if ($child instanceof DescElement) {
                $descElement = $child;
                break;
            }
        }

        $this->assertNotNull($descElement);
        $this->assertEquals('Group Description', $descElement->getContent());
    }

    public function testAddDescriptionUpdatesExistingDescription(): void
    {
        $group = new GroupElement();
        Accessibility::addDescription($group, 'First Description');
        Accessibility::addDescription($group, 'Updated Description');

        $children = $group->getChildren();
        $descElements = array_filter($children, fn ($c) => $c instanceof DescElement);
        $this->assertCount(1, $descElements);
        $this->assertEquals('Updated Description', reset($descElements)->getContent());
    }

    public function testSetAriaLabel(): void
    {
        $element = new RectElement();
        Accessibility::setAriaLabel($element, 'Icon Label');

        $this->assertEquals('Icon Label', $element->getAttribute('aria-label'));
    }

    public function testSetAriaRole(): void
    {
        $element = new RectElement();
        Accessibility::setAriaRole($element, 'img');

        $this->assertEquals('img', $element->getAttribute('role'));
    }

    public function testSetFocusableTrue(): void
    {
        $element = new RectElement();
        Accessibility::setFocusable($element, true);

        $this->assertEquals('true', $element->getAttribute('focusable'));
    }

    public function testSetFocusableFalse(): void
    {
        $element = new RectElement();
        Accessibility::setFocusable($element, false);

        $this->assertEquals('false', $element->getAttribute('focusable'));
    }

    public function testSetTabIndex(): void
    {
        $element = new RectElement();
        Accessibility::setTabIndex($element, 0);

        $this->assertEquals('0', $element->getAttribute('tabindex'));
    }

    public function testSetTabIndexNegative(): void
    {
        $element = new RectElement();
        Accessibility::setTabIndex($element, -1);

        $this->assertEquals('-1', $element->getAttribute('tabindex'));
    }

    public function testCheckAccessibilityFindsNoIssuesWithCompleteDocument(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Accessible SVG');

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertIsArray($issues);
        $this->assertEmpty($issues);
    }

    public function testCheckAccessibilityFindsMissingTitle(): void
    {
        $doc = Document::create();

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertNotEmpty($issues);
        $hasNoTitleIssue = false;
        foreach ($issues as $issue) {
            if (str_contains($issue['message'], 'title')) {
                $hasNoTitleIssue = true;
                break;
            }
        }
        $this->assertTrue($hasNoTitleIssue);
    }

    public function testImproveAccessibilityAddsTitle(): void
    {
        $doc = Document::create();

        Accessibility::improveAccessibility($doc);

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

    public function testImproveAccessibilityPreservesExistingTitle(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Custom Title');

        Accessibility::improveAccessibility($doc);

        $children = $doc->getRootElement()->getChildren();
        $titleElements = array_filter($children, fn ($c) => $c instanceof TitleElement);
        $this->assertCount(1, $titleElements);
        $this->assertEquals('Custom Title', reset($titleElements)->getContent());
    }

    public function testImproveAccessibilityWithCustomOptions(): void
    {
        $doc = Document::create();

        Accessibility::improveAccessibility($doc, [
            'add_missing_titles' => false,
            'add_role_attributes' => false,
            'ensure_focusable' => false,
        ]);

        $children = $doc->getRootElement()->getChildren();
        $this->assertEmpty($children);
    }

    public function testMethodChainingReturnsDocument(): void
    {
        $doc = Document::create();

        $result = Accessibility::setTitle($doc, 'Title');
        $this->assertSame($doc, $result);

        $result = Accessibility::setDescription($doc, 'Description');
        $this->assertSame($doc, $result);

        $result = Accessibility::improveAccessibility($doc);
        $this->assertSame($doc, $result);
    }

    public function testMethodChainingReturnsElement(): void
    {
        $group = new GroupElement();

        $result = Accessibility::addTitle($group, 'Title');
        $this->assertSame($group, $result);

        $result = Accessibility::addDescription($group, 'Description');
        $this->assertSame($group, $result);

        $rect = new RectElement();

        $result = Accessibility::setAriaLabel($rect, 'Label');
        $this->assertSame($rect, $result);

        $result = Accessibility::setAriaRole($rect, 'img');
        $this->assertSame($rect, $result);

        $result = Accessibility::setFocusable($rect, true);
        $this->assertSame($rect, $result);

        $result = Accessibility::setTabIndex($rect, 0);
        $this->assertSame($rect, $result);
    }

    public function testAddDescriptionToNonContainerReturnsElement(): void
    {
        $stop = new StopElement();
        $result = Accessibility::addDescription($stop, 'Stop Description');

        $this->assertSame($stop, $result);
        $this->assertFalse($stop->hasAttribute('aria-label'));
    }

    public function testSetTabIndexPositive(): void
    {
        $element = new RectElement();
        Accessibility::setTabIndex($element, 5);

        $this->assertEquals('5', $element->getAttribute('tabindex'));
    }

    public function testCheckAccessibilityFindsImageWithoutTextAlternative(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $image = new ImageElement();
        $doc->getRootElement()->appendChild($image);

        $issues = Accessibility::checkAccessibility($doc);

        $hasImageAltIssue = false;
        foreach ($issues as $issue) {
            if ('error' === $issue['severity'] && str_contains($issue['message'], 'text alternative')) {
                $hasImageAltIssue = true;
                break;
            }
        }
        $this->assertTrue($hasImageAltIssue);
    }

    public function testCheckAccessibilityFindsImageWithoutRole(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $image = new ImageElement();
        $doc->getRootElement()->appendChild($image);

        $issues = Accessibility::checkAccessibility($doc);

        $hasRoleIssue = false;
        foreach ($issues as $issue) {
            if ('warning' === $issue['severity'] && str_contains($issue['message'], 'role')) {
                $hasRoleIssue = true;
                break;
            }
        }
        $this->assertTrue($hasRoleIssue);
    }

    public function testCheckAccessibilityImageWithAriaLabelHasNoAltIssue(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $image = new ImageElement();
        $image->setAttribute('aria-label', 'Decorative image');
        $image->setAttribute('role', 'img');
        $doc->getRootElement()->appendChild($image);

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertEmpty($issues);
    }

    public function testCheckAccessibilityFindsUseElementWithoutTextAlternative(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $use = new UseElement();
        $doc->getRootElement()->appendChild($use);

        $issues = Accessibility::checkAccessibility($doc);

        $hasAltIssue = false;
        foreach ($issues as $issue) {
            if ('error' === $issue['severity'] && str_contains($issue['message'], 'text alternative')) {
                $hasAltIssue = true;
                break;
            }
        }
        $this->assertTrue($hasAltIssue);
    }

    public function testCheckAccessibilityFindsInteractiveElementWithoutKeyboardAccess(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $anchor = new AnchorElement('a');
        $doc->getRootElement()->appendChild($anchor);

        $issues = Accessibility::checkAccessibility($doc);

        $hasKeyboardIssue = false;
        foreach ($issues as $issue) {
            if ('warning' === $issue['severity'] && str_contains($issue['message'], 'keyboard')) {
                $hasKeyboardIssue = true;
                break;
            }
        }
        $this->assertTrue($hasKeyboardIssue);
    }

    public function testCheckAccessibilityInteractiveElementWithTabindexHasNoIssue(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $anchor = new AnchorElement('a');
        $anchor->setAttribute('tabindex', '0');
        $doc->getRootElement()->appendChild($anchor);

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertEmpty($issues);
    }

    public function testCheckAccessibilityInteractiveElementWithFocusableHasNoIssue(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $anchor = new AnchorElement('a');
        $anchor->setAttribute('focusable', 'true');
        $doc->getRootElement()->appendChild($anchor);

        $issues = Accessibility::checkAccessibility($doc);

        $this->assertEmpty($issues);
    }

    public function testCheckAccessibilityOnclickElementWithoutKeyboardAccess(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $rect = new RectElement();
        $rect->setAttribute('onclick', 'doSomething()');
        $doc->getRootElement()->appendChild($rect);

        $issues = Accessibility::checkAccessibility($doc);

        $hasKeyboardIssue = false;
        foreach ($issues as $issue) {
            if (str_contains($issue['message'], 'keyboard')) {
                $hasKeyboardIssue = true;
                break;
            }
        }
        $this->assertTrue($hasKeyboardIssue);
    }

    public function testCheckAccessibilityElementIdInIssue(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $image = new ImageElement();
        $image->setAttribute('id', 'my-image');
        $doc->getRootElement()->appendChild($image);

        $issues = Accessibility::checkAccessibility($doc);

        $hasIdReference = false;
        foreach ($issues as $issue) {
            if (isset($issue['element']) && str_contains($issue['element'], 'my-image')) {
                $hasIdReference = true;
                break;
            }
        }
        $this->assertTrue($hasIdReference);
    }

    public function testCheckAccessibilityElementWithoutIdUsesUnknown(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $image = new ImageElement();
        $doc->getRootElement()->appendChild($image);

        $issues = Accessibility::checkAccessibility($doc);

        $hasUnknownReference = false;
        foreach ($issues as $issue) {
            if (isset($issue['element']) && str_contains($issue['element'], 'unknown')) {
                $hasUnknownReference = true;
                break;
            }
        }
        $this->assertTrue($hasUnknownReference);
    }

    public function testCheckAccessibilityRecursesIntoChildren(): void
    {
        $doc = Document::create();
        Accessibility::setTitle($doc, 'Test');

        $group = new GroupElement();
        $image = new ImageElement();
        $group->appendChild($image);
        $doc->getRootElement()->appendChild($group);

        $issues = Accessibility::checkAccessibility($doc);

        $hasImageIssue = false;
        foreach ($issues as $issue) {
            if (str_contains($issue['message'], 'Image element')) {
                $hasImageIssue = true;
                break;
            }
        }
        $this->assertTrue($hasImageIssue);
    }

    public function testImproveAccessibilityAddsRoleToImageElement(): void
    {
        $doc = Document::create();

        $image = new ImageElement();
        $doc->getRootElement()->appendChild($image);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('img', $image->getAttribute('role'));
    }

    public function testImproveAccessibilityAddsRoleToUseElement(): void
    {
        $doc = Document::create();

        $use = new UseElement();
        $doc->getRootElement()->appendChild($use);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('img', $use->getAttribute('role'));
    }

    public function testImproveAccessibilityPreservesExistingRole(): void
    {
        $doc = Document::create();

        $image = new ImageElement();
        $image->setAttribute('role', 'presentation');
        $doc->getRootElement()->appendChild($image);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('presentation', $image->getAttribute('role'));
    }

    public function testImproveAccessibilityMakesInteractiveElementFocusable(): void
    {
        $doc = Document::create();

        $anchor = new AnchorElement('a');
        $doc->getRootElement()->appendChild($anchor);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('0', $anchor->getAttribute('tabindex'));
    }

    public function testImproveAccessibilityPreservesExistingTabindex(): void
    {
        $doc = Document::create();

        $anchor = new AnchorElement('a');
        $anchor->setAttribute('tabindex', '-1');
        $doc->getRootElement()->appendChild($anchor);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('-1', $anchor->getAttribute('tabindex'));
    }

    public function testImproveAccessibilityPreservesExistingFocusable(): void
    {
        $doc = Document::create();

        $anchor = new AnchorElement('a');
        $anchor->setAttribute('focusable', 'true');
        $doc->getRootElement()->appendChild($anchor);

        Accessibility::improveAccessibility($doc);

        $this->assertFalse($anchor->hasAttribute('tabindex'));
    }

    public function testImproveAccessibilityOnclickElement(): void
    {
        $doc = Document::create();

        $rect = new RectElement();
        $rect->setAttribute('onclick', 'doSomething()');
        $doc->getRootElement()->appendChild($rect);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('0', $rect->getAttribute('tabindex'));
    }

    public function testImproveAccessibilityDisableAddRoleOption(): void
    {
        $doc = Document::create();

        $image = new ImageElement();
        $doc->getRootElement()->appendChild($image);

        Accessibility::improveAccessibility($doc, ['add_role_attributes' => false]);

        $this->assertFalse($image->hasAttribute('role'));
    }

    public function testImproveAccessibilityDisableEnsureFocusableOption(): void
    {
        $doc = Document::create();

        $anchor = new AnchorElement('a');
        $doc->getRootElement()->appendChild($anchor);

        Accessibility::improveAccessibility($doc, ['ensure_focusable' => false]);

        $this->assertFalse($anchor->hasAttribute('tabindex'));
    }

    public function testImproveAccessibilityRecursesIntoChildren(): void
    {
        $doc = Document::create();

        $group = new GroupElement();
        $image = new ImageElement();
        $group->appendChild($image);
        $doc->getRootElement()->appendChild($group);

        Accessibility::improveAccessibility($doc);

        $this->assertEquals('img', $image->getAttribute('role'));
    }
}
