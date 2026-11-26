<?php

namespace iEducar\Packages\BuritiBiblioteca\Http\Controllers;

use iEducar\Packages\BuritiBiblioteca\Services\Emprestimo;
use iEducar\Packages\BuritiBiblioteca\Support\LeitoresNucleo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\AuditoriaFluxo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
                ['url' => '/biblioteca-escolar/acervo', 'icone' => 'fa-book', 'rotulo' => 'Acervo', 'meta' => 'Cadastre títulos'],
                ['url' => '/biblioteca-escolar/emprestimos', 'icone' => 'fa-hand-o-right', 'rotulo' => 'Emprestar', 'meta' => 'Abre o empréstimo'],
                ['url' => '/biblioteca-escolar/emprestimos', 'icone' => 'fa-check', 'rotulo' => 'Devolver', 'meta' => 'Registra e finaliza'],
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
            $exemplares = max(0, (int) ($p['exemplares'] ?? 0));
            $disponiveis = max(0, (int) ($p['disponiveis'] ?? 0));
            $emprestados = $this->contarEmprestimosAtivosObra((int) $r['id']);
            // Corrige divergência visual: emprestados reais prevalecem sobre payload.
            if ($emprestados > $exemplares) {
                $emprestados = $exemplares;
            }
            $disponiveisCalc = max(0, $exemplares - $emprestados);

            $linhas[] = [
                'id' => (int) $r['id'],
                'titulo' => $p['titulo'] ?? $r['titulo'],
                'autor' => (string) ($p['autor'] ?? ''),
                'isbn' => (string) ($p['isbn'] ?? ''),
                'exemplares' => $exemplares,
                'disponiveis' => $disponiveisCalc,
                'emprestados' => $emprestados,
                'meta' => ($p['autor'] ?? '—').' · '.$exemplares.' exemplares · '.$emprestados.' emprestado(s)',
                'badge' => $disponiveisCalc.' disp.',
                'badge_tom' => $disponiveisCalc > 0 ? 'ok' : 'fail',
                'corpo' => $p['isbn'] ?? '',
                'pode_excluir' => $emprestados === 0,
            ];
        }

        return view('biblioteca::acervo', [
            'linhas' => $linhas,
            'editando' => null,
        ]);
    }

    public function salvarAcervo(Request $request): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->withInput()->with('erro', 'Schema biblioteca ausente — rode migrate.');
        }

        $dados = $request->validate([
            'titulo' => 'required|string|max:180',
            'autor' => 'nullable|string|max:180',
            'isbn' => 'nullable|string|max:40',
            'exemplares' => 'required|integer|min:1|max:9999',
        ], [
            'titulo.required' => 'Informe o título da obra.',
            'exemplares.min' => 'Informe pelo menos 1 exemplar.',
        ]);

        $titulo = trim($dados['titulo']);
        $autor = trim((string) ($dados['autor'] ?? ''));
        $isbn = trim((string) ($dados['isbn'] ?? ''));
        $exemplares = (int) $dados['exemplares'];

        try {
            $id = (int) DB::table(self::TABELA)->insertGetId([
                'tipo' => 'acervo',
                'titulo' => mb_substr($titulo, 0, 180),
                'meta' => $autor !== '' ? mb_substr($autor, 0, 180) : null,
                'payload' => json_encode([
                    'titulo' => $titulo,
                    'autor' => $autor,
                    'isbn' => $isbn,
                    'exemplares' => $exemplares,
                    'disponiveis' => $exemplares,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('erro', 'Não foi possível cadastrar o título. Tente novamente.');
        }

        $this->auditar('acervo_create', "Título #{$id} '{$titulo}' cadastrado ({$exemplares} exemplares)", $id);

        return redirect('/biblioteca-escolar/acervo')->with('ok', "Título '{$titulo}' cadastrado.");
    }

    public function editarAcervo(int $id): View|RedirectResponse
    {
        $obra = $this->buscar($id);
        if ($obra === null || $obra['tipo'] !== 'acervo') {
            return redirect('/biblioteca-escolar/acervo')->with('erro', 'Título não encontrado.');
        }

        $p = $obra['payload'];
        $exemplares = max(0, (int) ($p['exemplares'] ?? 0));
        $emprestados = $this->contarEmprestimosAtivosObra($id);

        $linhas = [];
        foreach ($this->listar('acervo') as $r) {
            $rp = $r['payload'];
            $ex = max(0, (int) ($rp['exemplares'] ?? 0));
            $emp = $this->contarEmprestimosAtivosObra((int) $r['id']);
            $disp = max(0, $ex - $emp);
            $linhas[] = [
                'id' => (int) $r['id'],
                'titulo' => $rp['titulo'] ?? $r['titulo'],
                'meta' => ($rp['autor'] ?? '—').' · '.$ex.' exemplares · '.$emp.' emprestado(s)',
                'badge' => $disp.' disp.',
                'badge_tom' => $disp > 0 ? 'ok' : 'fail',
                'corpo' => $rp['isbn'] ?? '',
                'emprestados' => $emp,
                'pode_excluir' => $emp === 0,
            ];
        }

        return view('biblioteca::acervo', [
            'linhas' => $linhas,
            'editando' => [
                'id' => $id,
                'titulo' => (string) ($p['titulo'] ?? $obra['titulo'] ?? ''),
                'autor' => (string) ($p['autor'] ?? ''),
                'isbn' => (string) ($p['isbn'] ?? ''),
                'exemplares' => max(1, $exemplares),
                'emprestados' => $emprestados,
                'min_exemplares' => max(1, $emprestados),
            ],
        ]);
    }

    public function atualizarAcervo(Request $request, int $id): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->withInput()->with('erro', 'Schema biblioteca ausente — rode migrate.');
        }

        $dados = $request->validate([
            'titulo' => 'required|string|max:180',
            'autor' => 'nullable|string|max:180',
            'isbn' => 'nullable|string|max:40',
            'exemplares' => 'required|integer|min:1|max:9999',
        ], [
            'titulo.required' => 'Informe o título da obra.',
            'exemplares.min' => 'Informe pelo menos 1 exemplar.',
        ]);

        $titulo = trim($dados['titulo']);
        $autor = trim((string) ($dados['autor'] ?? ''));
        $isbn = trim((string) ($dados['isbn'] ?? ''));
        $exemplaresNovos = (int) $dados['exemplares'];

        try {
            DB::transaction(function () use ($id, $titulo, $autor, $isbn, $exemplaresNovos): void {
                $row = DB::table(self::TABELA)->where('id', $id)->lockForUpdate()->first();
                if ($row === null || (string) $row->tipo !== 'acervo') {
                    throw new \RuntimeException('Título não encontrado.');
                }

                $p = is_string($row->payload) ? (json_decode($row->payload, true) ?: []) : (array) ($row->payload ?? []);
                $emprestados = $this->contarEmprestimosAtivosObra($id);

                if ($exemplaresNovos < $emprestados) {
                    throw new \RuntimeException(
                        "Não é possível reduzir para {$exemplaresNovos} exemplar(es): há {$emprestados} empréstimo(s) ativo(s)."
                    );
                }

                $disponiveis = $exemplaresNovos - $emprestados;
                $tituloAntigo = (string) ($p['titulo'] ?? $row->titulo ?? '');

                DB::table(self::TABELA)->where('id', $id)->update([
                    'titulo' => mb_substr($titulo, 0, 180),
                    'meta' => $autor !== '' ? mb_substr($autor, 0, 180) : null,
                    'payload' => json_encode([
                        'titulo' => $titulo,
                        'autor' => $autor,
                        'isbn' => $isbn,
                        'exemplares' => $exemplaresNovos,
                        'disponiveis' => $disponiveis,
                    ], JSON_UNESCAPED_UNICODE),
                ]);

                if ($tituloAntigo !== $titulo) {
                    $this->sincronizarTituloEmprestimos($id, $titulo);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('erro', $e->getMessage());
        } catch (\Throwable) {
            return back()->withInput()->with('erro', 'Não foi possível atualizar o título. Tente novamente.');
        }

        $this->auditar('acervo_update', "Título #{$id} '{$titulo}' atualizado", $id);

        return redirect('/biblioteca-escolar/acervo')->with('ok', "Título '{$titulo}' atualizado.");
    }

    public function excluirAcervo(int $id): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->with('erro', 'Schema biblioteca ausente — rode migrate.');
        }

        $obra = $this->buscar($id);
        if ($obra === null || $obra['tipo'] !== 'acervo') {
            return redirect('/biblioteca-escolar/acervo')->with('erro', 'Título não encontrado.');
        }

        $ativos = $this->contarEmprestimosAtivosObra($id);
        if ($ativos > 0) {
            return redirect('/biblioteca-escolar/acervo')
                ->with('erro', "Não é possível excluir: há {$ativos} empréstimo(s) ativo(s) deste título. Devolva antes.");
        }

        $titulo = (string) ($obra['payload']['titulo'] ?? $obra['titulo'] ?? '#'.$id);

        try {
            DB::transaction(function () use ($id): void {
                $row = DB::table(self::TABELA)->where('id', $id)->lockForUpdate()->first();
                if ($row === null || (string) $row->tipo !== 'acervo') {
                    throw new \RuntimeException('Título não encontrado.');
                }
                $ativos = $this->contarEmprestimosAtivosObra($id);
                if ($ativos > 0) {
                    throw new \RuntimeException("Há {$ativos} empréstimo(s) ativo(s).");
                }
                DB::table(self::TABELA)->where('id', $id)->delete();
            });
        } catch (\RuntimeException $e) {
            return redirect('/biblioteca-escolar/acervo')->with('erro', $e->getMessage());
        } catch (\Throwable) {
            return redirect('/biblioteca-escolar/acervo')->with('erro', 'Não foi possível excluir o título.');
        }

        $this->auditar('acervo_delete', "Título #{$id} '{$titulo}' excluído", $id);

        return redirect('/biblioteca-escolar/acervo')->with('ok', "Título '{$titulo}' excluído.");
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
                        'meta' => 'Previsto '.$this->formatarData((string) ($p['devolucao_prevista'] ?? ''))
                            .(! empty($p['devolvido_em']) ? ' · finalizado '.$this->formatarData((string) $p['devolvido_em']) : ''),
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
        $leitores = LeitoresNucleo::opcoes();
        $filtroOpcoes = $this->opcoesFiltroLeitor($leitores);

        return view('biblioteca::emprestimos', [
            'linhas' => $linhas,
            'obras' => $obras,
            'filtroLeitor' => $filtroLeitor,
            'leitores' => $leitores,
            'filtroOpcoes' => $filtroOpcoes,
            'diasPadrao' => $params['dias_padrao'],
            'limite' => $params['limite_simultaneo'],
        ]);
    }

    public function exportarEmprestimos(Request $request): Response|RedirectResponse
    {
        $filtroLeitor = trim((string) $request->input('leitor', $request->query('leitor', '')));
        $linhas = [];
        foreach ($this->listar('emprestimo') as $r) {
            $p = $r['payload'];
            $leitor = (string) ($p['leitor'] ?? '');
            if ($filtroLeitor !== '' && ! str_contains(mb_strtolower($leitor), mb_strtolower($filtroLeitor))) {
                continue;
            }
            $atrasado = empty($p['devolvido_em']) && Emprestimo::atrasado((string) ($p['devolucao_prevista'] ?? date('Y-m-d')));
            $status = ! empty($p['devolvido_em']) ? 'Finalizado' : ($atrasado ? 'Atrasado' : 'Em andamento');
            $linhas[] = [
                $r['id'],
                (string) ($p['obra'] ?? $r['titulo'] ?? ''),
                $leitor !== '' ? $leitor : '—',
                (string) ($p['devolucao_prevista'] ?? ''),
                (string) ($p['devolvido_em'] ?? ''),
                $status,
            ];
        }

        return \iEducar\Packages\BuritiExportacaoUnificada\Support\ExportacaoModulo::download(
            'Empréstimos da biblioteca',
            ['ID', 'Obra', 'Leitor', 'Devolução prevista', 'Devolvido em', 'Situação'],
            $linhas,
            'Biblioteca escolar',
            strtolower(trim((string) $request->input('formato', 'pdf'))),
            $filtroLeitor !== '' ? 'Leitor: '.$filtroLeitor : 'Todos os leitores',
            null,
            'biblioteca-emprestimos',
        );
    }

    public function salvarEmprestimo(Request $request): RedirectResponse
    {
        if (! Schema::hasTable(self::TABELA)) {
            return back()->withInput()->with('erro', 'Schema biblioteca ausente.');
        }
        $params = $this->params();
        $obraId = (int) $request->input('obra_id', 0);
        $leitorChave = trim((string) $request->input('leitor_id', ''));
        $leitorLivre = trim((string) $request->input('leitor', ''));
        $dias = max(1, (int) $request->input('dias', $params['dias_padrao']));

        $resolvido = $leitorChave !== '' ? LeitoresNucleo::resolver($leitorChave) : null;
        if ($resolvido !== null) {
            $leitor = $resolvido['nome'];
            $leitorTipo = $resolvido['tipo'];
            $leitorRefId = $resolvido['ref_id'];
        } elseif ($leitorLivre !== '') {
            $leitor = $leitorLivre;
            $leitorTipo = null;
            $leitorRefId = null;
        } else {
            return back()->withInput()->with('erro', 'Selecione o aluno ou servidor na lista.');
        }

        if ($obraId < 1) {
            return back()->withInput()->with('erro', 'Selecione uma obra do acervo.');
        }

        $doLeitor = array_values(array_filter($this->listar('emprestimo'), static function ($e) use ($leitor, $leitorChave) {
            $payload = $e['payload'];
            if ($leitorChave !== '' && ($payload['leitor_id'] ?? '') === $leitorChave) {
                return true;
            }

            return ($payload['leitor'] ?? '') === $leitor;
        }));
        if ($params['bloquear_atraso'] && Emprestimo::leitorComAtraso($doLeitor)) {
            return back()->withInput()->with('erro', 'Leitor com empréstimo em atraso — devolva antes de novo empréstimo (config).');
        }
        $ativosLeitor = count(array_filter($doLeitor, static fn ($e) => empty($e['payload']['devolvido_em'])));

        $empId = 0;
        $tituloObra = '';

        try {
            DB::transaction(function () use (
                $obraId, $leitor, $leitorTipo, $leitorRefId, $leitorChave, $dias, $ativosLeitor, $params,
                &$empId, &$tituloObra
            ): void {
                $row = DB::table(self::TABELA)->where('id', $obraId)->lockForUpdate()->first();
                if ($row === null || (string) $row->tipo !== 'acervo') {
                    throw new \RuntimeException('Selecione uma obra do acervo.');
                }

                $p = is_string($row->payload) ? (json_decode($row->payload, true) ?: []) : (array) ($row->payload ?? []);
                $exemplares = max(0, (int) ($p['exemplares'] ?? 0));
                $emprestados = $this->contarEmprestimosAtivosObra($obraId);
                $disponiveis = max(0, $exemplares - $emprestados);

                if (! Emprestimo::podeEmprestar($disponiveis, $ativosLeitor, $params['limite_simultaneo'])) {
                    throw new \RuntimeException(
                        $disponiveis < 1
                            ? 'Sem exemplar disponível para esta obra.'
                            : 'Leitor no limite de '.$params['limite_simultaneo'].' empréstimos.'
                    );
                }

                $p['exemplares'] = $exemplares;
                $p['disponiveis'] = $disponiveis - 1;
                $tituloObra = (string) ($p['titulo'] ?? $row->titulo ?? 'Obra');

                DB::table(self::TABELA)->where('id', $obraId)->update([
                    'payload' => json_encode($p, JSON_UNESCAPED_UNICODE),
                ]);

                $payloadEmp = [
                    'obra_id' => $obraId,
                    'obra' => $tituloObra,
                    'leitor' => $leitor,
                    'emprestado_em' => date('Y-m-d'),
                    'devolucao_prevista' => date('Y-m-d', strtotime("+{$dias} days")),
                    'devolvido_em' => null,
                ];
                if ($leitorTipo !== null && $leitorRefId !== null) {
                    $payloadEmp['leitor_id'] = $leitorChave;
                    $payloadEmp['leitor_tipo'] = $leitorTipo;
                    $payloadEmp['leitor_ref_id'] = $leitorRefId;
                    if ($leitorTipo === 'aluno') {
                        $payloadEmp['aluno'] = $leitor;
                        $payloadEmp['ref_cod_aluno'] = $leitorRefId;
                    } else {
                        $payloadEmp['servidor'] = $leitor;
                        $payloadEmp['ref_cod_servidor'] = $leitorRefId;
                    }
                }

                $empId = (int) DB::table(self::TABELA)->insertGetId([
                    'tipo' => 'emprestimo',
                    'titulo' => mb_substr($tituloObra, 0, 180),
                    'meta' => $leitor,
                    'payload' => json_encode($payloadEmp, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('erro', $e->getMessage());
        } catch (\Throwable) {
            return back()->withInput()->with('erro', 'Não foi possível registrar o empréstimo. Tente novamente.');
        }

        $this->auditar('emprestimo', "Empréstimo #{$empId} de '{$tituloObra}' para {$leitor}", $empId);

        return redirect('/biblioteca-escolar/emprestimos/'.$empId.'/comprovante')
            ->with('ok', "Empréstimo de '{$tituloObra}' para {$leitor}.");
    }

    public function devolver(int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id): void {
                $empRow = DB::table(self::TABELA)->where('id', $id)->lockForUpdate()->first();
                if ($empRow === null || (string) $empRow->tipo !== 'emprestimo') {
                    throw new \RuntimeException('Empréstimo não encontrado.');
                }

                $p = is_string($empRow->payload) ? (json_decode($empRow->payload, true) ?: []) : (array) ($empRow->payload ?? []);
                if (! empty($p['devolvido_em'])) {
                    throw new \RuntimeException('Este empréstimo já foi devolvido.');
                }

                $p['devolvido_em'] = date('Y-m-d');
                DB::table(self::TABELA)->where('id', $id)->update([
                    'payload' => json_encode($p, JSON_UNESCAPED_UNICODE),
                ]);

                $obraId = (int) ($p['obra_id'] ?? 0);
                if ($obraId < 1) {
                    throw new \RuntimeException('Empréstimo sem obra vinculada — estoque não foi atualizado.');
                }

                $obraRow = DB::table(self::TABELA)->where('id', $obraId)->lockForUpdate()->first();
                if ($obraRow === null || (string) $obraRow->tipo !== 'acervo') {
                    throw new \RuntimeException('Obra do empréstimo não encontrada no acervo — estoque não foi atualizado.');
                }

                $op = is_string($obraRow->payload) ? (json_decode($obraRow->payload, true) ?: []) : (array) ($obraRow->payload ?? []);
                $exemplares = max(0, (int) ($op['exemplares'] ?? 0));
                $emprestados = $this->contarEmprestimosAtivosObra($obraId);
                $op['exemplares'] = $exemplares;
                $op['disponiveis'] = max(0, $exemplares - $emprestados);
                DB::table(self::TABELA)->where('id', $obraId)->update([
                    'payload' => json_encode($op, JSON_UNESCAPED_UNICODE),
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->with('erro', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('erro', 'Não foi possível registrar a devolução. Tente novamente.');
        }

        $emp = $this->buscar($id);
        $this->auditar('devolucao', 'Devolução do empréstimo #'.$id.' · '.($emp['payload']['leitor'] ?? ''), $id);

        return redirect('/biblioteca-escolar/emprestimos/'.$id.'/comprovante')
            ->with('ok', 'Devolução registrada — empréstimo finalizado.');
    }

    public function comprovante(int $id): View|RedirectResponse
    {
        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return redirect('/biblioteca-escolar/emprestimos')->with('erro', 'Empréstimo não encontrado.');
        }

        return view('biblioteca::comprovante', [
            'emprestimo' => $emp,
            'resumo' => $this->resumoEmprestimo($emp),
        ]);
    }

    /**
     * Ficha institucional via Exportação unificada (Dompdf / layout com logos e assinaturas).
     */
    public function exportarComprovante(Request $request, int $id): Response|RedirectResponse
    {
        if (! class_exists(\iEducar\Packages\BuritiExportacaoUnificada\Support\ExportacaoModulo::class)) {
            return redirect('/biblioteca-escolar/emprestimos/'.$id.'/comprovante')
                ->with('erro', 'Módulo de exportação unificada não está instalado.');
        }

        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return redirect('/biblioteca-escolar/emprestimos')->with('erro', 'Empréstimo não encontrado.');
        }

        $r = $this->resumoEmprestimo($emp);
        $via = strtolower(trim((string) $request->input('via', 'ambas')));
        if (! in_array($via, ['biblioteca', 'leitor', 'ambas'], true)) {
            $via = 'ambas';
        }

        $cabecalhos = ['Campo', 'Informação'];
        $linhas = [
            ['Protocolo', '#'.$r['id']],
            ['Situação', $r['status']],
            ['Obra', $r['obra']],
            ['Leitor', $r['leitor']],
            ['Tipo do leitor', $r['leitor_tipo']],
            ['Retirada', $r['emprestado_em']],
            ['Devolução prevista', $r['devolucao_prevista']],
            ['Devolvido em', $r['devolvido_em']],
            ['Controle', 'Documento em 2 vias — Biblioteca e quem retirou'],
        ];

        $assinaturas = $this->assinaturasComprovante($r, $via);
        $rotuloVia = match ($via) {
            'biblioteca' => '1ª via — Biblioteca',
            'leitor' => '2ª via — Leitor',
            default => '2 vias (Biblioteca + Leitor)',
        };

        return \iEducar\Packages\BuritiExportacaoUnificada\Support\ExportacaoModulo::download(
            'Comprovante de empréstimo — Biblioteca',
            $cabecalhos,
            $linhas,
            'Biblioteca escolar',
            'pdf',
            $rotuloVia.' · Protocolo #'.$r['id'].' · '.$r['status'],
            $this->nomeUsuario(),
            'bib-emprestimo-'.$r['id'].($via !== 'ambas' ? '-'.$via : ''),
            true,
            $assinaturas,
        );
    }

    public function etiqueta(int $id): View|RedirectResponse
    {
        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return redirect('/biblioteca-escolar/emprestimos')->with('erro', 'Empréstimo não encontrado.');
        }

        return view('biblioteca::emprestimo-etiqueta', [
            'emprestimo' => $emp,
            'resumo' => $this->resumoEmprestimo($emp),
            'urlComprovante' => url('/biblioteca-escolar/emprestimos/'.$id.'/comprovante'),
        ]);
    }

    public function vias(int $id): View|RedirectResponse
    {
        $emp = $this->buscar($id);
        if ($emp === null || $emp['tipo'] !== 'emprestimo') {
            return redirect('/biblioteca-escolar/emprestimos')->with('erro', 'Empréstimo não encontrado.');
        }

        return view('biblioteca::emprestimo-vias', [
            'emprestimo' => $emp,
            'resumo' => $this->resumoEmprestimo($emp),
        ]);
    }

    public function config(): RedirectResponse
    {
        return redirect('/configuracoes/modulos?modulo=biblioteca')
            ->with('ok', 'Prazos e regras da biblioteca ficam em Configurações → Módulos.');
    }

    public function salvarConfig(Request $request): RedirectResponse
    {
        return redirect('/configuracoes/modulos?modulo=biblioteca')
            ->with('ok', 'Use Configurações → Módulos para salvar as regras da biblioteca.');
    }

    /**
     * Opções do filtro “buscar leitor”: núcleo + nomes já usados em empréstimos.
     *
     * @param  list<array{id:string,nome:string,meta:string}>  $leitores
     * @return list<array{id:string,nome:string,meta:string}>
     */
    private function opcoesFiltroLeitor(array $leitores): array
    {
        $porNome = [];
        foreach ($leitores as $l) {
            $nome = trim((string) ($l['nome'] ?? ''));
            if ($nome === '') {
                continue;
            }
            $porNome[mb_strtolower($nome)] = [
                'id' => $nome,
                'nome' => $nome,
                'meta' => (string) ($l['meta'] ?? ''),
            ];
        }
        foreach ($this->listar('emprestimo') as $e) {
            $nome = trim((string) ($e['payload']['leitor'] ?? $e['meta'] ?? ''));
            if ($nome === '') {
                continue;
            }
            $chave = mb_strtolower($nome);
            if (isset($porNome[$chave])) {
                continue;
            }
            $tipo = (string) ($e['payload']['leitor_tipo'] ?? '');
            $porNome[$chave] = [
                'id' => $nome,
                'nome' => $nome,
                'meta' => $tipo !== '' ? ucfirst($tipo).' (histórico)' : 'Histórico',
            ];
        }
        $lista = array_values($porNome);
        usort($lista, static fn (array $a, array $b): int => strcasecmp($a['nome'], $b['nome']));

        return $lista;
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

    /**
     * @param  array{id:int,tipo:string,titulo:string,meta:?string,payload:array,created_at:?string}  $emp
     * @return array{
     *   id:int,obra:string,leitor:string,leitor_tipo:string,emprestado_em:string,
     *   devolucao_prevista:string,devolvido_em:string,status:string,atrasado:bool
     * }
     */
    private function resumoEmprestimo(array $emp): array
    {
        $p = $emp['payload'];
        $atrasado = empty($p['devolvido_em']) && Emprestimo::atrasado((string) ($p['devolucao_prevista'] ?? date('Y-m-d')));
        $status = ! empty($p['devolvido_em'])
            ? 'Devolvido'
            : ($atrasado ? 'Atrasado' : 'Emprestado');

        $tipo = (string) ($p['leitor_tipo'] ?? '');
        $tipoRotulo = match ($tipo) {
            'aluno' => 'Aluno',
            'servidor' => 'Servidor',
            default => 'Leitor',
        };

        return [
            'id' => (int) $emp['id'],
            'obra' => (string) ($p['obra'] ?? $emp['titulo'] ?? '—'),
            'leitor' => (string) ($p['leitor'] ?? $emp['meta'] ?? '—'),
            'leitor_tipo' => $tipoRotulo,
            'emprestado_em' => $this->formatarData((string) ($p['emprestado_em'] ?? '')),
            'devolucao_prevista' => $this->formatarData((string) ($p['devolucao_prevista'] ?? '')),
            'devolvido_em' => ! empty($p['devolvido_em']) ? $this->formatarData((string) $p['devolvido_em']) : '—',
            'status' => $status,
            'atrasado' => $atrasado,
        ];
    }

    /**
     * @param  array{id:int,obra:string,leitor:string,emprestado_em:string,devolucao_prevista:string,devolvido_em:string,status:string}  $r
     * @return list<array{cargo:string,nome:string,quando?:?string}>
     */
    private function assinaturasComprovante(array $r, string $via): array
    {
        $biblioteca = [
            'cargo' => 'Responsável da biblioteca',
            'nome' => '',
            'quando' => null,
        ];
        $leitor = [
            'cargo' => 'Quem retirou / leu o material',
            'nome' => $r['leitor'] !== '—' ? $r['leitor'] : '',
            'quando' => $r['emprestado_em'] !== '' ? 'Retirada: '.$r['emprestado_em'] : null,
        ];

        return match ($via) {
            'biblioteca' => [$biblioteca],
            'leitor' => [$leitor],
            default => [$biblioteca, $leitor],
        };
    }

    private function formatarData(string $iso): string
    {
        if (class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\DataHora::class)) {
            return \iEducar\Packages\BuritiLagunaAmbiente\Support\DataHora::data($iso);
        }
        $iso = trim($iso);
        if ($iso === '' || $iso === '—') {
            return '—';
        }
        $ts = strtotime($iso);

        return $ts ? date('d/m/Y', $ts) : $iso;
    }

    private function contarEmprestimosAtivosObra(int $obraId): int
    {
        if (! Schema::hasTable(self::TABELA) || $obraId < 1) {
            return 0;
        }

        $total = 0;
        foreach (DB::table(self::TABELA)->where('tipo', 'emprestimo')->orderByDesc('id')->get() as $r) {
            $p = is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) ($r->payload ?? []);
            if ((int) ($p['obra_id'] ?? 0) !== $obraId) {
                continue;
            }
            if (! empty($p['devolvido_em'])) {
                continue;
            }
            $total++;
        }

        return $total;
    }

    private function sincronizarTituloEmprestimos(int $obraId, string $titulo): void
    {
        foreach (DB::table(self::TABELA)->where('tipo', 'emprestimo')->orderByDesc('id')->get() as $r) {
            $p = is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : (array) ($r->payload ?? []);
            if ((int) ($p['obra_id'] ?? 0) !== $obraId) {
                continue;
            }
            $p['obra'] = $titulo;
            DB::table(self::TABELA)->where('id', $r->id)->update([
                'titulo' => mb_substr($titulo, 0, 180),
                'payload' => json_encode($p, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function nomeUsuario(): ?string
    {
        $u = auth()->user();
        if ($u === null) {
            return null;
        }

        try {
            $nome = trim((string) ($u->name ?? $u->login ?? ''));

            return $nome !== '' ? $nome : null;
        } catch (\Throwable) {
            return null;
        }
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
