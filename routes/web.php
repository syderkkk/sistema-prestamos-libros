<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\SetupLibrosController;
use App\Http\Controllers\SetupUsuariosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::resource('libros', LibroController::class);

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');


// Rutas de configuración
Route::get('/setup/usuarios', [SetupUsuariosController::class, 'setupUsuarios'])->name('setup.usuarios');
Route::get('/setup/libros', [SetupLibrosController::class, 'setupLibros'])->name('setup.libros');


// Ruta para ver los logs (depuración)
Route::get('/ver-logs', function() {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $lines = explode("\n", $logs);
        $lastLines = array_slice($lines, -50); // Últimas 50 líneas
        return '<pre>' . implode("\n", $lastLines) . '</pre>';
    }
    return 'No hay archivo de log';
});