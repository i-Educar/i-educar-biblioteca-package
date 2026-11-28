@extends('biblioteca::layout')
@php $editando = $editando ?? null; @endphp
@section('titulo', $editando ? 'Editar obra #'.$editando['id'] : 'Acervo')
@section('migalha', 'Acervo')
@section('icone-tela', 'fa-book')
@section('titulo-tela', $editando ? 'Editar título' : 'Acervo')
@section('lead')
    @if($editando)
        Altere dados e exemplares. Não é possível reduzir abaixo dos empréstimos ativos ({{ $editando['emprestados'] }}).
    @else
        Cadastro, edição e exclusão de títulos com controle de exemplares.
    @endif
@endsection
@section('acoes-topo')
    @if($editando)
        <a class="botao claro icone" href="{{ url('/biblioteca-escolar/acervo') }}" title="Cancelar edição" aria-label="Cancelar edição"><i aria-hidden="true" class="fa fa-times"></i></a>
    @endif
    <a class="botao claro icone" href="{{ url('/biblioteca-escolar/emprestimos') }}" title="Empréstimos" aria-label="Empréstimos"><i aria-hidden="true" class="fa fa-exchange"></i></a>
@endsection

@section('conteudo')
<section class="aviso-portal">
    <p><strong>Proteção de estoque.</strong> Empréstimos ativos impedem exclusão e limitam a redução de exemplares. O saldo disponível é recalculado com base nos empréstimos em aberto.</p>
</section>

@if($errors->any())
    <p class="flash erro">
        @foreach($errors->all() as $erro){{ $erro }}@if(!$loop->last)<br>@endif@endforeach
    </p>
@endif

<div class="bloco">
    <h2>
        <i class="fa {{ $editando ? 'fa-pencil' : 'fa-plus' }}"></i>
        {{ $editando ? 'Editar título #'.$editando['id'] : 'Novo título' }}
    </h2>
    <form class="form-grid" method="post"
          action="{{ $editando ? url('/biblioteca-escolar/acervo/'.$editando['id']) : url('/biblioteca-escolar/acervo') }}">
        @csrf
        @if($editando)
            @method('PUT')
        @endif
        <label class="col-wide">Título
            <input name="titulo" required maxlength="180" placeholder="Nome da obra"
                   value="{{ old('titulo', $editando['titulo'] ?? '') }}">
        </label>
        <label>Autor
            <input name="autor" maxlength="180" placeholder="Autor(a)"
                   value="{{ old('autor', $editando['autor'] ?? '') }}">
        </label>
        <label>ISBN
            <input name="isbn" maxlength="40" placeholder="Opcional"
                   value="{{ old('isbn', $editando['isbn'] ?? '') }}">
        </label>
        <label>Exemplares
            <input type="number" name="exemplares"
                   min="{{ $editando['min_exemplares'] ?? 1 }}" max="9999"
                   value="{{ old('exemplares', $editando['exemplares'] ?? 1) }}" required>
        </label>
        @if($editando && ($editando['emprestados'] ?? 0) > 0)
            <p class="texto-suave" style="grid-column:1/-1;margin:0">
                Mínimo permitido: {{ $editando['min_exemplares'] }} (há {{ $editando['emprestados'] }} emprestado(s) agora).
            </p>
        @endif
        <div class="acoes">
            @if($editando)
                <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}">Cancelar</a>
                <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar alterações</button>
            @else
                <button class="botao" type="submit"><i class="fa fa-save"></i> Cadastrar</button>
            @endif
        </div>
    </form>
</div>

<div class="bloco">
    <h2><i class="fa fa-list"></i> Títulos</h2>
    @if(empty($linhas))
        <p style="margin:0;color:var(--suave)">Nenhum título cadastrado ainda.</p>
    @else
        <div class="lista-obras">
            @foreach($linhas as $l)
                <article class="obra {{ !empty($editando) && (int) $editando['id'] === (int) $l['id'] ? 'obra--ativa' : '' }}">
                    <div>
                        <h3>{{ $l['titulo'] }}</h3>
                        <p class="meta">{{ $l['meta'] }}@if(!empty($l['corpo'])) · ISBN {{ $l['corpo'] }}@endif</p>
                    </div>
                    <div class="obra-acoes">
                        <span class="badge {{ $l['badge_tom'] ?? 'ok' }}">{{ $l['badge'] }}</span>
                        <a class="botao claro sm" href="{{ url('/biblioteca-escolar/acervo/'.$l['id'].'/editar') }}" title="Editar">
                            <i class="fa fa-pencil"></i>
                        </a>
                        @if(!empty($l['pode_excluir']))
                            <form method="post" action="{{ url('/biblioteca-escolar/acervo/'.$l['id'].'/excluir') }}"
                                  onsubmit="return confirm('Excluir permanentemente este título?')">
                                @csrf
                                <button class="botao claro sm" type="submit" title="Excluir" style="color:var(--erro)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        @else
                            <button class="botao claro sm" type="button" disabled title="Há empréstimos ativos" style="opacity:.45">
                                <i class="fa fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
