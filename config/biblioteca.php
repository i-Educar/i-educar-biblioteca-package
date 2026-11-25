<?php

return [
    'public' => [
        'aparecer_na_home' => false,
        'titulo' => 'Biblioteca escolar',
        'ordem' => 55,
        'url' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Regras operacionais (PoC / Laguna)
    |--------------------------------------------------------------------------
    |
    | Valores padrão; a tela /biblioteca-escolar/config pode sobrescrever
    | em biblioteca.poc_registro (tipo=config) sem alterar .env.
    |
    */
    'limite_simultaneo' => (int) env('BIBLIOTECA_LIMITE_SIMULTANEO', 3),
    'dias_padrao' => (int) env('BIBLIOTECA_DIAS_PADRAO', 7),
    'bloquear_atraso' => (bool) env('BIBLIOTECA_BLOQUEAR_ATRASO', true),
];
