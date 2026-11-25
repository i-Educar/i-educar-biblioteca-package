@extends('biblioteca::layout')
@section('titulo', 'Configuração')
@section('migalha', 'Configuração')
@section('icone-tela', 'fa-sliders')
@section('titulo-tela', 'Configuração da biblioteca')
@section('lead')
    Limite de empréstimos, prazo padrão e bloqueio por atraso — regras da rede escolar
@endsection
@section('acoes-topo')
    <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos') }}"><i class="fa fa-exchange"></i> Empréstimos</a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-cog"></i> Parâmetros operacionais</h2>
        <form class="form-grid" method="post" action="{{ url('/biblioteca-escolar/config') }}">
            @csrf
            <label>Limite de exemplares simultâneos por leitor
                <input type="number" name="limite_simultaneo" min="1" max="50" value="{{ $params['limite_simultaneo'] ?? 3 }}" required>
            </label>
            <label>Prazo padrão de empréstimo (dias)
                <input type="number" name="dias_padrao" min="1" max="90" value="{{ $params['dias_padrao'] ?? 7 }}" required>
            </label>
            <label class="col-wide">
                <input type="checkbox" name="bloquear_atraso" value="1" @checked(!empty($params['bloquear_atraso']))>
                Bloquear novos empréstimos enquanto houver atraso na devolução
            </label>
            <div class="acoes">
                <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar configuração</button>
            </div>
        </form>
        <p style="margin:12px 0 0;color:var(--suave);font-size:.85rem">
            Defaults também via <code>BIBLIOTECA_*</code> no .env. Alterações aqui ficam em <code>biblioteca.poc_registro</code> (auditoria).
        </p>
    </div>
@endsection
