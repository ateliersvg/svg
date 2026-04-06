<?php

declare(strict_types=1);

namespace Atelier\Svg\Tests\Exception;

use Atelier\Svg\Exception\ParseException;
use Atelier\Svg\Exception\RuntimeException;
use Atelier\Svg\Exception\SvgExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParseException::class)]
final class ParseExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new ParseException('Test message');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testImplementsSvgExceptionInterface(): void
    {
        $exception = new ParseException('Test message');

        $this->assertInstanceOf(SvgExceptionInterface::class, $exception);
    }

    public function testMessage(): void
    {
        $message = 'Failed to parse SVG';
        $exception = new ParseException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testCode(): void
    {
        $exception = new ParseException('Test', 42);

        $this->assertSame(42, $exception->getCode());
    }
}
