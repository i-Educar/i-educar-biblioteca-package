<?php

namespace iEducar\Packages\BuritiBiblioteca\Support;

final class Identidade
{
    public const COR = '#1e4d7b';

    public const DESTAQUE = '#2f6fad';

    public const COR_CLARA = '#e8f1f8';

    public const ICONE = 'fa-book';

    public const NOME = 'Biblioteca da escola';

    public const DESCRICAO = 'Acervo, empréstimos e devoluções';

    /** @return array{cor:string,destaque:string,clara:string,icone:string,nome:string,descricao:string} */
    public static function tokens(): array
    {
        return [
            'cor' => self::COR,
            'destaque' => self::DESTAQUE,
            'clara' => self::COR_CLARA,
            'icone' => self::ICONE,
            'nome' => self::NOME,
            'descricao' => self::DESCRICAO,
        ];
    }
}
