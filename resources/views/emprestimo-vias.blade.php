@extends('biblioteca::layout')
@php $r = $resumo; @endphp
@section('titulo', 'Vias #'.$r['id'])
@section('migalha', '2 vias')
@section('icone-tela', 'fa-files-o')
@section('titulo-tela', 'Controle em 2 vias — empréstimo #'.$r['id'])
@section('lead', 'Imprima, destaque no meio e arquive uma via na biblioteca; a outra fica com quem retirou.')
@section('acoes-topo')
    <form method="POST" action="{{ url('/biblioteca-escolar/emprestimos/'.$r['id'].'/comprovante/exportar') }}" style="display:inline">
        @csrf
        <input type="hidden" name="via" value="ambas">
        <button class="botao" type="submit"><i class="fa fa-file-pdf-o"></i> PDF institucional</button>
    </form>
    <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos/'.$r['id'].'/comprovante') }}"><i class="fa fa-arrow-left"></i> Comprovante</a>
    <button class="botao claro" type="button" onclick="window.print()"><i class="fa fa-print"></i> Imprimir vias</button>
@endsection

@section('conteudo')
<style>
    .vias-folha { display: grid; gap: 18px; }
    .via {
        break-inside: avoid; page-break-inside: avoid;
        border: 1px solid #c9c0c0; border-radius: 12px; padding: 16px 18px; background: #fff;
        border-top: 3px solid var(--cor-tela, #1e3a5f);
    }
    .via-topo { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; margin-bottom: 10px; }
    .via-topo h3 { margin: 0; font-size: 1rem; }
    .via-selo {
        font-size: .7rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
        color: var(--cor-tela, #1e3a5f); background: color-mix(in srgb, var(--cor-tela, #1e3a5f) 12%, #fff);
        padding: 4px 8px; border-radius: 999px;
    }
    .via dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 14px; margin: 0; }
    .via dt { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--suave); margin: 0; }
    .via dd { margin: 2px 0 0; font-weight: 700; }
    .via-corte {
        border: 0; border-top: 1px dashed #999; margin: 4px 0; position: relative;
    }
    .via-corte::after {
        content: 'destaque aqui';
        position: absolute; left: 50%; top: -0.7em; transform: translateX(-50%);
        background: var(--fundo, #f3f1f1); padding: 0 8px; font-size: .7rem; color: #777;
    }
    .via-assina {
        display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 28px;
        text-align: center; font-size: .8rem; color: var(--suave);
    }
    .via-assina .linha { border-top: 1px solid #1a242b; margin: 36px 12px 6px; }
    .via-assina strong { display: block; color: var(--tinta); font-size: .85rem; }
    .no-print-hint { margin: 0 0 12px; color: var(--suave); font-size: .85rem; }
    @media print {
        .lateral, .topo, .cabeca-tela, .acoes-tela, .flash, footer, .no-print-hint, .aviso-portal { display: none !important; }
        .app { display: block; } .miolo, main { padding: 0 !important; }
        .bloco { border: 0; box-shadow: none; padding: 0; margin: 0; }
        .via { border-radius: 0; page-break-inside: avoid; }
        .via-corte::after { background: #fff; }
    }
</style>

<p class="no-print-hint">Para o padrão visual com logos oficiais, use o <strong>PDF institucional</strong> (Exportação unificada). Esta tela é o controle rápido em papel.</p>

<div class="bloco">
    <div class="vias-folha">
        @foreach([
            ['selo' => '1ª via — Biblioteca', 'foco' => 'Arquivo da biblioteca'],
            ['selo' => '2ª via — Leitor', 'foco' => 'Fica com quem retirou'],
        ] as $via)
            <article class="via">
                <div class="via-topo">
                    <h3>Comprovante de empréstimo</h3>
                    <span class="via-selo">{{ $via['selo'] }}</span>
                </div>
                <p class="texto-suave" style="margin:0 0 10px">{{ $via['foco'] }} · Protocolo EMP-{{ str_pad((string) $r['id'], 6, '0', STR_PAD_LEFT) }}</p>
                <dl>
                    <div><dt>Obra</dt><dd>{{ $r['obra'] }}</dd></div>
                    <div><dt>Leitor</dt><dd>{{ $r['leitor'] }} ({{ $r['leitor_tipo'] }})</dd></div>
                    <div><dt>Retirada</dt><dd>{{ $r['emprestado_em'] }}</dd></div>
                    <div><dt>Devolução prevista</dt><dd>{{ $r['devolucao_prevista'] }}</dd></div>
                    <div><dt>Situação</dt><dd>{{ $r['status'] }}</dd></div>
                    <div><dt>Devolvido em</dt><dd>{{ $r['devolvido_em'] }}</dd></div>
                </dl>
                <div class="via-assina">
                    <div>
                        <div class="linha"></div>
                        <strong>________________</strong>
                        <span>Responsável da biblioteca</span>
                    </div>
                    <div>
                        <div class="linha"></div>
                        <strong>{{ $r['leitor'] !== '—' ? $r['leitor'] : '________________' }}</strong>
                        <span>Quem retirou / leu o material</span>
                    </div>
                </div>
            </article>
            @if($loop->first)
                <hr class="via-corte">
            @endif
        @endforeach
    </div>
</div>
@endsection
