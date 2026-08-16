<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        Model::preventAccessingMissingAttributes();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Blade::directive('icon', fn ($expression): string => "<i class=\"fas fa-fw fa-{{ $expression }}\"></i>");
        Paginator::useBootstrap();
    }
}
