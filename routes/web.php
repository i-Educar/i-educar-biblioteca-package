<?php

use iEducar\Packages\BuritiBiblioteca\Http\Controllers\BibliotecaController;
use Illuminate\Support\Facades\Route;

Route::prefix('biblioteca-escolar')->group(function (): void {
    Route::get('/', [BibliotecaController::class, 'index']);
    Route::get('/acervo', [BibliotecaController::class, 'acervo']);
    Route::post('/acervo', [BibliotecaController::class, 'salvarAcervo']);
    Route::get('/emprestimos', [BibliotecaController::class, 'emprestimos']);
    Route::post('/emprestimos', [BibliotecaController::class, 'salvarEmprestimo']);
    Route::post('/emprestimos/{id}/devolver', [BibliotecaController::class, 'devolver'])->whereNumber('id');
    Route::get('/emprestimos/{id}/comprovante', [BibliotecaController::class, 'comprovante'])->whereNumber('id');
    Route::get('/config', [BibliotecaController::class, 'config']);
    Route::post('/config', [BibliotecaController::class, 'salvarConfig']);
});
