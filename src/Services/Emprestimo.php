<?php

namespace iEducar\Packages\BuritiBiblioteca\Services;

final class Emprestimo
{
    public static function podeEmprestar(int $exemplaresDisponiveis, int $emprestimosAtivos, int $limite = 3): bool {
        return $exemplaresDisponiveis > 0 && $emprestimosAtivos < $limite;
    }
    public static function atrasado(string $devolucaoPrevista, ?string $hoje = null): bool {
        $hoje = $hoje ?? date('Y-m-d');
        return $hoje > $devolucaoPrevista;
    }
}
