<?php

namespace iEducar\Packages\BuritiBiblioteca\Support;

final class MenuFluxo
{
    /**
     * @param  array<string, bool>  $permite
     * @return list<array{nome:string,itens:list<array{rotulo:string,url:string,icone:string,chave:string}>}>
     */
    public static function grupos(array $permite): array
    {
        $item = static function (string $chave, string $rotulo, string $url, string $icone) use ($permite): ?array {
            if (! ($permite[$chave] ?? true)) {
                return null;
            }

            return compact('chave', 'rotulo', 'url', 'icone');
        };
        $filtrar = static fn (array $itens): array => array_values(array_filter($itens));

        return array_values(array_filter([
            [
                'nome' => 'Biblioteca',
                'itens' => $filtrar([
                    $item('painel', 'Início', '/biblioteca-escolar', 'fa-th-large'),
                    $item('acervo', 'Livros do acervo', '/biblioteca-escolar/acervo', 'fa-book'),
                    $item('emprestimos', 'Empréstimos e devoluções', '/biblioteca-escolar/emprestimos', 'fa-exchange'),
                ]),
            ],
            [
                'nome' => 'Configuração',
                'itens' => $filtrar([
                    $item('config', 'Prazos e regras', '/biblioteca-escolar/config', 'fa-cog'),
                ]),
            ],
        ], static fn (array $g): bool => $g['itens'] !== []));
    }
}
