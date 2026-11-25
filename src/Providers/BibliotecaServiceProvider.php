<?php

namespace iEducar\Packages\BuritiBiblioteca\Providers;

use iEducar\Packages\BuritiBiblioteca\Console\BibliotecaDemoCommand;
use iEducar\Packages\BuritiBiblioteca\Support\Identidade;
use iEducar\Packages\BuritiBiblioteca\Support\MenuFluxo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class BibliotecaServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-biblioteca-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/biblioteca.php', 'biblioteca');
    }

    public function boot(): void
    {
        if (class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::class)) {
            \iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::registrar();
        }
        $root = dirname(__DIR__, 2);
        $this->loadViewsFrom($root.'/resources/views', 'biblioteca');

        if ($this->app->runningInConsole()) {
            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
            $this->commands([BibliotecaDemoCommand::class]);
        }

        Route::middleware([
            'web',
            'ieducar.navigation',
            'ieducar.footer',
            'ieducar.xssbypass',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ])->group($root.'/routes/web.php');

        View::composer('biblioteca::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
            $view->with('menuGrupos', MenuFluxo::grupos([
                'painel' => true,
                'acervo' => true,
                'emprestimos' => true,
                'config' => true,
            ]));
        });
    }
}
