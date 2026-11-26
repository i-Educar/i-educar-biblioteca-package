<?php

namespace iEducar\Packages\BuritiBiblioteca\Support;

final class Processos
{
    /** Faixa própria — não reutilizar 881500–881502 (Exportação unificada). */
    public const int RAIZ = 881510;

    public const int PAINEL = 881511;

    public const int ACERVO = 881512;

    public const int EMPRESTIMOS = 881513;

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
