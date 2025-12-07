<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$root = __DIR__;

$finder = new Finder()
    ->in([
        $root . '/src',
        $root . '/tests',
    ])
    ->name('*.php')
    ->notName('*Stub.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return new Config()
    ->setCacheFile($root . '/.php-cs-fixer.cache')
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PER-CS2.0' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
    ])
    ->setFinder($finder);
