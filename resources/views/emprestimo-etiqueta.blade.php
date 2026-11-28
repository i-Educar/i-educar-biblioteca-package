@extends('biblioteca::layout')
@php $r = $resumo; @endphp
@section('titulo', 'Etiqueta #'.$r['id'])
@section('migalha', 'Etiqueta')
@section('icone-tela', 'fa-tags')
@section('titulo-tela', 'Etiqueta do empréstimo #'.$r['id'])
@section('lead', 'Imprima e cole no volume ou na ficha de controle. Serve de identificação rápida na devolução.')
@section('acoes-topo')
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/emprestimos/'.$r['id'].'/comprovante') }}" title="Comprovante" aria-label="Comprovante"><i aria-hidden="true" class="fa fa-arrow-left"></i></a>
    <button class="botao icone" type="button" onclick="window.print()" title="Imprimir etiqueta" aria-label="Imprimir etiqueta"><i aria-hidden="true" class="fa fa-print"></i></button>
@endsection

@section('conteudo')
<style>
    .etiq-grade { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px; }
    .etiq {
        break-inside: avoid; page-break-inside: avoid;
        border: 2px solid #111; border-radius: 10px; padding: 14px; background: #fff;
        display: grid; gap: 6px; min-height: 160px;
    }
    .etiq-selo { margin: 0; font-size: .65rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #555; }
    .etiq-titulo { margin: 0; font-size: 1rem; font-weight: 800; line-height: 1.25; color: #111; }
    .etiq-linha { margin: 0; font-size: .82rem; color: #222; }
    .etiq-codigo {
        margin: 6px 0 0; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .95rem; font-weight: 800; letter-spacing: .1em;
        padding: 6px 8px; border: 1px dashed #333; border-radius: 6px; display: inline-block;
    }
    .etiq-assin {
        margin-top: 10px; padding-top: 10px; border-top: 1px solid #ccc;
        display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: .72rem; color: #444;
    }
    .etiq-assin span { display: block; border-top: 1px solid #222; margin-top: 22px; padding-top: 4px; text-align: center; }
    .no-print-hint { margin: 0 0 12px; color: var(--suave); font-size: .85rem; }
    @media print {
        .lateral, .topo, .cabeca-tela, .acoes-tela, .flash, footer, .no-print-hint, .aviso-portal { display: none !important; }
        .app { display: block; } .miolo, main { padding: 8px !important; }
        .bloco { border: 0; box-shadow: none; padding: 0; margin: 0; }
        .etiq-grade { grid-template-columns: 1fr; max-width: 360px; }
    }
</style>

<p class="no-print-hint">Duas etiquetas iguais: uma para o volume e outra para o arquivo da biblioteca.</p>

<div class="bloco">
    <div class="etiq-grade">
        @foreach(['Volume', 'Arquivo biblioteca'] as $destino)
            <article class="etiq" aria-label="Etiqueta {{ $destino }}">
                <p class="etiq-selo">Biblioteca escolar · {{ $destino }}</p>
                <h3 class="etiq-titulo">{{ $r['obra'] }}</h3>
                <p class="etiq-linha"><strong>Leitor:</strong> {{ $r['leitor'] }}</p>
                <p class="etiq-linha"><strong>Retirada:</strong> {{ $r['emprestado_em'] }} · <strong>Prevista:</strong> {{ $r['devolucao_prevista'] }}</p>
                <p class="etiq-linha"><strong>Situação:</strong> {{ $r['status'] }}</p>
                <code class="etiq-codigo">EMP-{{ str_pad((string) $r['id'], 6, '0', STR_PAD_LEFT) }}</code>
                <div class="etiq-assin">
                    <div>Biblioteca<span>Assinatura</span></div>
                    <div>Quem retirou<span>Assinatura</span></div>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
