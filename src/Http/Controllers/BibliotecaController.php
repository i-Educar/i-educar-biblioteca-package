<?php

namespace iEducar\Packages\BuritiBiblioteca\Http\Controllers;

use iEducar\Packages\BuritiBiblioteca\Services\Emprestimo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\AuditoriaFluxo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BibliotecaController extends Controller
{
    private const TABELA = 'biblioteca.poc_registro';

    public function index(): View
    {
        $acervo = $this->contar('acervo');
        $emprestimos = $this->listar('emprestimo');
        $ativos = [];
        $atrasados = [];
        $devolvidos = 0;
        $exemplares = 0;
        $disponiveis = 0;
        foreach ($this->listar('acervo') as $r) {
            $p = $r['payload'];
            $exemplares += (int) ($p['exemplares'] ?? 0);
            $disponiveis += (int) ($p['disponiveis'] ?? 0);
        }
        foreach ($emprestimos as $e) {
            $p = $e['payload'];
            if (! empty($p['devolvido_em'])) {
                $devolvidos++;

                continue;
            }
            $ativos[] = $e;
            if (Emprestimo::atrasado((string) ($p['devolucao_prevista'] ?? date('Y-m-d')))) {
                $atrasados[] = $e;
            }
        }

        $analise = [];
        foreach (array_slice($atrasados, 0, 8) as $e) {
            $p = $e['payload'];
            $analise[] = [
                'titulo' => (string) ($p['leitor'] ?? $p['aluno'] ?? 'Leitor'),
                'meta' => (string) ($p['titulo'] ?? $e['titulo'] ?? 'Obra'),
                'corpo' => 'Previsto: '.($p['devolucao_prevista'] ?? '—'),
                'badge' => 'Atrasado',
                'badge_tom' => 'fail',
                'valor' => '',
                'url' => '/biblioteca-escolar/emprestimos',
            ];
        }
        if ($analise === []) {
            $analise[] = [
                'titulo' => 'Sem atrasos',
                'meta' => count($ativos).' empréstimo(s) em dia',
                'badge' => 'OK',
                'badge_tom' => 'ok',
                'valor' => (string) count($ativos),
            ];
        }

        return view('biblioteca::painel', [
            'kpis' => [
                ['rotulo' => 'Títulos', 'valor' => $acervo, 'icone' => 'fa-book', 'tom' => 'var(--cor)'],
                ['rotulo' => 'Exemplares', 'valor' => $exemplares, 'icone' => 'fa-files-o', 'tom' => 'var(--destaque)'],
                ['rotulo' => 'Disponíveis', 'valor' => $disponiveis, 'icone' => 'fa-check', 'tom' => 'var(--ok)'],
                ['rotulo' => 'Empréstimos ativos', 'valor' => count($ativos), 'icone' => 'fa-exchange', 'tom' => '#0e7490'],
                ['rotulo' => 'Atrasados', 'valor' => count($atrasados), 'icone' => 'fa-clock-o', 'tom' => count($atrasados) ? 'var(--erro)' : 'var(--ok)'],
                ['rotulo' => 'Devolvidos', 'valor' => $devolvidos, 'icone' => 'fa-history', 'tom' => 'var(--ambar)'],
            ],
            'destaques' => [
                [
                    'titulo' => count($atrasados) ? 'Atenção: atrasos' : 'Acervo sob controle',
                    'meta' => $disponiveis.' exemplar(es) disponíveis',
                    'icone' => count($atrasados) ? 'fa-warning' : 'fa-check',
                    'tom' => count($atrasados) ? 'var(--erro)' : 'var(--ok)',
                    'corpo' => count($atrasados)
                        ? count($atrasados).' empréstimo(s) fora do prazo — cobrado no painel.'
                        : 'Nenhum atraso. Taxa de disponibilidade '.($exemplares > 0 ? round(100 * $disponiveis / $exemplares).'%' : '—').'.',
                ],
            ],
            'analise' => $analise,
            'analiseTitulo' => 'Empréstimos em atraso / fila',
            'resumo' => 'Biblioteca escolar · emprestar, devolver e consultar histórico por leitor. Bloqueio por atraso conforme configuração.',
            'atalhos' => [
                ['url' => '/biblioteca-escolar/acervo', 'icone' => 'fa-book', 'rotulo' => '1. Acervo', 'meta' => 'Cadastre títulos'],
                ['url' => '/biblioteca-escolar/emprestimos', 'icone' => 'fa-hand-o-right', 'rotulo' => '2. Emprestar', 'meta' => 'Abre o empréstimo'],
                ['url' => '/biblioteca-escolar/emprestimos', 'icone' => 'fa-check', 'rotulo' => '3. Devolver', 'meta' => 'Registra e finaliza'],
                ['url' => '/biblioteca-escolar/emprestimos', 'icone' => 'fa-search', 'rotulo' => 'Histórico', 'meta' => 'Busca por leitor'],
            ],
            'fluxoTitulo' => 'Fluxo da biblioteca',
        ]);
    }

    public function acervo(): View
    {
        $linhas = [];
        foreach ($this->listar('acervo') as $r) {
            $p = $r['payload'];
            $linhas[] = [
                'titulo' => $p['titulo'] ?? $r['titulo'],
                'meta' => ($p['autor'] ?? '—').' · exemplares '.($p['exemplares'] ?? 0),
                'badge' => ((int) ($p['disponiveis'] ?? 0)).' disp.',
                'badge_tom' => ((int) ($p['disponiveis'] ?? 0)) > 0 ? 'ok' : 'fail',
                'corpo' => $p['isbn'] ?? '',
            ];
        }

        return view('biblioteca::acervo', compact('linhas'));
    }

    public function salvarAcervo(Request $request): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->with('erro', 'Schema biblioteca ausente — rode migrate.');
        }
        $titulo = trim((string) $request->input('titulo', ''));
        $autor = trim((string) $request->input('autor', ''));
        $exemplares = max(1, (int) $request->input('exemplares', 1));
        if ($titulo === '') {
            return back()->with('erro', 'Informe o título.');
        }
        DB::table(self::TABELA)->insert([
            'tipo' => 'acervo',
            'titulo' => mb_substr($titulo, 0, 180),
            'meta' => $autor !== '' ? mb_substr($autor, 0, 180) : null,
            'payload' => json_encode([
                'titulo' => $titulo,
                'autor' => $autor,
                'isbn' => trim((string) $request->input('isbn', '')),
                'exemplares' => $exemplares,
                'disponiveis' => $exemplares,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        return redirect('/biblioteca-escolar/acervo')->with('ok', "Título '{$titulo}' cadastrado.");
    }

    public function emprestimos(Request $request): View
    {
        $filtroLeitor = trim((string) $request->query('leitor', ''));
        $linhas = [];
        foreach ($this->listar('emprestimo') as $r) {
            $p = $r['payload'];
            $leitor = (string) ($p['leitor'] ?? '');
            if ($filtroLeitor !== '' && ! str_contains(mb_strtolower($leitor), mb_strtolower($filtroLeitor))) {
                continue;
            }
            $atrasado = empty($p['devolvido_em']) && Emprestimo::atrasado((string) ($p['devolucao_prevista'] ?? date('Y-m-d')));
            $linhas[] = [
                'id' => $r['id'],
                'titulo' => ($p['obra'] ?? $r['titulo']).' → '.($leitor !== '' ? $leitor : '—'),
                'meta' => 'Previsto '.$p['devolucao_prevista'].(! empty($p['devolvido_em']) ? ' · finalizado '.$p['devolvido_em'] : ''),
                'badge' => ! empty($p['devolvido_em']) ? 'Finalizado' : ($atrasado ? 'Atrasado' : 'Em andamento'),
                'badge_tom' => ! empty($p['devolvido_em']) ? 'ok' : ($atrasado ? 'fail' : 'warn'),
                'ativo' => empty($p['devolvido_em']),
            ];
        }
        $obras = array_map(static fn ($r) => [
            'id' => $r['id'],
            'titulo' => $r['payload']['titulo'] ?? $r['titulo'],
            'disponiveis' => (int) ($r['payload']['disponiveis'] ?? 0),
        ], $this->listar('acervo'));

        $params = $this->params();

        return view('biblioteca::emprestimos', [
            'linhas' => $linhas,
            'obras' => $obras,
            'filtroLeitor' => $filtroLeitor,
            'diasPadrao' => $params['dias_padrao'],
            'limite' => $params['limite_simultaneo'],
        ]);
    }

    public function salvarEmprestimo(Request $request): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->with('erro', 'Schema biblioteca ausente.');
        }
        $params = $this->params();
        $obraId = (int) $request->input('obra_id', 0);
        $leitor = trim((string) $request->input('leitor', ''));
        $dias = max(1, (int) $request->input('dias', $params['dias_padrao']));
        $obra = $this->buscar($obraId);
        if ($obra === null || $obra['tipo'] !== 'acervo') {
            return back()->with('erro', 'Selecione uma obra do acervo.');
        }
        $p = $obra['payload'];
        $doLeitor = array_values(array_filter($this->listar('emprestimo'), static function ($e) use ($leitor) {
            return ($e['payload']['leitor'] ?? '') === $leitor;
        }));
        if ($params['bloquear_atraso'] && Emprestimo::leitorComAtraso($doLeitor)) {
            return back()->with('erro', 'Leitor com empréstimo em atraso — devolva antes de novo empréstimo (config).');
        }
        $ativos = count(array_filter($doLeitor, static fn ($e) => empty($e['payload']['devolvido_em'])));
        if (! Emprestimo::podeEmprestar((int) ($p['disponiveis'] ?? 0), $ativos, $params['limite_simultaneo'])) {
            return back()->with('erro', 'Sem exemplar disponível ou leitor no limite de '.$params['limite_simultaneo'].' empréstimos.');
        }
        $p['disponiveis'] = max(0, (int) $p['disponiveis'] - 1);
        DB::table(self::TABELA)->where('id', $obraId)->update(['payload' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
        $empId = (int) DB::table(self::TABELA)->insertGetId([
            'tipo' => 'emprestimo',
            'titulo' => mb_substr((string) $p['titulo'], 0, 180),
            'meta' => $leitor,
            'payload' => json_encode([
                'obra_id' => $obraId,
                'obra' => $p['titulo'],
                'leitor' => $leitor,
                'emprestado_em' => date('Y-m-d'),
                'devolucao_prevista' => date('Y-m-d', strtotime("+{$dias} days")),
                'devolvido_em' => null,
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        $this->auditar('emprestimo', "Empréstimo #{$empId} de '{$p['titulo']}' para {$leitor}", $empId);

        return redirect('/biblioteca-escolar/emprestimos/'.$empId.'/comprovante')
            ->with('ok', "Empréstimo de '{$p['titulo']}' para {$leitor}.");
    }

    public function devolver(int $id): RedirectResponse
    {
        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return back()->with('erro', 'Empréstimo não encontrado.');
        }
        $p = $emp['payload'];
        if (! empty($p['devolvido_em'])) {
            return back()->with('erro', 'Já devolvido.');
        }
        $p['devolvido_em'] = date('Y-m-d');
        DB::table(self::TABELA)->where('id', $id)->update(['payload' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
        $obra = $this->buscar((int) ($p['obra_id'] ?? 0));
        if ($obra !== null) {
            $op = $obra['payload'];
            $op['disponiveis'] = min((int) ($op['exemplares'] ?? 0), (int) ($op['disponiveis'] ?? 0) + 1);
            DB::table(self::TABELA)->where('id', $obra['id'])->update(['payload' => json_encode($op, JSON_UNESCAPED_UNICODE)]);
        }

        $this->auditar('devolucao', 'Devolução do empréstimo #'.$id.' · '.($p['leitor'] ?? ''), $id);

        return redirect('/biblioteca-escolar/emprestimos/'.$id.'/comprovante')
            ->with('ok', 'Devolução registrada — empréstimo finalizado.');
    }

    public function comprovante(int $id): View|RedirectResponse
    {
        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return redirect('/biblioteca-escolar/emprestimos')->with('erro', 'Empréstimo não encontrado.');
        }

        return view('biblioteca::comprovante', ['emprestimo' => $emp]);
    }

    public function config(): View
    {
        return view('biblioteca::config', ['params' => $this->params()]);
    }

    public function salvarConfig(Request $request): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->with('erro', 'Schema biblioteca ausente.');
        }
        $payload = [
            'limite_simultaneo' => max(1, min(50, (int) $request->input('limite_simultaneo', 3))),
            'dias_padrao' => max(1, min(90, (int) $request->input('dias_padrao', 7))),
            'bloquear_atraso' => $request->boolean('bloquear_atraso'),
            'atualizado_em' => now()->toIso8601String(),
            'usuario' => AuditoriaFluxo::usuario(),
        ];
        $existente = $this->listar('config')[0] ?? null;
        if ($existente !== null) {
            DB::table(self::TABELA)->where('id', $existente['id'])->update([
                'titulo' => 'Configuração operacional',
                'meta' => 'ativo',
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        } else {
            DB::table(self::TABELA)->insert([
                'tipo' => 'config',
                'titulo' => 'Configuração operacional',
                'meta' => 'ativo',
                'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }
        $this->auditar('config', 'Config biblioteca: limite '.$payload['limite_simultaneo']
            .' · prazo '.$payload['dias_padrao'].'d · bloqueio atraso '
            .($payload['bloquear_atraso'] ? 'sim' : 'não'));

        return redirect('/biblioteca-escolar/config')->with('ok', 'Configuração salva.');
    }

    /** @return array{limite_simultaneo:int,dias_padrao:int,bloquear_atraso:bool} */
    private function params(): array
    {
        $base = [
            'limite_simultaneo' => (int) config('biblioteca.limite_simultaneo', 3),
            'dias_padrao' => (int) config('biblioteca.dias_padrao', 7),
            'bloquear_atraso' => (bool) config('biblioteca.bloquear_atraso', true),
        ];
        $cfg = $this->listar('config')[0] ?? null;
        if ($cfg === null) {
            return $base;
        }
        $p = $cfg['payload'];

        return [
            'limite_simultaneo' => max(1, (int) ($p['limite_simultaneo'] ?? $base['limite_simultaneo'])),
            'dias_padrao' => max(1, (int) ($p['dias_padrao'] ?? $base['dias_padrao'])),
            'bloquear_atraso' => (bool) ($p['bloquear_atraso'] ?? $base['bloquear_atraso']),
        ];
    }

    private function auditar(string $acao, string $detalhe, ?int $refId = null): void
    {
        AuditoriaFluxo::registrar(
            function (string $tipo, string $titulo, ?string $meta, array $payload) {
                if (! Schema::hasTable(self::TABELA)) {
                    return 0;
                }

                return (int) DB::table(self::TABELA)->insertGetId([
                    'tipo' => $tipo,
                    'titulo' => mb_substr($titulo, 0, 180),
                    'meta' => $meta !== null ? mb_substr($meta, 0, 180) : null,
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                ]);
            },
            $acao,
            $detalhe,
            $refId
        );
    }

    private function contar(string $tipo): int
    {
        if (! Schema::hasTable(self::TABELA)) {
            return 0;
        }

        return (int) DB::table(self::TABELA)->where('tipo', $tipo)->count();
    }

    /** @return list<array{id:int,tipo:string,titulo:string,meta:?string,payload:array,created_at:?string}> */
    private function listar(string $tipo): array
    {
        if (! Schema::hasTable(self::TABELA)) {
            return [];
        }

        return DB::table(self::TABELA)->where('tipo', $tipo)->orderByDesc('id')->limit(200)->get()
            ->map(static fn ($r): array => [
                'id' => (int) $r->id,
                'tipo' => (string) $r->tipo,
                'titulo' => (string) $r->titulo,
                'meta' => $r->meta !== null ? (string) $r->meta : null,
                'payload' => is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) ($r->payload ?? []),
                'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
            ])->all();
    }

    private function buscar(int $id): ?array
    {
        if (! Schema::hasTable(self::TABELA) || $id < 1) {
            return null;
        }
        $r = DB::table(self::TABELA)->where('id', $id)->first();
        if ($r === null) {
            return null;
        }

        return [
            'id' => (int) $r->id,
            'tipo' => (string) $r->tipo,
            'titulo' => (string) $r->titulo,
            'meta' => $r->meta !== null ? (string) $r->meta : null,
            'payload' => is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) ($r->payload ?? []),
            'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
        ];
    }
}
