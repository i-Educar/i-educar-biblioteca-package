<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiBiblioteca\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Garante Biblioteca aninhada em Escola (mesmo padrão do Atendimento).
 */
return new class extends Migration
{
    public function up(): void
    {
        $escola = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q) {
                $q->where('process', 55)->orWhere('title', 'Escola');
            })
            ->first();

        if ($escola === null) {
            return;
        }

        // Remove raiz solta no topo (type 1 / parent null) se existir
        Menu::query()
            ->where('process', Processos::RAIZ)
            ->where(function ($q) use ($escola) {
                $q->whereNull('parent_id')
                    ->orWhere('parent_id', '!=', $escola->getKey());
            })
            ->update([
                'parent_id' => $escola->getKey(),
                'type' => 2,
                'title' => 'Biblioteca',
                'description' => 'Acervo e empréstimos da escola',
                'link' => '/biblioteca-escolar',
                'icon' => 'fa-book',
                'order' => 7,
                'active' => true,
            ]);

        $raiz = Menu::query()->updateOrCreate(['process' => Processos::RAIZ], [
            'parent_id' => $escola->getKey(),
            'title' => 'Biblioteca',
            'description' => 'Acervo e empréstimos da escola',
            'link' => '/biblioteca-escolar',
            'icon' => 'fa-book',
            'order' => 7,
            'type' => 2,
            'active' => true,
        ]);

        foreach ([
            [Processos::PAINEL, 'Painel', '/biblioteca-escolar', 'Visão geral', 1],
            [Processos::ACERVO, 'Acervo', '/biblioteca-escolar/acervo', 'Títulos e exemplares', 2],
            [Processos::EMPRESTIMOS, 'Empréstimos', '/biblioteca-escolar/emprestimos', 'Empréstimo e devolução', 3],
        ] as [$processo, $titulo, $link, $descricao, $ordem]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $raiz->getKey(),
                'title' => $titulo,
                'description' => $descricao,
                'link' => $link,
                'order' => $ordem,
                'type' => 3,
                'active' => true,
            ]);
        }

        $processos = [Processos::RAIZ, Processos::PAINEL, Processos::ACERVO, Processos::EMPRESTIMOS];
        foreach (DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->get(['cod_tipo_usuario']) as $tipo) {
            foreach ($processos as $processo) {
                $menuId = Menu::query()->where('process', $processo)->value('id');
                if ($menuId === null) {
                    continue;
                }
                DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                    ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                    ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
                );
            }
        }

        if (class_exists(MenuCacheService::class)) {
            app(MenuCacheService::class)->flushAll();
        }
    }

    public function down(): void
    {
        // Mantém sob Escola; não sobe para raiz.
    }
};
