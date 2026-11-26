@extends('biblioteca::layout')
@php
    $p = $payload ?? ($obra['payload'] ?? []);
    $tituloObra = $p['titulo'] ?? $obra['titulo'] ?? 'Obra';
@endphp
@section('titulo', 'Editar acervo')
@section('migalha', 'Editar')
@section('icone-tela', 'fa-pencil')
@section('titulo-tela', 'Editar: '.$tituloObra)
@section('lead')
    Ajuste dados do título. Disponíveis = exemplares − emprestados ({{ $emprestados ?? 0 }}).
@endsection
@section('acoes-topo')
    <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}"><i class="fa fa-arrow-left"></i> Acervo</a>
@endsection

@section('conteudo')
<section class="aviso-portal">
    <p>
        <strong>Regras.</strong>
        Há <strong>{{ $emprestimos_ativos ?? 0 }}</strong> empréstimo(s) em andamento e
        <strong>{{ $emprestados ?? 0 }}</strong> exemplar(es) emprestado(s).
        O total de exemplares não pode ficar abaixo desse número.
        @if(($emprestimos_ativos ?? 0) > 0)
            Inativação bloqueada até devolver.
        @endif
    </p>
</section>

<div class="bloco">
    <h2><i class="fa fa-book"></i> Dados da obra</h2>
    <form class="form-grid" method="post" action="{{ url('/biblioteca-escolar/acervo/'.($obra['id'] ?? 0)) }}">
        @csrf
        @method('PUT')
        <label class="col-wide">Título *
            <input name="titulo" required maxlength="180" value="{{ old('titulo', $p['titulo'] ?? '') }}">
        </label>
        <label>Autor
            <input name="autor" maxlength="180" value="{{ old('autor', $p['autor'] ?? '') }}">
        </label>
        <label>ISBN
            <input name="isbn" maxlength="40" value="{{ old('isbn', $p['isbn'] ?? '') }}">
        </label>
        <label>Editora
            <input name="editora" maxlength="120" value="{{ old('editora', $p['editora'] ?? '') }}">
        </label>
        <label>Localização
            <input name="localizacao" maxlength="80" value="{{ old('localizacao', $p['localizacao'] ?? '') }}">
        </label>
        <label>Exemplares *
            <input type="number" name="exemplares" min="{{ max(1, (int) ($emprestados ?? 0)) }}" max="9999"
                   value="{{ old('exemplares', $p['exemplares'] ?? 1) }}" required>
        </label>
        <div class="col-wide">
            <p class="texto-suave" style="margin:0">
                Disponíveis atuais: <strong>{{ (int) ($p['disponiveis'] ?? 0) }}</strong>
                · Após salvar: recalculados automaticamente.
            </p>
        </div>
        <div class="acoes">
            <button class="botao" type="submit"><i class="fa fa-save"></i> Salvar alterações</button>
            <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}">Cancelar</a>
        </div>
    </form>
</div>

@if(\iEducar\Packages\BuritiBiblioteca\Services\Acervo::estaAtivo($p))
    <div class="bloco">
        <h2><i class="fa fa-ban"></i> Inativar</h2>
        <p class="lead-bloco">Remove a obra da lista de novos empréstimos. Histórico e empréstimos passados permanecem.</p>
        @if(($emprestimos_ativos ?? 0) > 0)
            <p class="flash erro" style="margin:0">Inativação bloqueada enquanto houver empréstimos em andamento.</p>
        @else
            <form method="post" action="{{ url('/biblioteca-escolar/acervo/'.($obra['id'] ?? 0).'/inativar') }}"
                  onsubmit="return confirm('Inativar esta obra?')">
                @csrf
                <div class="form-grid">
                    <label class="col-wide">Motivo (opcional)
                        <input name="motivo" maxlength="180" placeholder="Ex.: extravio, descarte, substituição">
                    </label>
                </div>
                <div class="acoes" style="margin-top:10px">
                    <button class="botao" type="submit" style="background:var(--erro,#9b2c2c)"><i class="fa fa-ban"></i> Inativar obra</button>
                </div>
            </form>
        @endif
    </div>
@else
    <div class="bloco">
        <h2><i class="fa fa-check"></i> Obra inativa</h2>
        <p class="texto-suave">Inativada em {{ $p['inativado_em'] ?? '—' }}{{ !empty($p['inativado_motivo']) ? ' · '.$p['inativado_motivo'] : '' }}</p>
        <form method="post" action="{{ url('/biblioteca-escolar/acervo/'.($obra['id'] ?? 0).'/reativar') }}">
            @csrf
            <button class="botao" type="submit"><i class="fa fa-check"></i> Reativar</button>
        </form>
    </div>
@endif
@endsection
