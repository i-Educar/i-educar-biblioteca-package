@extends('biblioteca::layout')
@section('titulo', 'Empréstimos')
@section('migalha', 'Empréstimos')
@section('icone-tela', 'fa-exchange')
@section('titulo-tela', 'Empréstimos e devoluções')
@section('lead')
    Foque na movimentação; emprestar e buscar ficam nos botões.
@endsection
@section('acoes-topo')
    @include('exportacao::partials.botoes-exportar', [
        'action' => url('/biblioteca-escolar/emprestimos/exportar'),
        'formatos' => ['pdf', 'xlsx', 'csv'],
        'opcoes' => [
            ['value' => 'movimentacao', 'label' => 'Movimentação (empréstimos)'],
        ],
        'campoExtra' => ['leitor' => $filtroLeitor ?? ''],
        'tituloModal' => 'Download — Empréstimos',
    ])
    @if(!empty($obras))
        <button type="button" class="botao primaria" data-buriti-modal="modal-emprestar">
            <i class="fa fa-hand-o-right"></i> Emprestar
        </button>
    @else
        <button type="button" class="botao buriti-acao-bloqueada" disabled title="Cadastre um título no acervo">
            <i class="fa fa-lock"></i> Emprestar
        </button>
    @endif
    <button type="button" class="botao consulta" data-buriti-modal="modal-buscar-leitor">
        <i class="fa fa-search"></i> Buscar leitor
    </button>
    <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}"><i class="fa fa-book"></i> Acervo</a>
@endsection

@section('foco')
@php
    $ativos = collect($linhas ?? [])->where('ativo', true)->count();
    $total = count($linhas ?? []);
@endphp
<section class="buriti-foco">
    <p class="buriti-foco-kicker">Agora</p>
    <h2 class="buriti-foco-titulo">
        @if($ativos > 0)
            {{ $ativos }} empréstimo{{ $ativos === 1 ? '' : 's' }} em aberto
        @else
            Nenhum empréstimo em aberto
        @endif
    </h2>
    <p class="buriti-foco-texto">Devolva pelo botão na linha. Histórico listado: {{ $total }}.</p>
</section>
@endsection

@section('conteudo')
<style>
    .bib-emp-form { display: grid; gap: 14px; margin: 0; }
    .bib-emp-passo {
        display: grid; gap: 8px; padding: 12px 12px 14px;
        border: 1px solid var(--linha, #cfd8df); border-radius: 12px;
        background: color-mix(in srgb, var(--clara, #e8f1f8) 40%, #fff);
    }
    .bib-emp-passo-num {
        margin: 0; font-size: .68rem; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase; color: var(--cor-tela, var(--cor, #1e4d7b));
    }
    .bib-emp-passo .buriti-select,
    .bib-emp-passo .buriti-select.col-wide {
        display: grid; gap: 4px; width: 100%; max-width: none; margin: 0;
    }
    .bib-emp-passo .buriti-select-lista {
        width: 100%; min-height: 40px !important; height: 40px !important; padding: 8px 10px;
    }
    .bib-emp-campo {
        display: grid; gap: 4px; font-size: .8rem; font-weight: 700;
        color: var(--suave, #5b6b76); margin: 0;
    }
    .bib-emp-campo span { color: var(--tinta, #1a242b); font-weight: 800; }
    .bib-emp-campo input {
        min-height: 40px; padding: 8px 10px; border: 1px solid var(--linha, #cfd8df);
        border-radius: 8px; font: inherit; font-weight: 500; background: #fff;
    }
    .bib-emp-campo small { font-weight: 500; color: var(--suave); }
    .bib-emp-campo--curto { max-width: 160px; }
    .bib-emp-ajuda { margin: 0; font-size: .82rem; color: var(--suave, #5b6b76); font-weight: 500; }
    .bib-emp-ajuda a { color: var(--cor-tela, var(--cor)); font-weight: 700; }
    .bib-emp-acoes {
        display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px;
        position: sticky; bottom: 0; padding: 12px 0 2px; margin: 0;
        background: linear-gradient(180deg, transparent, #fff 35%);
    }
    #modal-emprestar .buriti-modal,
    #modal-buscar-leitor .buriti-modal { width: min(480px, 100%); }
</style>

    <div class="bloco">
        <h2><i class="fa fa-history"></i> Movimentação</h2>
        @if(empty($linhas))
            <p style="margin:0;color:var(--suave)">Nenhum empréstimo registrado — use <strong>Emprestar</strong>.</p>
        @else
            <table>
                <thead><tr><th>Obra / leitor</th><th>Situação</th><th>Prazo</th><th></th></tr></thead>
                <tbody>
                    @foreach($linhas as $l)
                        <tr>
                            <td>{{ $l['titulo'] }}</td>
                            <td><span class="badge {{ $l['badge_tom'] }}">{{ $l['badge'] }}</span></td>
                            <td>{{ $l['meta'] }}</td>
                            <td style="white-space:nowrap">
                                <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos/'.$l['id'].'/comprovante') }}" style="padding:4px 8px;font-size:.78rem"><i class="fa fa-print"></i></a>
                                @if(!empty($l['ativo']))
                                    <form method="post" action="{{ url('/biblioteca-escolar/emprestimos/'.$l['id'].'/devolver') }}" onsubmit="return confirm('Confirmar devolução?')" style="display:inline">
                                        @csrf
                                        <button class="botao" type="submit"><i class="fa fa-undo"></i> Devolver</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @include('buriti-ui::operacao-modal', [
        'id' => 'modal-buscar-leitor',
        'titulo' => 'Filtrar por leitor',
        'lead' => 'Escolha na lista para ver só a movimentação dessa pessoa.',
        'corpo' => view('biblioteca::partials.emprestimos-buscar', [
            'filtroLeitor' => $filtroLeitor ?? '',
            'filtroOpcoes' => $filtroOpcoes ?? [],
        ])->render(),
    ])

    @if(!empty($obras))
        @include('buriti-ui::operacao-modal', [
            'id' => 'modal-emprestar',
            'titulo' => 'Novo empréstimo',
            'lead' => 'Três passos: livro → leitor → prazo.',
            'corpo' => view('biblioteca::partials.emprestimos-novo', [
                'obras' => $obras,
                'leitores' => $leitores ?? [],
                'filtroOpcoes' => $filtroOpcoes ?? [],
                'diasPadrao' => $diasPadrao ?? 7,
                'limite' => $limite ?? 3,
            ])->render(),
        ])
    @endif
@endsection
