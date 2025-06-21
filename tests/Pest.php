<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');
pest()->extend(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Performance Testing Helpers
 */
function measureExecutionTime(callable $callback): float
{
    $start = microtime(true);
    $callback();
    return microtime(true) - $start;
}

function expectExecutionTimeUnder(callable $callback, float $maxSeconds): void
{
    $time = measureExecutionTime($callback);
    expect($time)->toBeLessThan($maxSeconds, "Execution took {$time}s, expected under {$maxSeconds}s");
}

/**
 * File System Helpers
 */
function createTempDirectory(): string
{
    $tempDir = sys_get_temp_dir() . '/torrentpier_test_' . uniqid();
    mkdir($tempDir, 0755, true);
    return $tempDir;
}

function removeTempDirectory(string $dir): void
{
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? removeTempDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

/**
 * Exception Testing Helpers
 */
function expectException(callable $callback, string $exceptionClass, ?string $message = null): void
{
    try {
        $callback();
        fail("Expected exception $exceptionClass was not thrown");
    } catch (Exception $e) {
        expect($e)->toBeInstanceOf($exceptionClass);
        if ($message) {
            expect($e->getMessage())->toContain($message);
        }
    }
}
