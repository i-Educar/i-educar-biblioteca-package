@extends('biblioteca::layout')
@php
    $p = $emprestimo['payload'] ?? [];
    $titulo = ($p['obra'] ?? $emprestimo['titulo'] ?? 'Obra').' · '.($p['leitor'] ?? '');
@endphp
@section('titulo', 'Comprovante #'.($emprestimo['id'] ?? ''))
@section('migalha', 'Comprovante')
@section('icone-tela', 'fa-print')
@section('titulo-tela', 'Comprovante de empréstimo')
@section('lead')
    {{ $titulo }}
@endsection
@section('acoes-topo')
    <button class="botao claro" type="button" onclick="window.print()"><i class="fa fa-print"></i> Imprimir</button>
    <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos') }}"><i class="fa fa-exchange"></i> Voltar</a>
@endsection

@section('conteudo')
    <div class="bloco" id="comprovante-print">
        <h2><i class="fa fa-book"></i> Biblioteca escolar — comprovante</h2>
        <dl class="form-grid" style="margin-top:12px">
            <div><strong>Protocolo</strong><br>#{{ $emprestimo['id'] ?? '—' }}</div>
            <div><strong>Situação</strong><br>
                @if(!empty($p['devolvido_em']))
                    Devolvido em {{ $p['devolvido_em'] }}
                @else
                    Emprestado
                @endif
            </div>
            <div class="col-wide"><strong>Obra</strong><br>{{ $p['obra'] ?? $emprestimo['titulo'] ?? '—' }}</div>
            <div><strong>Leitor</strong><br>{{ $p['leitor'] ?? '—' }}</div>
            <div><strong>Retirada</strong><br>{{ $p['emprestado_em'] ?? '—' }}</div>
            <div><strong>Devolução prevista</strong><br>{{ $p['devolucao_prevista'] ?? '—' }}</div>
            @if(!empty($p['devolvido_em']))
                <div><strong>Devolvido em</strong><br>{{ $p['devolvido_em'] }}</div>
            @endif
        </dl>
        <p style="margin-top:16px;color:var(--suave);font-size:.85rem">
            Emitido em {{ now()->format('d/m/Y H:i') }} · guarde este comprovante.
        </p>
    </div>
@endsection
