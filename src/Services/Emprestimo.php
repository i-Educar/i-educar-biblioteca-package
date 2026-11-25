<?php

namespace iEducar\Packages\BuritiBiblioteca\Services;

final class Emprestimo
{
    public static function podeEmprestar(int $exemplaresDisponiveis, int $emprestimosAtivos, int $limite = 3): bool
    {
        return $exemplaresDisponiveis > 0 && $emprestimosAtivos < $limite;
    }

    public static function atrasado(string $devolucaoPrevista, ?string $hoje = null): bool
    {
        $hoje = $hoje ?? date('Y-m-d');

        return $hoje > $devolucaoPrevista;
    }

    /**
     * @param  list<array{payload:array<string,mixed>}>  $emprestimosDoLeitor
     */
    public static function leitorComAtraso(array $emprestimosDoLeitor, ?string $hoje = null): bool
    {
        foreach ($emprestimosDoLeitor as $e) {
            $p = $e['payload'] ?? [];
            if (! empty($p['devolvido_em'])) {
                continue;
            }
            $prevista = (string) ($p['devolucao_prevista'] ?? '');
            if ($prevista !== '' && self::atrasado($prevista, $hoje)) {
                return true;
            }
        }

        return false;
    }
}
