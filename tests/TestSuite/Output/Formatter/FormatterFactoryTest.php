<?php

declare(strict_types=1);

namespace Tests\TestSuite\Output\Formatter;

use CssLint\Output\Formatter\FormatterFactory;
use CssLint\Output\Formatter\FormatterManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FormatterFactoryTest extends TestCase
{
    public function testCreateWithoutArgumentReturnsDefaultManager(): void
    {
        $factory = new FormatterFactory();
        $manager = $factory->create(null);
        $this->assertInstanceOf(FormatterManager::class, $manager);
    }

    public function testCreateWithInvalidNameThrowsException(): void
    {
        $factory = new FormatterFactory();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid formatter: invalid');
        $factory->create(['invalid' => null]);
    }
}
