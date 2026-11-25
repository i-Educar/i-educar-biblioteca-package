<?php

namespace iEducar\Packages\BuritiBiblioteca\Console;

use iEducar\Packages\BuritiBiblioteca\Services\BibliotecaDemoService;
use Illuminate\Console\Command;

class BibliotecaDemoCommand extends Command
{
    protected $signature = 'biblioteca:demo {--force : Recria acervo/empréstimos demo}';

    protected $description = 'PoC — povoa acervo e empréstimos da biblioteca escolar';

    public function handle(BibliotecaDemoService $demo): int
    {
        $this->info('Biblioteca escolar · carga demo');
        $r = $demo->executar((bool) $this->option('force'));
        if ($r['pulado']) {
            $this->warn('  '.$r['mensagem']);

            return self::SUCCESS;
        }
        if (str_contains($r['mensagem'], 'ausente')) {
            $this->error('  '.$r['mensagem']);

            return self::FAILURE;
        }
        $this->line('  '.$r['mensagem']);

        return self::SUCCESS;
    }
}
