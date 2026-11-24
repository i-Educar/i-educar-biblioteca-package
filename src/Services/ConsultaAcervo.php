<?php

namespace iEducar\Packages\BuritiBiblioteca\Services;

final class ConsultaAcervo
{
    /** @param list<array{titulo:string,autor:string,disponivel:bool}> $acervo */
    public static function buscar(array $acervo, string $q): array {
        $q = mb_strtolower($q);
        return array_values(array_filter($acervo, fn($i)=> str_contains(mb_strtolower($i['titulo'].' '.$i['autor']), $q)));
    }
}
