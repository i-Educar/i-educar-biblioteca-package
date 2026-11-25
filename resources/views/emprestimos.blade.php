@extends('biblioteca::layout')
@section('titulo', 'Empréstimos')
@section('migalha', 'Empréstimos')
@section('icone-tela', 'fa-exchange')
@section('titulo-tela', 'Empréstimos e devoluções')
@section('lead')
    Empreste, depois registre a devolução para finalizar — busque o histórico por leitor
@endsection
@section('acoes-topo')
    <a class="botao claro" href="{{ url('/biblioteca-escolar/acervo') }}"><i class="fa fa-book"></i> Acervo</a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-search"></i> Histórico por leitor</h2>
        <form class="form-grid" method="get" action="{{ url('/biblioteca-escolar/emprestimos') }}">
            <label>Leitor <input name="leitor" value="{{ $filtroLeitor ?? '' }}" placeholder="Nome ou trecho"></label>
            <div class="acoes">
                <button class="botao" type="submit"><i class="fa fa-search"></i> Buscar</button>
                <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos') }}">Limpar</a>
            </div>
        </form>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-hand-o-right"></i> Novo empréstimo</h2>
        @if(empty($obras))
            <p style="margin:0;color:var(--suave)">Cadastre pelo menos um título no acervo antes de emprestar.</p>
        @else
            <form class="form-grid" method="post" action="{{ url('/biblioteca-escolar/emprestimos') }}">
                @csrf
                @include('buriti-ui::select-busca', [
                    'campo' => 'obra_id',
                    'rotulo' => 'Livro / título',
                    'icone' => 'fa-book',
                    'opcoes' => collect($obras)->filter(fn ($o) => ($o['disponiveis'] ?? 0) >= 1)->map(fn ($o) => [
                        'id' => $o['id'],
                        'nome' => $o['titulo'],
                        'meta' => ($o['disponiveis'] ?? 0).' disponível(is)',
                    ])->values()->all(),
                    'selecionado' => null,
                    'placeholder' => 'Digite o título do livro…',
                    'obrigatorio' => true,
                    'ajuda' => 'Só aparecem títulos com exemplar disponível.',
                ])
                <label>Quem leva (nome ou matrícula) <input name="leitor" required maxlength="120" placeholder="Nome do estudante ou servidor"></label>
                <label>Prazo (dias) <input type="number" name="dias" min="1" max="60" value="{{ $diasPadrao ?? 7 }}"></label>
                <p class="col-wide" style="margin:0;color:var(--suave);font-size:.85rem">
                    Limite atual: {{ $limite ?? 3 }} exemplar(es) por leitor
                    · <a href="{{ url('/biblioteca-escolar/config') }}">alterar regras</a>
                </p>
                <div class="acoes"><button class="botao" type="submit"><i class="fa fa-check"></i> Emprestar</button></div>
            </form>
        @endif
    </div>

    <div class="bloco">
        <h2><i class="fa fa-history"></i> Movimentação</h2>
        @if(empty($linhas))
            <p style="margin:0;color:var(--suave)">Nenhum empréstimo registrado.</p>
        @else
            <table>
                <thead><tr><th>Obra / leitor</th><th>Situação</th><th>Prazo</th><th></th></tr></thead>
                <tbody>
                    @foreach($linhas as $l)
                        <tr>
                            <td>{{ $l['titulo'] }}</td>
                            <td><span class="badge {{ $l['badge_tom'] }}">{{ $l['badge'] }}</span></td>
                            <td>{{ $l['meta'] }}</td>
                            <td style="white-space:nowrap">
                                <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos/'.$l['id'].'/comprovante') }}" style="padding:4px 8px;font-size:.78rem"><i class="fa fa-print"></i></a>
                                @if(!empty($l['ativo']))
                                    <form method="post" action="{{ url('/biblioteca-escolar/emprestimos/'.$l['id'].'/devolver') }}" onsubmit="return confirm('Confirmar devolução?')" style="display:inline">
                                        @csrf
                                        <button class="botao claro" type="submit"><i class="fa fa-undo"></i> Devolver</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
