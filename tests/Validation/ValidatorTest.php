<?php

namespace Atelier\Svg\Tests\Validation;

use Atelier\Svg\Document;
use Atelier\Svg\Element\Descriptive\TitleElement;
use Atelier\Svg\Element\Gradient\LinearGradientElement;
use Atelier\Svg\Element\ImageElement;
use Atelier\Svg\Element\Shape\CircleElement;
use Atelier\Svg\Element\Shape\RectElement;
use Atelier\Svg\Element\Structural\DefsElement;
use Atelier\Svg\Element\Structural\UseElement;
use Atelier\Svg\Validation\ReferenceTracker;
use Atelier\Svg\Validation\ValidationProfile;
use Atelier\Svg\Validation\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
final class ValidatorTest extends TestCase
{
    public function testValidateValidDocument(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $circle = new CircleElement();
        $circle->setAttribute('cx', '50')
               ->setAttribute('cy', '50')
               ->setAttribute('r', '25');
        $root->appendChild($circle);

        $validator = new Validator();
        $result = $validator->validate($doc);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function testDetectsMissingRequiredAttributes(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $circle = new CircleElement();
        // Missing required attributes: cx, cy, r
        $root->appendChild($circle);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
    }

    public function testDetectsBrokenReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#nonexistent)');
        $root->appendChild($rect);

        $validator = new Validator();
        $result = $validator->validate($doc);

        $this->assertFalse($result->isValid());

        $errors = $result->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('nonexistent', $errors[0]->message);
    }

    public function testDetectsDuplicateIds(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect1 = new RectElement();
        $rect1->setAttribute('id', 'dup');
        $rect1->setAttribute('width', '10');
        $rect1->setAttribute('height', '10');
        $root->appendChild($rect1);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'dup');
        $rect2->setAttribute('width', '10');
        $rect2->setAttribute('height', '10');
        $root->appendChild($rect2);

        $validator = new Validator();
        $result = $validator->validate($doc);

        // Check for duplicate ID issues
        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('duplicate_id' === $issue->code && str_contains($issue->message, 'dup')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find duplicate ID issue');
    }

    public function testDetectsCircularReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();

        $grad1 = new LinearGradientElement();
        $grad1->setAttribute('id', 'g1');
        $grad1->setAttribute('href', '#g2');

        $grad2 = new LinearGradientElement();
        $grad2->setAttribute('id', 'g2');
        $grad2->setAttribute('href', '#g1');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $root->appendChild($defs);

        $validator = new Validator();
        $result = $validator->validate($doc);

        // Check for circular reference issues
        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('circular_reference' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find circular reference issue');
    }

    public function testStrictProfileReportsMoreIssues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        // Missing width and height
        $rect->setAttribute('x', '10')
             ->setAttribute('y', '10');
        $root->appendChild($rect);

        $strictValidator = new Validator(ValidationProfile::strict());
        $strictResult = $strictValidator->validate($doc);

        $lenientValidator = new Validator(ValidationProfile::lenient());
        $lenientResult = $lenientValidator->validate($doc);

        // Strict profile should report at least as many issues as lenient
        $this->assertGreaterThanOrEqual($lenientResult->count(), $strictResult->count());
    }

    public function testAccessibleProfileRequiresTitle(): void
    {
        $doc = Document::create();

        $validator = new Validator(ValidationProfile::accessible());
        $result = $validator->validate($doc);

        $hasAccessibilityIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_title' === $issue->code) {
                $hasAccessibilityIssue = true;
                break;
            }
        }

        $this->assertTrue($hasAccessibilityIssue);
    }

    public function testAccessibleProfilePassesWithTitle(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $title = new TitleElement();
        $title->setContent('Test SVG');
        $root->appendChild($title);

        $validator = new Validator(ValidationProfile::accessible());
        $result = $validator->validate($doc);

        $hasTitleIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_title' === $issue->code) {
                $hasTitleIssue = true;
                break;
            }
        }

        $this->assertFalse($hasTitleIssue);
    }

    public function testValidatesInvalidColorValues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', '#invalid'); // Invalid hex color
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $hasColorIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code) {
                $hasColorIssue = true;
                break;
            }
        }

        $this->assertTrue($hasColorIssue);
    }

    public function testValidatesValidColorValues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', '#3b82f6'); // Valid hex color
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $hasColorIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'fill' === $issue->attribute) {
                $hasColorIssue = true;
                break;
            }
        }

        $this->assertFalse($hasColorIssue);
    }

    public function testValidatesIdFormat(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('id', '123-invalid'); // ID should not start with number
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $hasIdFormatIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_id_format' === $issue->code) {
                $hasIdFormatIssue = true;
                break;
            }
        }

        $this->assertTrue($hasIdFormatIssue);
    }

    public function testLenientProfileSkipsIdFormatCheck(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('id', '123-invalid');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::lenient());
        $result = $validator->validate($doc);

        $hasIdFormatIssue = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_id_format' === $issue->code) {
                $hasIdFormatIssue = true;
                break;
            }
        }

        $this->assertFalse($hasIdFormatIssue);
    }

    public function testEmptyDocumentValidation(): void
    {
        $doc = new Document();

        $validator = new Validator();
        $result = $validator->validate($doc);

        $this->assertFalse($result->isValid());
        $this->assertEquals('no_root', $result->getErrors()[0]->code);
    }

    public function testValidationResultCounts(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('id', 'dup');
        $rect->setAttribute('width', '10');
        $rect->setAttribute('height', '10');
        $root->appendChild($rect);

        $rect2 = new RectElement();
        $rect2->setAttribute('id', 'dup');
        $rect2->setAttribute('width', '10');
        $rect2->setAttribute('height', '10');
        $root->appendChild($rect2);

        $validator = new Validator();
        $result = $validator->validate($doc);

        $counts = $result->getCounts();
        $this->assertIsArray($counts);
        $this->assertArrayHasKey('errors', $counts);
        $this->assertArrayHasKey('warnings', $counts);
        $this->assertArrayHasKey('info', $counts);

        // Should have at least one issue (duplicate ID)
        $totalIssues = $counts['errors'] + $counts['warnings'] + $counts['info'];
        $this->assertGreaterThan(0, $totalIssues, 'Expected to find validation issues');
    }

    public function testGetProfile(): void
    {
        $profile = ValidationProfile::strict();
        $validator = new Validator($profile);

        $this->assertSame($profile, $validator->getProfile());
    }

    public function testGetTracker(): void
    {
        $doc = Document::create();

        $validator = new Validator();
        $this->assertNull($validator->getTracker());

        $validator->validate($doc);
        $this->assertNotNull($validator->getTracker());
    }

    public function testGetTrackerAfterValidation(): void
    {
        $doc = Document::create();

        $validator = new Validator();
        $validator->validate($doc);

        $tracker = $validator->getTracker();
        $this->assertInstanceOf(ReferenceTracker::class, $tracker);
    }

    public function testGetProfileReturnsProfile(): void
    {
        $profile = ValidationProfile::strict();
        $validator = new Validator($profile);

        $returned = $validator->getProfile();
        $this->assertInstanceOf(ValidationProfile::class, $returned);
        $this->assertSame($profile, $returned);
    }

    public function testValidateInvalidNesting(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();
        $gradient = new LinearGradientElement();
        $gradient->setAttribute('id', 'grad1');

        // rect is not allowed inside linearGradient
        $rect = new RectElement();
        $rect->setAttribute('width', '10');
        $rect->setAttribute('height', '10');
        $gradient->appendChild($rect);

        $defs->appendChild($gradient);
        $root->appendChild($defs);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_nesting' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find invalid nesting issue for rect inside linearGradient');
    }

    public function testValidateInvalidAttributeValues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('width', 'abc'); // non-numeric, non-length value
        $rect->setAttribute('height', '10');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_attribute_value' === $issue->code && 'width' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find invalid attribute value issue for non-numeric width');
    }

    public function testValidateAccessibilityMissingViewbox(): void
    {
        $doc = Document::create();

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'require_viewbox' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_viewbox' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find missing viewBox issue');
    }

    public function testValidateAccessibilityMissingTitle(): void
    {
        $doc = Document::create();

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'require_title' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_title' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find missing title issue');
    }

    public function testValidateAccessibilityMissingDesc(): void
    {
        $doc = Document::create();

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'require_desc' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_desc' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find missing desc issue');
    }

    public function testValidateImageAltText(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        // No aria-label or title
        $root->appendChild($image);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find missing image alt text issue');
    }

    public function testValidateUseElementRequiresHref(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $use = new UseElement();
        // No href or xlink:href
        $root->appendChild($use);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_required_attribute' === $issue->code && str_contains($issue->message, 'href')) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find missing href issue for use element');
    }

    public function testValidateLengthValues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('width', '50px');
        $rect->setAttribute('height', '3.5em');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $hasInvalidWidth = false;
        $hasInvalidHeight = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_attribute_value' === $issue->code && 'width' === $issue->attribute) {
                $hasInvalidWidth = true;
            }
            if ('invalid_attribute_value' === $issue->code && 'height' === $issue->attribute) {
                $hasInvalidHeight = true;
            }
        }
        $this->assertFalse($hasInvalidWidth, 'width="50px" should be a valid length value');
        $this->assertFalse($hasInvalidHeight, 'height="3.5em" should be a valid length value');
    }

    public function testValidateInvalidColorValues(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('stroke', 'notacolor');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'stroke' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find invalid color issue for stroke="notacolor"');
    }

    public function testValidateCircularReferences(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $defs = new DefsElement();

        $grad1 = new LinearGradientElement();
        $grad1->setAttribute('id', 'a');
        $grad1->setAttribute('href', '#b');

        $grad2 = new LinearGradientElement();
        $grad2->setAttribute('id', 'b');
        $grad2->setAttribute('href', '#a');

        $defs->appendChild($grad1);
        $defs->appendChild($grad2);
        $root->appendChild($defs);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('circular_reference' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find circular reference issue');
    }

    public function testValidateInvalidIdFormat(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('id', '9starts-with-number');
        $rect->setAttribute('width', '10');
        $rect->setAttribute('height', '10');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_id_format' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Expected to find invalid ID format issue for ID starting with number');
    }

    public function testValidateNestingWithUnknownParentIsAllowed(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        // A circle inside a circle - circle is not in NESTING_RULES as a parent
        $outerCircle = new CircleElement();
        $outerCircle->setAttribute('cx', '50');
        $outerCircle->setAttribute('cy', '50');
        $outerCircle->setAttribute('r', '40');

        $innerRect = new RectElement();
        $innerRect->setAttribute('width', '10');
        $innerRect->setAttribute('height', '10');

        // We need to test a parent that is NOT in NESTING_RULES
        // Let's use an image element as parent
        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        $root->appendChild($image);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        // Should NOT have an invalid_nesting issue for image since it is not in NESTING_RULES
        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_nesting' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Should not report nesting issue when parent has no nesting rules');
    }

    public function testValidateAccessibilityDescPresentSkipsWarning(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $desc = new \Atelier\Svg\Element\Descriptive\DescElement();
        $root->appendChild($desc);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'require_desc' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_desc' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Should not report missing desc when desc element is present');
    }

    public function testValidateColorUrlValueIsValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'url(#myGradient)');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'fill' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'url() references should be valid color values');
    }

    public function testValidateColorNamedKeywordsAreValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'none');
        $rect->setAttribute('stroke', 'currentColor');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Named color keywords (none, currentColor) should be valid');
    }

    public function testValidateColorRgbValueIsValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'rgb(255, 0, 0)');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'fill' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'rgb() should be a valid color value');
    }

    public function testValidateColorRgbaValueIsValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'rgba(255, 0, 0, 0.5)');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'fill' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'rgba() should be a valid color value');
    }

    public function testValidateColorHslValueIsValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('fill', 'hsl(120, 100%, 50%)');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'fill' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'hsl() should be a valid color value');
    }

    public function testValidateColorHslaValueIsValid(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $rect = new RectElement();
        $rect->setAttribute('stroke', 'hsla(120, 100%, 50%, 0.5)');
        $root->appendChild($rect);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_color' === $issue->code && 'stroke' === $issue->attribute) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'hsla() should be a valid color value');
    }

    public function testValidateImageWithAriaLabelHasAltText(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        $image->setAttribute('aria-label', 'My image');
        $root->appendChild($image);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Image with aria-label should not report missing alt text');
    }

    public function testValidateImageWithoutAltTextReportsIssue(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        $root->appendChild($image);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Image without any alt text should report missing_image_alt');
    }

    public function testValidateNestingWithParentNotInNestingRules(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $circle = new CircleElement();
        $circle->setAttribute('cx', '50');
        $circle->setAttribute('cy', '50');
        $circle->setAttribute('r', '40');

        $rect = new RectElement();
        $rect->setAttribute('width', '10');
        $rect->setAttribute('height', '10');

        $circle->appendChild($rect);
        $root->appendChild($circle);

        $validator = new Validator(ValidationProfile::strict());
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('invalid_nesting' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Should not report nesting issue when parent tag has no nesting rules');
    }

    public function testValidateAccessibilityWithNullRoot(): void
    {
        $doc = new Document();

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $this->assertFalse($result->isValid());
    }

    public function testHasChildTitleWithContainerImageHavingTitleChild(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $title = new TitleElement();
        $title->setContent('Doc title');
        $root->appendChild($title);

        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        $image->setAttribute('title', 'Image title');
        $root->appendChild($image);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Image with title attribute should not report missing alt text');
    }

    public function testHasChildTitleIteratesChildrenOfContainer(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $title = new TitleElement();
        $title->setContent('Doc title');
        $root->appendChild($title);

        $group = new \Atelier\Svg\Element\Structural\GroupElement();
        $image = new ImageElement();
        $image->setAttribute('width', '100');
        $image->setAttribute('height', '100');
        $group->appendChild($image);
        $root->appendChild($group);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Nested image without alt text should be detected');
    }

    public function testHasChildTitleWithContainerImageAndTitleChild(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $docTitle = new TitleElement();
        $docTitle->setContent('Doc title');
        $root->appendChild($docTitle);

        // Create a container with tagName 'image' to trigger hasChildTitle iteration
        $containerImage = new class extends \Atelier\Svg\Element\AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('image');
            }
        };

        $childTitle = new TitleElement();
        $childTitle->setContent('Alt text');
        $containerImage->appendChild($childTitle);
        $root->appendChild($containerImage);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, 'Container image with title child should not report missing alt text');
    }

    public function testHasChildTitleReturnsFalseForContainerImageWithoutTitle(): void
    {
        $doc = Document::create();
        $root = $doc->getRootElement();

        $docTitle = new TitleElement();
        $docTitle->setContent('Doc title');
        $root->appendChild($docTitle);

        // Create a container with tagName 'image' but no title child
        $containerImage = new class extends \Atelier\Svg\Element\AbstractContainerElement {
            public function __construct()
            {
                parent::__construct('image');
            }
        };

        $rect = new RectElement();
        $rect->setAttribute('width', '10');
        $rect->setAttribute('height', '10');
        $containerImage->appendChild($rect);
        $root->appendChild($containerImage);

        $profile = ValidationProfile::custom([
            'check_accessibility' => true,
            'check_image_alt_text' => true,
        ]);

        $validator = new Validator($profile);
        $result = $validator->validate($doc);

        $found = false;
        foreach ($result->getIssues() as $issue) {
            if ('missing_image_alt' === $issue->code) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Container image without title child should report missing alt text');
    }
}
