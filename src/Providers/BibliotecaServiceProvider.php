<?php

namespace iEducar\Packages\BuritiBiblioteca\Providers;

use Illuminate\Support\ServiceProvider;

class BibliotecaServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-biblioteca-package';

    public function register(): void
    {
        // Services são value objects — instanciar com `new` nos callers (não bind no container).
    }

    public function boot(): void
    {
    }
}
