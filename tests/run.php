#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__.'/../src/Services/Emprestimo.php';
require __DIR__.'/../src/Services/ConsultaAcervo.php';

$ok=0; $fail=0;
$assert = static function(bool $c, string $m) use (&$ok,&$fail): void {
  echo ($c? '  OK  ':' FAIL ').$m."\n";
  $c ? $ok++ : $fail++;
};

$assert(\iEducar\Packages\BuritiBiblioteca\Services\Emprestimo::podeEmprestar(2,1)===true, 'pode emprestar');
$assert(\iEducar\Packages\BuritiBiblioteca\Services\Emprestimo::atrasado('2026-01-01','2026-01-02')===true, 'atrasado');
$b = \iEducar\Packages\BuritiBiblioteca\Services\ConsultaAcervo::buscar([['titulo'=>'Dom Casmurro','autor'=>'Machado','disponivel'=>true]], 'casmurro');
$assert(count($b)===1, 'consulta acervo');

echo "\nResultado: $ok ok, $fail falhas\n";
exit($fail>0?1:0);
