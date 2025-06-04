<?php

declare(strict_types=1);

namespace Tests\TestSuite;

use CssLint\LintError;
use CssLint\TokenLinter\TokenError;
use Generator;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Assert that the errors are equal to the expected errors.
     * @param array<array<string, mixed>> $expected
     * @param array<TokenError> $actual
     */
    protected function assertErrorsEquals(array $expected, Generator $actual)
    {

        $actual = iterator_to_array($actual, false);
        $this->assertCount(count($expected), $actual, json_encode($actual, JSON_PRETTY_PRINT));
        foreach ($actual as $key => $error) {
            $this->assertInstanceOf(LintError::class, $error, "Error at index $key is not a LintError");
            $this->assertEquals(
                $expected[$key],
                $error->jsonSerialize(),
                "Error at index $key does not match expected value."
            );
        }
    }
}
