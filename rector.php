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
    )
    // uncomment to reach your current PHP version
    ->withComposerBased(laravel: true)
    ->withFluentCallNewLine()
    ->withTreatClassesAsFinal()
    ->withPhpSets(php84: true)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
