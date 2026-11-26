<form class="bib-emp-form" method="get" action="{{ url('/biblioteca-escolar/emprestimos') }}">
    <section class="bib-emp-passo">
        <p class="bib-emp-passo-num">Leitor</p>
        @if(!empty($filtroOpcoes))
            @include('buriti-ui::select-busca', [
                'campo' => 'leitor',
                'rotulo' => 'Aluno, servidor ou histórico',
                'icone' => 'fa-user',
                'opcoes' => $filtroOpcoes,
                'selecionado' => $filtroLeitor ?? '',
                'placeholder' => 'Buscar por nome…',
                'vazio' => '— todos os leitores —',
                'compacto' => true,
                'colWide' => true,
                'obrigatorio' => false,
            ])
        @else
            <label class="bib-emp-campo">
                <span>Nome ou trecho</span>
                <input name="leitor" value="{{ $filtroLeitor ?? '' }}" placeholder="Digite para filtrar…" autocomplete="off">
            </label>
        @endif
    </section>
    <div class="bib-emp-acoes">
        <a class="botao claro" href="{{ url('/biblioteca-escolar/emprestimos') }}">Limpar</a>
        <button class="botao" type="submit"><i class="fa fa-search"></i> Filtrar movimentação</button>
    </div>
</form>
