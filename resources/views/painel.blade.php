@extends('biblioteca::layout')
@section('titulo', 'Início — Biblioteca')
@section('migalha', 'Início')
@section('icone-tela', 'fa-book')
@section('titulo-tela', 'Biblioteca da escola')
@section('lead')
    Empreste livros, registre devoluções e acompanhe quem está com atraso.
@endsection
@section('acoes-topo')
    <a class="botao" href="{{ url('/biblioteca-escolar/emprestimos') }}"><i class="fa fa-exchange"></i> Emprestar</a>
    <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}"><i class="fa fa-plus"></i> Novo título</a>
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
