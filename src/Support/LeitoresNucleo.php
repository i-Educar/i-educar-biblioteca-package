<?php

namespace iEducar\Packages\BuritiBiblioteca\Support;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Alunos e servidores do i-Educar para seleção em empréstimos (sem digitar o nome).
 */
final class LeitoresNucleo
{
    /**
     * @return list<array{id:string,nome:string,meta:string,tipo:string,ref_id:int}>
     */
    public static function opcoes(int $limite = 250): array
    {
        $escolas = self::escolasDoUsuario();
        $alunos = self::alunos($escolas, $limite);
        $servidores = self::servidores($escolas, $limite);

        $todos = array_merge($alunos, $servidores);
        usort($todos, static fn (array $a, array $b): int => strcasecmp($a['nome'], $b['nome']));

        return $todos;
    }

    public static function resolver(string $chave): ?array
    {
        if (! preg_match('/^(aluno|servidor):(\d+)$/', $chave, $m)) {
            return null;
        }
        $tipo = $m[1];
        $refId = (int) $m[2];
        $nome = $tipo === 'aluno' ? self::nomeAluno($refId) : self::nomeServidor($refId);
        if ($nome === null || $nome === '') {
            return null;
        }

        return [
            'id' => $chave,
            'tipo' => $tipo,
            'ref_id' => $refId,
            'nome' => $nome,
            'meta' => $tipo === 'aluno' ? 'Aluno #'.$refId : 'Servidor #'.$refId,
        ];
    }

    /** @return list<int>|null null = sem restrição (SME/admin) */
    public static function escolasDoUsuario(?User $user = null): ?array
    {
        $user ??= Auth::user();
        if ($user === null) {
            return [];
        }
        if (! method_exists($user, 'isSchooling') || ! $user->isSchooling()) {
            return null;
        }

        return $user->schools()
            ->pluck('cod_escola')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->values()
            ->all();
    }

    /**
     * @param  list<int>|null  $escolas
     * @return list<array{id:string,nome:string,meta:string,tipo:string,ref_id:int}>
     */
    private static function alunos(?array $escolas, int $limite): array
    {
        if (! Schema::hasTable('pmieducar.aluno') || ! Schema::hasTable('cadastro.pessoa')) {
            return [];
        }

        try {
            $query = DB::table('pmieducar.aluno as a')
                ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
                ->where('a.ativo', 1);

            if (Schema::hasTable('pmieducar.matricula')) {
                $query->join('pmieducar.matricula as m', 'm.ref_cod_aluno', '=', 'a.cod_aluno')
                    ->where('m.ativo', 1)
                    ->whereIn('m.aprovado', [1, 2, 3, 12, 13, 14]);
                if ($escolas !== null) {
                    if ($escolas === []) {
                        return [];
                    }
                    $query->whereIn('m.ref_ref_cod_escola', $escolas);
                }
            } elseif ($escolas !== null && $escolas === []) {
                return [];
            }

            return $query
                ->orderBy('p.nome')
                ->limit($limite)
                ->distinct()
                ->get(['a.cod_aluno as id', 'p.nome as nome'])
                ->map(static fn ($r): array => [
                    'id' => 'aluno:'.(int) $r->id,
                    'nome' => (string) $r->nome,
                    'meta' => 'Aluno · matrícula '.(int) $r->id,
                    'tipo' => 'aluno',
                    'ref_id' => (int) $r->id,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param  list<int>|null  $escolas
     * @return list<array{id:string,nome:string,meta:string,tipo:string,ref_id:int}>
     */
    private static function servidores(?array $escolas, int $limite): array
    {
        if (! Schema::hasTable('pmieducar.servidor') || ! Schema::hasTable('cadastro.pessoa')) {
            return [];
        }

        try {
            if ($escolas !== null && Schema::hasTable('pmieducar.servidor_alocacao')) {
                if ($escolas === []) {
                    return [];
                }
                $query = DB::table('pmieducar.servidor_alocacao as sa')
                    ->join('pmieducar.servidor as s', 's.cod_servidor', '=', 'sa.ref_cod_servidor')
                    ->join('cadastro.pessoa as p', 'p.idpes', '=', 's.cod_servidor')
                    ->where('s.ativo', 1)
                    ->where('sa.ativo', 1)
                    ->whereIn('sa.ref_cod_escola', $escolas);
            } else {
                $query = DB::table('pmieducar.servidor as s')
                    ->join('cadastro.pessoa as p', 'p.idpes', '=', 's.cod_servidor')
                    ->where('s.ativo', 1);
            }

            return $query
                ->orderBy('p.nome')
                ->limit($limite)
                ->distinct()
                ->get(['s.cod_servidor as id', 'p.nome as nome'])
                ->map(static fn ($r): array => [
                    'id' => 'servidor:'.(int) $r->id,
                    'nome' => (string) $r->nome,
                    'meta' => 'Servidor · cod. '.(int) $r->id,
                    'tipo' => 'servidor',
                    'ref_id' => (int) $r->id,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private static function nomeAluno(int $id): ?string
    {
        if ($id < 1 || ! Schema::hasTable('pmieducar.aluno') || ! Schema::hasTable('cadastro.pessoa')) {
            return null;
        }
        try {
            $n = DB::table('pmieducar.aluno as a')
                ->join('cadastro.pessoa as p', 'p.idpes', '=', 'a.ref_idpes')
                ->where('a.cod_aluno', $id)
                ->value('p.nome');

            return $n !== null ? (string) $n : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function nomeServidor(int $id): ?string
    {
        if ($id < 1 || ! Schema::hasTable('pmieducar.servidor') || ! Schema::hasTable('cadastro.pessoa')) {
            return null;
        }
        try {
            $n = DB::table('pmieducar.servidor as s')
                ->join('cadastro.pessoa as p', 'p.idpes', '=', 's.cod_servidor')
                ->where('s.cod_servidor', $id)
                ->value('p.nome');

            return $n !== null ? (string) $n : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
