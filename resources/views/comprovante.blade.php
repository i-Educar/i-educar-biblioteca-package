@extends('biblioteca::layout')
@php
    $p = $emprestimo['payload'] ?? [];
    $r = $resumo ?? [];
    $titulo = ($r['obra'] ?? $p['obra'] ?? $emprestimo['titulo'] ?? 'Obra').' · '.($r['leitor'] ?? $p['leitor'] ?? '');
@endphp
@section('titulo', 'Comprovante #'.($emprestimo['id'] ?? ''))
@section('migalha', 'Comprovante')
@section('icone-tela', 'fa-print')
@section('titulo-tela', 'Comprovante de empréstimo')
@section('lead')
    {{ $titulo }}
@endsection
@section('acoes-topo')
    <form method="POST" action="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/comprovante/exportar') }}" style="display:inline">
        @csrf
        <input type="hidden" name="via" value="ambas">
        <button class="botao icone" type="submit" title="PDF institucional" aria-label="PDF institucional"><i aria-hidden="true" class="fa fa-file-pdf-o"></i></button>
    </form>
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/vias') }}" title="2 vias" aria-label="2 vias"><i aria-hidden="true" class="fa fa-files-o"></i></a>
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/etiqueta') }}" title="Etiqueta" aria-label="Etiqueta"><i aria-hidden="true" class="fa fa-tags"></i></a>
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/emprestimos') }}" title="Voltar" aria-label="Voltar"><i aria-hidden="true" class="fa fa-exchange"></i></a>
@endsection

@section('conteudo')
<section class="aviso-portal">
    <p><strong>Exportação unificada.</strong> O PDF usa o mesmo layout institucional dos demais módulos (logos, cabeçalho, QR de validação e linhas de assinatura). Use <em>2 vias</em> para controle físico (biblioteca + leitor) e <em>Etiqueta</em> para colar no volume.</p>
</section>

<div class="bloco">
    <h2><i class="fa fa-book"></i> Pré-visualização</h2>
    <p class="lead-bloco">Protocolo #{{ $r['id'] ?? $emprestimo['id'] ?? '—' }} · {{ $r['status'] ?? '—' }}</p>
    <dl class="form-grid" style="margin-top:12px">
        <div><strong>Obra</strong><br>{{ $r['obra'] ?? '—' }}</div>
        <div><strong>Leitor</strong><br>{{ $r['leitor'] ?? '—' }} <span class="texto-suave">({{ $r['leitor_tipo'] ?? 'Leitor' }})</span></div>
        <div><strong>Retirada</strong><br>{{ $r['emprestado_em'] ?? '—' }}</div>
        <div><strong>Devolução prevista</strong><br>{{ $r['devolucao_prevista'] ?? '—' }}</div>
        <div><strong>Devolvido em</strong><br>{{ $r['devolvido_em'] ?? '—' }}</div>
        <div><strong>Emitido em</strong><br>@dataAbs(now())</div>
    </dl>

    <h2 style="margin-top:18px"><i class="fa fa-pencil"></i> Assinaturas no PDF</h2>
    <p class="texto-suave">No documento institucional entram linhas para o responsável da biblioteca e para quem retirou o material.</p>
    <div class="acoes-linha" style="margin-top:10px">
        <form method="POST" action="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/comprovante/exportar') }}">
            @csrf
            <input type="hidden" name="via" value="ambas">
            <button class="botao" type="submit"><i class="fa fa-file-pdf-o"></i> PDF — 2 assinaturas</button>
        </form>
        <form method="POST" action="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/comprovante/exportar') }}">
            @csrf
            <input type="hidden" name="via" value="biblioteca">
            <button class="botao claro" type="submit">Só via biblioteca</button>
        </form>
        <form method="POST" action="{{ url('/biblioteca-escolar/emprestimos/'.($emprestimo['id'] ?? 0).'/comprovante/exportar') }}">
            @csrf
            <input type="hidden" name="via" value="leitor">
            <button class="botao claro" type="submit">Só via leitor</button>
        </form>
    </div>
</div>
@endsection
