@extends('biblioteca::layout')
@section('titulo', 'Início — Biblioteca')
@section('migalha', 'Início')
@section('icone-tela', 'fa-book')
@section('titulo-tela', 'Biblioteca da escola')
@section('lead')
    Empreste livros, registre devoluções e acompanhe quem está com atraso.
@endsection
@section('acoes-topo')
    <a class="botao icone" href="{{ url('/biblioteca-escolar/emprestimos') }}" title="Emprestar" aria-label="Emprestar"><i aria-hidden="true" class="fa fa-exchange"></i></a>
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/acervo') }}" title="Novo título" aria-label="Novo título"><i aria-hidden="true" class="fa fa-plus"></i></a>
@endsection

@section('conteudo')
    @include('buriti-ui::painel-corpo', [
        'kpis' => $kpis ?? [],
        'atalhos' => $atalhos ?? [],
        'destaques' => $destaques ?? [],
        'analise' => $analise ?? [],
        'analiseTitulo' => $analiseTitulo ?? 'Análise',
        'resumo' => $resumo ?? null,
        'fluxoTitulo' => $fluxoTitulo ?? 'Fluxo do módulo',
    ])
@endsection
