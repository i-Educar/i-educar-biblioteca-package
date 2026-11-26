{{-- Empréstimo: coluna única + select compacto (evita listas abertas sobrepostas no modal) --}}
<form class="bib-emp-form" method="post" action="{{ url('/biblioteca-escolar/emprestimos') }}">
    @csrf

    <section class="bib-emp-passo">
        <p class="bib-emp-passo-num">1 · Livro</p>
        @include('buriti-ui::select-busca', [
            'campo' => 'obra_id',
            'rotulo' => 'Título disponível',
            'icone' => 'fa-book',
            'opcoes' => collect($obras)->filter(fn ($o) => ($o['disponiveis'] ?? 0) >= 1)->map(fn ($o) => [
                'id' => $o['id'],
                'nome' => $o['titulo'],
                'meta' => ($o['disponiveis'] ?? 0).' disponível(is)',
            ])->values()->all(),
            'selecionado' => null,
            'placeholder' => 'Buscar título…',
            'vazio' => '— escolher o livro —',
            'obrigatorio' => true,
            'compacto' => true,
            'colWide' => true,
        ])
    </section>

    <section class="bib-emp-passo">
        <p class="bib-emp-passo-num">2 · Quem leva</p>
        @if(!empty($leitores))
            @include('buriti-ui::select-busca', [
                'campo' => 'leitor_id',
                'rotulo' => 'Aluno ou servidor',
                'icone' => 'fa-user',
                'opcoes' => $leitores,
                'selecionado' => null,
                'placeholder' => 'Buscar por nome…',
                'vazio' => '— escolher na lista —',
                'obrigatorio' => true,
                'compacto' => true,
                'colWide' => true,
            ])
        @else
            <label class="bib-emp-campo">
                <span>Nome completo</span>
                <input name="leitor" required maxlength="120" placeholder="Estudante ou servidor"
                       list="bib-leitores-fallback" autocomplete="off">
                <datalist id="bib-leitores-fallback">
                    @foreach(($filtroOpcoes ?? []) as $l)
                        <option value="{{ $l['nome'] }}"></option>
                    @endforeach
                </datalist>
                <small>Cadastro escolar indisponível — digite o nome.</small>
            </label>
        @endif
    </section>

    <section class="bib-emp-passo bib-emp-passo--prazo">
        <p class="bib-emp-passo-num">3 · Prazo</p>
        <label class="bib-emp-campo bib-emp-campo--curto">
            <span>Dias para devolução</span>
            <input type="number" name="dias" min="1" max="60" value="{{ $diasPadrao ?? 7 }}">
        </label>
        <p class="bib-emp-ajuda">
            Limite: <strong>{{ $limite ?? 3 }}</strong> exemplar(es) por leitor
            · <a href="{{ url('/configuracoes/modulos?modulo=biblioteca') }}">regras</a>
        </p>
    </section>

    <div class="bib-emp-acoes">
        <button type="button" class="botao claro" data-buriti-fechar>Cancelar</button>
        <button class="botao" type="submit"><i class="fa fa-check"></i> Confirmar empréstimo</button>
    </div>
</form>
