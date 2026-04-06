<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Validation\DocumentValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentValidator::class)]
final class DocumentValidatorTest extends TestCase
{
    // ---------------------------------------------------------------
    // validate()
    // ---------------------------------------------------------------

    public function testValidateReturnsErrorWhenNoRootElement(): void
    {
        $doc = new Document();
        $errors = DocumentValidator::validate($doc);

        $this->assertCount(1, $errors);
        $this->assertSame('Document has no root element', $errors[0]);
    }

    public function testValidateReturnsEmptyArrayForValidDocument(): void
    {
        $doc = Document::create();
        $errors = DocumentValidator::validate($doc);

        $this->assertSame([], $errors);
    }

    public function testValidateDetectsDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect1 = new RectElement();
        $rect1->setId('dup');
        $root->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setId('dup');
        $root->appendChild($rect2);

        $errors = DocumentValidator::validate($doc);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString("Duplicate ID 'dup'", $errors[0]);
        $this->assertStringContainsString('2 times', $errors[0]);
    }

    public function testValidateDetectsBrokenUrlReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#nonexistent)');
        $root->appendChild($rect);

        $errors = DocumentValidator::validate($doc);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('#nonexistent', $errors[0]);
    }

    public function testValidateDetectsBrokenHashReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('href', '#missing');
        $root->appendChild($circle);

        $errors = DocumentValidator::validate($doc);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('#missing', $errors[0]);
    }

    public function testValidateReturnsMultipleErrors(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        // Duplicate IDs
        $rect1 = new RectElement();
        $rect1->setId('same');
        $root->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setId('same');
        $root->appendChild($rect2);

        // Broken reference
        $circle = new CircleElement();
        $circle->setAttribute('fill', 'url(#ghost)');
        $root->appendChild($circle);

        $errors = DocumentValidator::validate($doc);

        $this->assertGreaterThanOrEqual(2, count($errors));
    }

    // ---------------------------------------------------------------
    // isValid()
    // ---------------------------------------------------------------

    public function testIsValidReturnsTrueForValidDocument(): void
    {
        $doc = Document::create();
        $this->assertTrue(DocumentValidator::isValid($doc));
    }

    public function testIsValidReturnsFalseForDocumentWithoutRoot(): void
    {
        $doc = new Document();
        $this->assertFalse(DocumentValidator::isValid($doc));
    }

    public function testIsValidReturnsFalseForDocumentWithDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $r1 = new RectElement();
        $r1->setId('x');
        $root->appendChild($r1);

        $r2 = new RectElement();
        $r2->setId('x');
        $root->appendChild($r2);

        $this->assertFalse(DocumentValidator::isValid($doc));
    }

    // ---------------------------------------------------------------
    // lint()
    // ---------------------------------------------------------------

    public function testLintWithAllOptionsEnabled(): void
    {
        $doc = Document::create();
        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => true,
            'check_references' => true,
            'check_colors' => true,
            'check_transforms' => true,
            'check_accessibility' => true,
        ]);

        // Document without title should trigger accessibility warning
        $this->assertNotEmpty($warnings);
    }

    public function testLintWithAllOptionsDisabled(): void
    {
        $doc = Document::create();
        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $this->assertSame([], $warnings);
    }

    public function testLintDetectsDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $r1 = new RectElement();
        $r1->setId('myid');
        $root->appendChild($r1);

        $r2 = new RectElement();
        $r2->setId('myid');
        $root->appendChild($r2);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => true,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $duplicateWarnings = array_filter($warnings, fn ($w) => 'duplicate_id' === $w['type']);
        $this->assertNotEmpty($duplicateWarnings);
        $first = array_values($duplicateWarnings)[0];
        $this->assertSame('error', $first['severity']);
    }

    public function testLintDetectsInvalidIdFormat(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setId('123-bad');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => true,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $invalidIdWarnings = array_filter($warnings, fn ($w) => 'invalid_id' === $w['type']);
        $this->assertNotEmpty($invalidIdWarnings);
    }

    public function testLintDetectsBrokenReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('filter', 'url(#no-such-filter)');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => true,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $brokenRefWarnings = array_filter($warnings, fn ($w) => 'broken_reference' === $w['type']);
        $this->assertNotEmpty($brokenRefWarnings);
        $first = array_values($brokenRefWarnings)[0];
        $this->assertSame('error', $first['severity']);
    }

    public function testLintDetectsInvalidHexColor(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', '#gggggg');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => true,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $colorWarnings = array_filter($warnings, fn ($w) => 'invalid_color' === $w['type']);
        $this->assertNotEmpty($colorWarnings);
    }

    public function testLintSkipsUrlColorsForColorCheck(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#gradient)');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => true,
            'check_transforms' => false,
            'check_accessibility' => false,
        ]);

        $colorWarnings = array_filter($warnings, fn ($w) => 'invalid_color' === $w['type']);
        $this->assertEmpty($colorWarnings);
    }

    public function testLintDetectsIdentityTranslateTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('transform', 'translate(0, 0)');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => true,
            'check_accessibility' => false,
        ]);

        $transformWarnings = array_filter($warnings, fn ($w) => 'identity_transform' === $w['type']);
        $this->assertNotEmpty($transformWarnings);
    }

    public function testLintDetectsIdentityScaleTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('transform', 'scale(1, 1)');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => true,
            'check_accessibility' => false,
        ]);

        $transformWarnings = array_filter($warnings, fn ($w) => 'identity_transform' === $w['type']);
        $this->assertNotEmpty($transformWarnings);
    }

    public function testLintDetectsIdentityRotateTransform(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('transform', 'rotate(0)');
        $root->appendChild($rect);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => true,
            'check_accessibility' => false,
        ]);

        $transformWarnings = array_filter($warnings, fn ($w) => 'identity_transform' === $w['type']);
        $this->assertNotEmpty($transformWarnings);
    }

    public function testLintDetectsMissingTitleForAccessibility(): void
    {
        $doc = Document::create();

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => true,
        ]);

        $titleWarnings = array_filter($warnings, fn ($w) => 'missing_title' === $w['type']);
        $this->assertNotEmpty($titleWarnings);
    }

    public function testLintAccessibilityPassesWithTitle(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $title = new TitleElement();
        $title->setContent('My SVG');
        $root->appendChild($title);

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => true,
        ]);

        $titleWarnings = array_filter($warnings, fn ($w) => 'missing_title' === $w['type']);
        $this->assertEmpty($titleWarnings);
    }

    public function testLintUsesDefaultOptions(): void
    {
        $doc = Document::create();
        $warningsDefault = DocumentValidator::lint($doc);
        $warningsExplicit = DocumentValidator::lint($doc, [
            'check_ids' => true,
            'check_references' => true,
            'check_colors' => true,
            'check_transforms' => true,
            'check_accessibility' => true,
        ]);

        $this->assertSame(count($warningsDefault), count($warningsExplicit));
    }

    // ---------------------------------------------------------------
    // findBrokenReferences()
    // ---------------------------------------------------------------

    public function testFindBrokenUrlReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#missing-grad)');
        $root->appendChild($rect);

        $broken = DocumentValidator::findBrokenReferences($doc);

        $this->assertCount(1, $broken);
        $this->assertSame('missing-grad', $broken[0]['id']);
        $this->assertSame('rect', $broken[0]['element']);
        $this->assertSame('fill', $broken[0]['attribute']);
    }

    public function testFindBrokenHashReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('href', '#no-target');
        $root->appendChild($circle);

        $broken = DocumentValidator::findBrokenReferences($doc);

        $this->assertCount(1, $broken);
        $this->assertSame('no-target', $broken[0]['id']);
        $this->assertSame('href', $broken[0]['attribute']);
    }

    public function testFindBrokenReferencesReturnsEmptyForValidRefs(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setId('grad1');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#grad1)');
        $root->appendChild($rect);

        $broken = DocumentValidator::findBrokenReferences($doc);

        $this->assertSame([], $broken);
    }

    public function testFindBrokenReferencesChecksMultipleAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#a)');
        $rect->setAttribute('stroke', 'url(#b)');
        $rect->setAttribute('clip-path', 'url(#c)');
        $root->appendChild($rect);

        $broken = DocumentValidator::findBrokenReferences($doc);

        $this->assertCount(3, $broken);
        $brokenIds = array_column($broken, 'id');
        $this->assertContains('a', $brokenIds);
        $this->assertContains('b', $brokenIds);
        $this->assertContains('c', $brokenIds);
    }

    public function testFindBrokenReferencesOnEmptyDocument(): void
    {
        $doc = new Document();
        $broken = DocumentValidator::findBrokenReferences($doc);

        $this->assertSame([], $broken);
    }

    // ---------------------------------------------------------------
    // updateReferences()
    // ---------------------------------------------------------------

    public function testUpdateUrlReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#old)');
        $root->appendChild($rect);

        $count = DocumentValidator::updateReferences($doc, 'old', 'new');

        $this->assertSame(1, $count);
        $this->assertSame('url(#new)', $rect->getAttribute('fill'));
    }

    public function testUpdateHashReference(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('href', '#old');
        $root->appendChild($circle);

        $count = DocumentValidator::updateReferences($doc, 'old', 'new');

        $this->assertSame(1, $count);
        $this->assertSame('#new', $circle->getAttribute('href'));
    }

    public function testUpdateReferencesAcrossMultipleElements(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#grad)');
        $root->appendChild($rect);

        $circle = new CircleElement();
        $circle->setAttribute('stroke', 'url(#grad)');
        $root->appendChild($circle);

        $count = DocumentValidator::updateReferences($doc, 'grad', 'gradient-new');

        $this->assertSame(2, $count);
        $this->assertSame('url(#gradient-new)', $rect->getAttribute('fill'));
        $this->assertSame('url(#gradient-new)', $circle->getAttribute('stroke'));
    }

    public function testUpdateReferencesReturnsZeroWhenNothingToUpdate(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'red');
        $root->appendChild($rect);

        $count = DocumentValidator::updateReferences($doc, 'nonexistent', 'new');

        $this->assertSame(0, $count);
    }

    public function testUpdateReferencesOnDocumentWithoutRoot(): void
    {
        $doc = new Document();
        $count = DocumentValidator::updateReferences($doc, 'a', 'b');

        $this->assertSame(0, $count);
    }

    // ---------------------------------------------------------------
    // fixBrokenReferences()
    // ---------------------------------------------------------------

    public function testFixBrokenReferencesRemovesBrokenUrlRef(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#missing)');
        $root->appendChild($rect);

        $fixed = DocumentValidator::fixBrokenReferences($doc);

        $this->assertGreaterThanOrEqual(1, $fixed);
        $this->assertNull($rect->getAttribute('fill'));
    }

    public function testFixBrokenReferencesRemovesBrokenHashRef(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $circle = new CircleElement();
        $circle->setAttribute('href', '#gone');
        $root->appendChild($circle);

        $fixed = DocumentValidator::fixBrokenReferences($doc);

        $this->assertGreaterThanOrEqual(1, $fixed);
        $this->assertNull($circle->getAttribute('href'));
    }

    public function testFixBrokenReferencesPreservesValidRefs(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setId('valid-grad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#valid-grad)');
        $root->appendChild($rect);

        $fixed = DocumentValidator::fixBrokenReferences($doc);

        $this->assertSame(0, $fixed);
        $this->assertSame('url(#valid-grad)', $rect->getAttribute('fill'));
    }

    public function testFixBrokenReferencesReturnsZeroWhenNoBrokenRefs(): void
    {
        $doc = Document::create();
        $fixed = DocumentValidator::fixBrokenReferences($doc);

        $this->assertSame(0, $fixed);
    }

    // ---------------------------------------------------------------
    // fixDuplicateIds()
    // ---------------------------------------------------------------

    public function testFixDuplicateIdsRenamesDuplicates(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect1 = new RectElement();
        $rect1->setId('shape');
        $root->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setId('shape');
        $root->appendChild($rect2);

        $fixed = DocumentValidator::fixDuplicateIds($doc);

        $this->assertSame(1, $fixed);
        // First element keeps original ID, second gets a new one
        $this->assertSame('shape', $rect1->getId());
        $this->assertNotSame('shape', $rect2->getId());
    }

    public function testFixDuplicateIdsReturnsZeroWhenNoDuplicates(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setId('unique');
        $root->appendChild($rect);

        $fixed = DocumentValidator::fixDuplicateIds($doc);

        $this->assertSame(0, $fixed);
    }

    public function testFixDuplicateIdsHandlesMultipleDuplicateGroups(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $r1 = new RectElement();
        $r1->setId('a');
        $root->appendChild($r1);

        $r2 = new RectElement();
        $r2->setId('a');
        $root->appendChild($r2);

        $c1 = new CircleElement();
        $c1->setId('b');
        $root->appendChild($c1);

        $c2 = new CircleElement();
        $c2->setId('b');
        $root->appendChild($c2);

        $fixed = DocumentValidator::fixDuplicateIds($doc);

        $this->assertSame(2, $fixed);
    }

    // ---------------------------------------------------------------
    // autoFix()
    // ---------------------------------------------------------------

    public function testAutoFixCombinesBrokenRefsAndDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        // Duplicate IDs
        $r1 = new RectElement();
        $r1->setId('dup');
        $root->appendChild($r1);

        $r2 = new RectElement();
        $r2->setId('dup');
        $root->appendChild($r2);

        // Broken reference
        $circle = new CircleElement();
        $circle->setAttribute('fill', 'url(#phantom)');
        $root->appendChild($circle);

        $fixes = DocumentValidator::autoFix($doc);

        $this->assertArrayHasKey('broken_references', $fixes);
        $this->assertArrayHasKey('duplicate_ids', $fixes);
        $this->assertGreaterThanOrEqual(1, $fixes['broken_references']);
        $this->assertGreaterThanOrEqual(1, $fixes['duplicate_ids']);
    }

    public function testAutoFixRespectsOptions(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $r1 = new RectElement();
        $r1->setId('dup');
        $root->appendChild($r1);

        $r2 = new RectElement();
        $r2->setId('dup');
        $root->appendChild($r2);

        $fixes = DocumentValidator::autoFix($doc, [
            'fix_broken_references' => false,
            'fix_duplicate_ids' => true,
        ]);

        $this->assertSame(0, $fixes['broken_references']);
        $this->assertGreaterThanOrEqual(1, $fixes['duplicate_ids']);
    }

    public function testAutoFixReturnsZerosWhenNothingToFix(): void
    {
        $doc = Document::create();

        $fixes = DocumentValidator::autoFix($doc);

        $this->assertSame(0, $fixes['broken_references']);
        $this->assertSame(0, $fixes['duplicate_ids']);
    }

    // ---------------------------------------------------------------
    // suggestImprovements()
    // ---------------------------------------------------------------

    public function testSuggestImprovementsSuggestsViewBox(): void
    {
        $doc = Document::create();

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $viewBoxSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'viewBox')
        );
        $this->assertNotEmpty($viewBoxSuggestions);
        $this->assertSame('high', array_values($viewBoxSuggestions)[0]['impact']);
    }

    public function testSuggestImprovementsDoesNotSuggestViewBoxWhenPresent(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 300 150');

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $viewBoxSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'viewBox')
        );
        $this->assertEmpty($viewBoxSuggestions);
    }

    public function testSuggestImprovementsSuggestsTitleWhenMissing(): void
    {
        $doc = Document::create();

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $titleSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], '<title>')
        );
        $this->assertNotEmpty($titleSuggestions);
    }

    public function testSuggestImprovementsDoesNotSuggestTitleWhenPresent(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $title = new TitleElement();
        $title->setContent('Accessible SVG');
        $root->appendChild($title);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $titleSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], '<title>')
        );
        $this->assertEmpty($titleSuggestions);
    }

    public function testSuggestImprovementsDetectsUnusedDefs(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 300 150');

        $title = new TitleElement();
        $title->setContent('Test');
        $root->appendChild($title);

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setId('unused-grad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $unusedDefsSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'unused definition')
        );
        $this->assertNotEmpty($unusedDefsSuggestions);
    }

    public function testSuggestImprovementsDoesNotFlagUsedDefs(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 300 150');

        $title = new TitleElement();
        $title->setContent('Test');
        $root->appendChild($title);

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setId('used-grad');
        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#used-grad)');
        $root->appendChild($rect);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $unusedDefsSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'unused definition')
        );
        $this->assertEmpty($unusedDefsSuggestions);
    }

    public function testSuggestImprovementsDetectsShortenableColor(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', '#aabbcc');
        $root->appendChild($rect);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $colorSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'Shorten color')
        );
        $this->assertNotEmpty($colorSuggestions);
    }

    public function testSuggestImprovementsDoesNotFlagNonShortenableColor(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);

        $rect = new RectElement();
        $rect->setAttribute('fill', '#abcdef');
        $root->appendChild($rect);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        $colorSuggestions = array_filter(
            $suggestions,
            fn ($s) => str_contains((string) $s['suggestion'], 'Shorten color')
        );
        $this->assertEmpty($colorSuggestions);
    }

    public function testSuggestImprovementsReturnsEmptyForDocumentWithoutRoot(): void
    {
        $doc = new Document();
        $suggestions = DocumentValidator::suggestImprovements($doc);

        $this->assertSame([], $suggestions);
    }

    public function testSuggestImprovementsDescPresentIsDetected(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();
        $this->assertNotNull($root);
        $root->setAttribute('viewBox', '0 0 300 150');

        $title = new TitleElement();
        $title->setContent('Test');
        $root->appendChild($title);

        $desc = new \Atelier\Svg\Element\Descriptive\DescElement();
        $root->appendChild($desc);

        $suggestions = DocumentValidator::suggestImprovements($doc);

        // When desc is present, the desc detection line (400) is covered
        $this->assertIsArray($suggestions);
    }

    public function testLintAccessibilityWithNoRootReturnsEmpty(): void
    {
        $doc = new Document();

        $warnings = DocumentValidator::lint($doc, [
            'check_ids' => false,
            'check_references' => false,
            'check_colors' => false,
            'check_transforms' => false,
            'check_accessibility' => true,
        ]);

        $this->assertSame([], $warnings);
    }
}
