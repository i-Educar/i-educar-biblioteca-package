<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Biblioteca e Exportação compartilhavam 881500–881502.
 * Ao ativar/desativar um módulo, sincronizarMenus derrubava o outro (404/menus off).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $mapa = [
            881500 => 881510,
            881501 => 881511,
            881502 => 881512,
            881503 => 881513,
        ];

        foreach ($mapa as $antigo => $novo) {
            DB::table('menus')
                ->where('process', $antigo)
                ->where(function ($q): void {
                    $q->where('link', '/biblioteca-escolar')
                        ->orWhere('link', 'like', '/biblioteca-escolar/%')
                        ->orWhere('title', 'ilike', 'biblioteca%')
                        ->orWhere('title', 'ilike', 'acervo%')
                        ->orWhere('title', 'ilike', 'empréstimo%')
                        ->orWhere('title', 'ilike', 'emprestimo%');
                })
                ->update(['process' => $novo]);
        }

        // Reativa menus da biblioteca se o módulo estiver ativo (ou sem setting = ativo).
        $ativo = DB::table('settings')->where('key', 'buriti.modulos.biblioteca.ativo')->value('value');
        $ligado = $ativo === null || in_array((string) $ativo, ['1', 'true', 'on', 'yes'], true);
        if ($ligado) {
            DB::table('menus')
                ->where(function ($q): void {
                    $q->where('link', '/biblioteca-escolar')
                        ->orWhere('link', 'like', '/biblioteca-escolar/%');
                })
                ->update(['active' => true]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus')) {
            return;
        }

        $mapa = [
            881510 => 881500,
            881511 => 881501,
            881512 => 881502,
            881513 => 881503,
        ];

        foreach ($mapa as $novo => $antigo) {
            DB::table('menus')
                ->where('process', $novo)
                ->where(function ($q): void {
                    $q->where('link', '/biblioteca-escolar')
                        ->orWhere('link', 'like', '/biblioteca-escolar/%');
                })
                ->update(['process' => $antigo]);
        }
    }
};
