<?php

namespace iEducar\Packages\BuritiBiblioteca\Providers;

use iEducar\Packages\BuritiBiblioteca\Services\Emprestimo;
use iEducar\Packages\BuritiBiblioteca\Services\ConsultaAcervo;
use Illuminate\Support\ServiceProvider;

class BibliotecaServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-biblioteca-package';

    public function register(): void
    {
        $this->app->singleton(Emprestimo::class);
        $this->app->singleton(ConsultaAcervo::class);
    }

    public function boot(): void
    {
    }
}
