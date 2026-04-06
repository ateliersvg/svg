<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Parser\Attribute;

use Atelier\Svg\Parser\Attribute\AttributeSpec;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeSpec::class)]
final class AttributeSpecTest extends TestCase
{
    public function testValidateEnumRejectNonScalarValue(): void
    {
        $spec = AttributeSpec::enum('display', ['inline', 'block', 'none']);

        $this->assertFalse($spec->validate(['inline']));
    }
}
