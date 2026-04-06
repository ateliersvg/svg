<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Parser\Attribute;

use Atelier\Svg\Parser\Attribute\AttributeRegistry;
use Atelier\Svg\Parser\Attribute\AttributeSpec;
use Atelier\Svg\Parser\Attribute\AttributeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeRegistry::class)]
#[CoversClass(AttributeSpec::class)]
#[CoversClass(AttributeType::class)]
final class AttributeRegistryTest extends TestCase
{
    public function testGetReturnsSpecForKnownAttribute(): void
    {
        $spec = AttributeRegistry::get('fill');

        $this->assertNotNull($spec);
        $this->assertInstanceOf(AttributeSpec::class, $spec);
        $this->assertSame('fill', $spec->getName());
        $this->assertSame(AttributeType::PAINT, $spec->getType());
    }

    public function testGetReturnsNullForUnknownAttribute(): void
    {
        $spec = AttributeRegistry::get('nonexistent-attribute');

        $this->assertNull($spec);
    }

    public function testIsKnown(): void
    {
        $this->assertTrue(AttributeRegistry::isKnown('fill'));
        $this->assertTrue(AttributeRegistry::isKnown('stroke'));
        $this->assertTrue(AttributeRegistry::isKnown('viewBox'));
        $this->assertFalse(AttributeRegistry::isKnown('nonexistent'));
    }

    public function testGetType(): void
    {
        $this->assertSame(AttributeType::PAINT, AttributeRegistry::getType('fill'));
        $this->assertSame(AttributeType::TRANSFORM, AttributeRegistry::getType('transform'));
        $this->assertSame(AttributeType::PATH_DATA, AttributeRegistry::getType('d'));
        $this->assertSame(AttributeType::UNKNOWN, AttributeRegistry::getType('nonexistent'));
    }

    public function testIsDeprecated(): void
    {
        $this->assertTrue(AttributeRegistry::isDeprecated('xlink:href'));
        $this->assertFalse(AttributeRegistry::isDeprecated('href'));
        $this->assertFalse(AttributeRegistry::isDeprecated('fill'));
    }

    public function testGetAll(): void
    {
        $all = AttributeRegistry::getAll();

        $this->assertIsArray($all);
        $this->assertNotEmpty($all);
        $this->assertArrayHasKey('fill', $all);
        $this->assertArrayHasKey('stroke', $all);
        $this->assertArrayHasKey('d', $all);
    }

    public function testRegisterCustomAttribute(): void
    {
        $customSpec = AttributeSpec::string('data-custom');
        AttributeRegistry::register($customSpec);

        $this->assertTrue(AttributeRegistry::isKnown('data-custom'));
        $this->assertSame(AttributeType::STRING, AttributeRegistry::getType('data-custom'));
    }

    public function testAttributeSpecCreation(): void
    {
        $spec = new AttributeSpec(
            name: 'test',
            type: AttributeType::LENGTH,
            required: true,
            defaultValue: '10px',
            deprecated: true,
            deprecatedMessage: 'Use something else',
            namespace: 'http://example.com/ns',
        );

        $this->assertSame('test', $spec->getName());
        $this->assertSame(AttributeType::LENGTH, $spec->getType());
        $this->assertTrue($spec->isRequired());
        $this->assertSame('10px', $spec->getDefaultValue());
        $this->assertTrue($spec->isDeprecated());
        $this->assertSame('Use something else', $spec->getDeprecatedMessage());
        $this->assertSame('http://example.com/ns', $spec->getNamespace());
        $this->assertTrue($spec->isNamespaced());
    }

    public function testAttributeSpecFactoryMethods(): void
    {
        $string = AttributeSpec::string('id');
        $this->assertSame(AttributeType::STRING, $string->getType());

        $length = AttributeSpec::length('width', required: true, default: '100');
        $this->assertSame(AttributeType::LENGTH, $length->getType());
        $this->assertTrue($length->isRequired());
        $this->assertSame('100', $length->getDefaultValue());

        $number = AttributeSpec::number('opacity', default: 1.0);
        $this->assertSame(AttributeType::NUMBER, $number->getType());
        $this->assertSame(1.0, $number->getDefaultValue());

        $color = AttributeSpec::color('fill', default: 'black');
        $this->assertSame(AttributeType::COLOR, $color->getType());

        $paint = AttributeSpec::paint('stroke');
        $this->assertSame(AttributeType::PAINT, $paint->getType());

        $transform = AttributeSpec::transform('transform');
        $this->assertSame(AttributeType::TRANSFORM, $transform->getType());

        $iri = AttributeSpec::iri('href');
        $this->assertSame(AttributeType::IRI, $iri->getType());
    }

    public function testEnumAttributeSpec(): void
    {
        $spec = AttributeSpec::enum('visibility', ['visible', 'hidden', 'collapse'], default: 'visible');

        $this->assertSame(AttributeType::ENUM, $spec->getType());
        $this->assertSame(['visible', 'hidden', 'collapse'], $spec->getEnumValues());
        $this->assertSame('visible', $spec->getDefaultValue());

        $this->assertTrue($spec->validate('visible'));
        $this->assertTrue($spec->validate('hidden'));
        $this->assertFalse($spec->validate('invalid'));
    }

    public function testDeprecatedAttributeSpec(): void
    {
        $spec = AttributeSpec::deprecated('xlink:href', AttributeType::IRI, 'Use href instead');

        $this->assertTrue($spec->isDeprecated());
        $this->assertSame('Use href instead', $spec->getDeprecatedMessage());
    }

    public function testNamespacedAttributeSpec(): void
    {
        $spec = AttributeSpec::namespaced('xml:lang', 'http://www.w3.org/XML/1998/namespace', AttributeType::STRING);

        $this->assertTrue($spec->isNamespaced());
        $this->assertSame('http://www.w3.org/XML/1998/namespace', $spec->getNamespace());
    }

    public function testValidationForRequiredAttribute(): void
    {
        $required = AttributeSpec::string('id', required: true);
        $optional = AttributeSpec::string('class', required: false);

        $this->assertFalse($required->validate(null));
        $this->assertFalse($required->validate(''));
        $this->assertTrue($required->validate('myId'));

        $this->assertTrue($optional->validate(null));
        $this->assertTrue($optional->validate(''));
        $this->assertTrue($optional->validate('myClass'));
    }

    public function testKnownAttributeTypes(): void
    {
        // Verify specific well-known attributes have correct types
        $expectations = [
            'id' => AttributeType::STRING,
            'class' => AttributeType::STRING,
            'fill' => AttributeType::PAINT,
            'stroke' => AttributeType::PAINT,
            'fill-opacity' => AttributeType::NUMBER,
            'stroke-width' => AttributeType::LENGTH,
            'transform' => AttributeType::TRANSFORM,
            'd' => AttributeType::PATH_DATA,
            'points' => AttributeType::POINTS,
            'viewBox' => AttributeType::VIEWBOX,
            'preserveAspectRatio' => AttributeType::PRESERVE_ASPECT_RATIO,
            'href' => AttributeType::IRI,
            'filter' => AttributeType::IRI,
            'visibility' => AttributeType::ENUM,
        ];

        foreach ($expectations as $attr => $expectedType) {
            $this->assertSame(
                $expectedType,
                AttributeRegistry::getType($attr),
                "Attribute '$attr' should have type '{$expectedType->value}'"
            );
        }
    }

    public function testDefaultValues(): void
    {
        $fill = AttributeRegistry::get('fill');
        $this->assertSame('black', $fill->getDefaultValue());

        $stroke = AttributeRegistry::get('stroke');
        $this->assertSame('none', $stroke->getDefaultValue());

        $opacity = AttributeRegistry::get('opacity');
        $this->assertSame(1.0, $opacity->getDefaultValue());
    }

    public function testAttributeSpecIsNotNamespaced(): void
    {
        $spec = AttributeSpec::string('id');

        $this->assertFalse($spec->isNamespaced());
        $this->assertNull($spec->getNamespace());
    }

    public function testAttributeSpecValidateNonEnumType(): void
    {
        $spec = AttributeSpec::string('class');

        // Non-enum types should return true for any non-empty value
        $this->assertTrue($spec->validate('my-class'));
        $this->assertTrue($spec->validate('123'));
    }

    public function testAttributeSpecGetEnumValuesForNonEnumType(): void
    {
        $spec = AttributeSpec::string('id');

        $this->assertNull($spec->getEnumValues());
    }

    public function testAttributeSpecNotDeprecated(): void
    {
        $spec = AttributeSpec::string('id');

        $this->assertFalse($spec->isDeprecated());
        $this->assertNull($spec->getDeprecatedMessage());
    }

    public function testAttributeSpecNotRequired(): void
    {
        $spec = AttributeSpec::string('class');

        $this->assertFalse($spec->isRequired());
    }

    public function testAttributeTypeValues(): void
    {
        // Test that all attribute types have correct string values
        $this->assertSame('string', AttributeType::STRING->value);
        $this->assertSame('length', AttributeType::LENGTH->value);
        $this->assertSame('number', AttributeType::NUMBER->value);
        $this->assertSame('color', AttributeType::COLOR->value);
        $this->assertSame('paint', AttributeType::PAINT->value);
        $this->assertSame('transform', AttributeType::TRANSFORM->value);
        $this->assertSame('path_data', AttributeType::PATH_DATA->value);
        $this->assertSame('points', AttributeType::POINTS->value);
        $this->assertSame('iri', AttributeType::IRI->value);
        $this->assertSame('viewbox', AttributeType::VIEWBOX->value);
        $this->assertSame('enum', AttributeType::ENUM->value);
        $this->assertSame('unknown', AttributeType::UNKNOWN->value);
    }

    public function testIsDeprecatedForUnknownAttribute(): void
    {
        $this->assertFalse(AttributeRegistry::isDeprecated('nonexistent-attribute'));
    }

    public function testRegistryWithLengthOrPercentageType(): void
    {
        $spec = AttributeRegistry::get('width');

        $this->assertNotNull($spec);
        $this->assertSame(AttributeType::LENGTH_OR_PERCENTAGE, $spec->getType());
    }

    public function testRegistryWithViewboxType(): void
    {
        $spec = AttributeRegistry::get('viewBox');

        $this->assertNotNull($spec);
        $this->assertSame(AttributeType::VIEWBOX, $spec->getType());
    }

    public function testRegistryWithPreserveAspectRatioType(): void
    {
        $spec = AttributeRegistry::get('preserveAspectRatio');

        $this->assertNotNull($spec);
        $this->assertSame(AttributeType::PRESERVE_ASPECT_RATIO, $spec->getType());
    }
}
