<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/lang',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPreparedSets(
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        deadCode: true
    )
    // uncomment to reach your current PHP version
    ->withComposerBased(laravel: true)
    ->withFluentCallNewLine()
    ->withTreatClassesAsFinal()
    ->withPhpSets(php84: true)
    ->withCodeQualityLevel(0);
