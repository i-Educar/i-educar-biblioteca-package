@extends('biblioteca::layout')
@section('titulo', 'Acervo')
@section('migalha', 'Acervo')
@section('icone-tela', 'fa-book')
@section('titulo-tela', 'Acervo')
@section('lead')
    Cadastro de títulos e controle de exemplares disponíveis
@endsection
@section('acoes-topo')
    <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos') }}"><i class="fa fa-exchange"></i> Empréstimos</a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-plus"></i> Novo título</h2>
        <form class="form-grid" method="post" action="{{ url('/biblioteca-escolar/acervo') }}">
            @csrf
            <label class="col-wide">Título <input name="titulo" required maxlength="180" placeholder="Nome da obra"></label>
            <label>Autor <input name="autor" maxlength="180" placeholder="Autor(a)"></label>
            <label>ISBN <input name="isbn" maxlength="40" placeholder="Opcional"></label>
            <label>Exemplares <input type="number" name="exemplares" min="1" value="1"></label>
            <div class="acoes"><button class="botao" type="submit"><i class="fa fa-save"></i> Cadastrar</button></div>
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-list"></i> Títulos</h2>
        @if(empty($linhas))
            <p style="margin:0;color:var(--suave)">Nenhum título cadastrado ainda.</p>
        @else
            <div class="lista-obras">
                @foreach($linhas as $l)
                    <article class="obra">
                        <div>
                            <h3>{{ $l['titulo'] }}</h3>
                            <p class="meta">{{ $l['meta'] }}@if(!empty($l['corpo'])) · ISBN {{ $l['corpo'] }}@endif</p>
                        </div>
                        <span class="badge {{ $l['badge_tom'] ?? 'ok' }}">{{ $l['badge'] }}</span>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
