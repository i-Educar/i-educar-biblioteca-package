<?php

use iEducar\Packages\BuritiBiblioteca\Http\Controllers\BibliotecaController;
use Illuminate\Support\Facades\Route;

Route::prefix('biblioteca-escolar')->group(function (): void {
    Route::get('/', [BibliotecaController::class, 'index']);
    Route::get('/acervo', [BibliotecaController::class, 'acervo']);
    Route::post('/acervo', [BibliotecaController::class, 'salvarAcervo']);
    Route::get('/acervo/{id}/editar', [BibliotecaController::class, 'editarAcervo'])->whereNumber('id');
    Route::put('/acervo/{id}', [BibliotecaController::class, 'atualizarAcervo'])->whereNumber('id');
    Route::post('/acervo/{id}/excluir', [BibliotecaController::class, 'excluirAcervo'])->whereNumber('id');
    Route::get('/emprestimos', [BibliotecaController::class, 'emprestimos']);
    Route::post('/emprestimos/exportar', [BibliotecaController::class, 'exportarEmprestimos']);
    Route::post('/emprestimos', [BibliotecaController::class, 'salvarEmprestimo']);
    Route::post('/emprestimos/{id}/devolver', [BibliotecaController::class, 'devolver'])->whereNumber('id');
    Route::get('/emprestimos/{id}/comprovante', [BibliotecaController::class, 'comprovante'])->whereNumber('id');
    Route::post('/emprestimos/{id}/comprovante/exportar', [BibliotecaController::class, 'exportarComprovante'])->whereNumber('id');
    Route::get('/emprestimos/{id}/etiqueta', [BibliotecaController::class, 'etiqueta'])->whereNumber('id');
    Route::get('/emprestimos/{id}/vias', [BibliotecaController::class, 'vias'])->whereNumber('id');
    Route::get('/config', [BibliotecaController::class, 'config']);
    Route::post('/config', [BibliotecaController::class, 'salvarConfig']);
});
