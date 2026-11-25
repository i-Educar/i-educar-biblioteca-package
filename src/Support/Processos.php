<?php

namespace iEducar\Packages\BuritiBiblioteca\Support;

final class Processos
{
    public const int RAIZ = 881500;

    public const int PAINEL = 881501;

    public const int ACERVO = 881502;

    public const int EMPRESTIMOS = 881503;

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Biblioteca',
            self::PAINEL => 'Painel',
            self::ACERVO => 'Acervo',
            self::EMPRESTIMOS => 'Empréstimos',
        ];
    }
}
