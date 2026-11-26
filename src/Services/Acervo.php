<?php

namespace iEducar\Packages\BuritiBiblioteca\Services;

/**
 * Regras de cadastro/edição de títulos do acervo.
 */
final class Acervo
{
    public const int EXEMPLARES_MAX = 9999;

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>|null  $atual  payload atual (update)
     * @param  int  $emprestados  exemplares atualmente emprestados
     * @return array{ok:bool,erro:?string,payload?:array<string,mixed>}
     */
    public static function validar(array $input, ?array $atual = null, int $emprestados = 0): array
    {
        $titulo = trim((string) ($input['titulo'] ?? ''));
        $autor = trim((string) ($input['autor'] ?? ''));
        $isbn = trim((string) ($input['isbn'] ?? ''));
        $editora = trim((string) ($input['editora'] ?? ''));
        $localizacao = trim((string) ($input['localizacao'] ?? ''));
        $rawEx = $input['exemplares'] ?? null;

        if ($titulo === '') {
            return ['ok' => false, 'erro' => 'Informe o título da obra.'];
        }
        if (mb_strlen($titulo) > 180) {
            return ['ok' => false, 'erro' => 'O título deve ter no máximo 180 caracteres.'];
        }
        if ($rawEx === null || $rawEx === '') {
            return ['ok' => false, 'erro' => 'Informe a quantidade de exemplares.'];
        }
        if (! is_numeric($rawEx)) {
            return ['ok' => false, 'erro' => 'Quantidade de exemplares inválida.'];
        }

        $exemplares = (int) $rawEx;
        if ($exemplares < 1) {
            return ['ok' => false, 'erro' => 'Informe ao menos 1 exemplar (não é permitido zero ou negativo).'];
        }
        if ($exemplares > self::EXEMPLARES_MAX) {
            return ['ok' => false, 'erro' => 'Quantidade de exemplares acima do limite (máx. '.self::EXEMPLARES_MAX.').'];
        }
        if ($emprestados < 0) {
            $emprestados = 0;
        }
        if ($exemplares < $emprestados) {
            return [
                'ok' => false,
                'erro' => "Não é possível reduzir para {$exemplares} exemplar(es): há {$emprestados} emprestado(s). Devolva ou aumente o total.",
            ];
        }

        $disponiveis = $exemplares - $emprestados;
        $ativo = $atual === null
            ? true
            : (($atual['ativo'] ?? true) !== false);

        return [
            'ok' => true,
            'erro' => null,
            'payload' => [
                'titulo' => $titulo,
                'autor' => $autor,
                'isbn' => $isbn,
                'editora' => $editora,
                'localizacao' => $localizacao,
                'exemplares' => $exemplares,
                'disponiveis' => $disponiveis,
                'ativo' => $ativo,
            ],
        ];
    }

    public static function estaAtivo(array $payload): bool
    {
        return ($payload['ativo'] ?? true) !== false;
    }

    public static function emprestados(array $payload): int
    {
        $ex = max(0, (int) ($payload['exemplares'] ?? 0));
        $disp = max(0, (int) ($payload['disponiveis'] ?? 0));

        return max(0, $ex - $disp);
    }
}
