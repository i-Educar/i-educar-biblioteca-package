<?php

namespace iEducar\Packages\BuritiBiblioteca\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class BibliotecaDemoService
{
    public const DEMO_TAG = 'laguna-carga-v1';

    private const TABELA = 'biblioteca.poc_registro';

    /**
     * @return array{acervo:int,emprestimos:int,pulado:bool,mensagem:string}
     */
    public function executar(bool $force = false): array
    {
        $vazio = ['acervo' => 0, 'emprestimos' => 0, 'pulado' => false, 'mensagem' => ''];
        if (! Schema::hasTable(self::TABELA)) {
            $vazio['mensagem'] = 'schema biblioteca ausente';

            return $vazio;
        }
        if (! $force && $this->jaPovoado()) {
            $vazio['pulado'] = true;
            $vazio['mensagem'] = 'já povoado (use --force)';

            return $vazio;
        }
        if ($force) {
            $this->limparDemo();
        }

        $obras = [
            ['titulo' => 'O Pequeno Príncipe', 'autor' => 'Antoine de Saint-Exupéry', 'isbn' => '9788522031445', 'exemplares' => 5],
            ['titulo' => 'Dom Casmurro', 'autor' => 'Machado de Assis', 'isbn' => '9788535902778', 'exemplares' => 4],
            ['titulo' => 'A Bolsa Amarela', 'autor' => 'Lygia Bojunga', 'isbn' => '9788516060473', 'exemplares' => 3],
            ['titulo' => 'Capitães da Areia', 'autor' => 'Jorge Amado', 'isbn' => '9788535914061', 'exemplares' => 2],
        ];
        $ids = [];
        foreach ($obras as $o) {
            $ids[] = (int) DB::table(self::TABELA)->insertGetId([
                'tipo' => 'acervo',
                'titulo' => $o['titulo'],
                'meta' => $o['autor'],
                'payload' => json_encode([
                    'titulo' => $o['titulo'],
                    'autor' => $o['autor'],
                    'isbn' => $o['isbn'],
                    'exemplares' => $o['exemplares'],
                    'disponiveis' => max(0, $o['exemplares'] - 1),
                    'demo' => self::DEMO_TAG,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }

        $emprestimos = [
            ['obra' => $obras[0]['titulo'], 'leitor' => 'João Pedro Silva', 'dias' => -3, 'obra_id' => $ids[0]],
            ['obra' => $obras[1]['titulo'], 'leitor' => 'Maria Eduarda Costa', 'dias' => 5, 'obra_id' => $ids[1]],
            ['obra' => $obras[2]['titulo'], 'leitor' => 'Lucas Henrique', 'dias' => 10, 'obra_id' => $ids[2]],
            ['obra' => $obras[0]['titulo'], 'leitor' => 'Ana Clara', 'dias' => -10, 'obra_id' => $ids[0], 'devolvido' => true],
        ];
        $nEmp = 0;
        foreach ($emprestimos as $e) {
            $prevista = now()->addDays($e['dias'])->toDateString();
            DB::table(self::TABELA)->insert([
                'tipo' => 'emprestimo',
                'titulo' => $e['obra'].' → '.$e['leitor'],
                'meta' => $e['leitor'],
                'payload' => json_encode([
                    'obra_id' => $e['obra_id'],
                    'obra' => $e['obra'],
                    'titulo' => $e['obra'],
                    'leitor' => $e['leitor'],
                    'aluno' => $e['leitor'],
                    'devolucao_prevista' => $prevista,
                    'devolvido_em' => ! empty($e['devolvido']) ? now()->subDays(2)->toDateString() : null,
                    'demo' => self::DEMO_TAG,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
            $nEmp++;
        }

        return [
            'acervo' => count($ids),
            'emprestimos' => $nEmp,
            'pulado' => false,
            'mensagem' => 'acervo='.count($ids).' emprestimos='.$nEmp,
        ];
    }

    private function jaPovoado(): bool
    {
        foreach (DB::table(self::TABELA)->where('tipo', 'acervo')->limit(30)->get(['payload']) as $r) {
            $p = is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) $r->payload;
            if (($p['demo'] ?? null) === self::DEMO_TAG) {
                return true;
            }
        }

        return false;
    }

    private function limparDemo(): void
    {
        foreach (DB::table(self::TABELA)->orderByDesc('id')->limit(500)->get(['id', 'payload']) as $r) {
            $p = is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) $r->payload;
            if (($p['demo'] ?? null) === self::DEMO_TAG) {
                DB::table(self::TABELA)->where('id', $r->id)->delete();
            }
        }
    }
}
