<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\CatalogosController;
use App\Http\Controllers\VersionesController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\CoincidenciasController;
use App\Http\Controllers\EvaluacionesController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\DecisionesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensibilidadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Inicio: redirige al login (o al tablero si ya hay sesión)
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

// Pantallas del sistema (requieren sesión iniciada)
Route::middleware(['auth', 'active', 'operation'])->group(function () {

    // Operación
    //Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    //Route::view('/evaluaciones', 'evaluaciones.index')->name('evaluaciones.index');
    //Route::view('/validaciones', 'evaluaciones.show')->name('evaluaciones.show');
    Route::get('/evaluaciones', [EvaluacionesController::class, 'index'])->name('evaluaciones.index');
    Route::post('/evaluaciones', [EvaluacionesController::class, 'store'])->name('evaluaciones.store');
    Route::get('/evaluaciones/{id}', [EvaluacionesController::class, 'show'])->name('evaluaciones.show');
    Route::post('/evaluaciones/{id}/calcular', [EvaluacionesController::class, 'calcular'])->name('evaluaciones.calcular');
    Route::post('/evaluaciones/{id}/cerrar', [EvaluacionesController::class, 'cerrar'])->name('evaluaciones.cerrar');
    //Route::view('/eventos', 'eventos.create')->name('eventos.create');
    //Route::view('/eventos/calificar', 'eventos.calificar')->name('eventos.calificar');
    Route::get('/eventos/crear', [EventosController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventosController::class, 'store'])->name('eventos.store');
    Route::get('/eventos/{id}/calificar', [EventosController::class, 'calificar'])->name('eventos.calificar');
    Route::post('/eventos/{id}/calificaciones', [EventosController::class, 'guardarCalificaciones'])->name('calificaciones.store');
    //Route::view('/resultados', 'resultados.show')->name('resultados.show');
    Route::get('/resultados/{codigo}', [ResultadosController::class, 'show'])->name('resultados.show');
    Route::post('/resultados/{codigo}', [ResultadosController::class, 'store'])->name('resultados.store');
    //Route::view('/decisiones', 'decisiones.create')->name('decisiones.create');
    Route::get('/decisiones/crear', [DecisionesController::class, 'create'])->name('decisiones.create');
    Route::post('/decisiones', [DecisionesController::class, 'store'])->name('decisiones.store');
    //Route::view('/sensibilidad', 'sensibilidad.index')->name('sensibilidad.index');
    Route::get('/sensibilidad', [SensibilidadController::class, 'index'])->name('sensibilidad.index');
    Route::post('/sensibilidad/simular', [SensibilidadController::class, 'simular'])->name('sensibilidad.simular');
    //Route::view('/clientes', 'clientes.index')->name('clientes.index');
    //Route::view('/clientes/crear', 'clientes.create')->name('clientes.create');
    //Route::view('/clientes/editar', 'clientes.edit')->name('clientes.edit');
    Route::resource('clientes', ClientesController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    //Route::view('/coincidencias', 'coincidencias.index')->name('coincidencias.index');
    Route::get('/coincidencias', [CoincidenciasController::class, 'index'])->name('coincidencias.index');
    Route::put('/coincidencias/{id}', [CoincidenciasController::class, 'update'])->name('coincidencias.update');
    //Route::view('/versiones', 'versiones.index')->name('versiones.index');
    Route::get('/versiones', [VersionesController::class, 'index'])->name('versiones.index');
    Route::post('/versiones', [VersionesController::class, 'store'])->name('versiones.store');
    //Route::view('/versiones/editar', 'versiones.edit')->name('versiones.edit');
    //Route::view('/versiones/revisar', 'versiones.revisar')->name('versiones.revisar');
    Route::get('/versiones/revisar/{id?}', [VersionesController::class, 'revisar'])->name('versiones.revisar');
    Route::post('/versiones/{id}/decidir', [VersionesController::class, 'decidir'])->name('versiones.decidir');
    Route::get('/versiones/editar/{id?}', [VersionesController::class, 'edit'])->name('versiones.edit');
    Route::put('/versiones/{id}', [VersionesController::class, 'update'])->name('versiones.update');
    Route::get('/versiones/{id}/ver', [VersionesController::class, 'ver'])->name('versiones.ver');
    //Route::view('/usuarios', 'usuarios.index')->name('usuarios.index');
    Route::resource('usuarios', UsuariosController::class)->only(['index', 'store', 'update', 'destroy']);
    //Route::view('/bitacora', 'bitacora.index')->name('bitacora.index');
    Route::get('/bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('/bitacora/verificar', [BitacoraController::class, 'verificar'])->name('bitacora.verificar');
    //Route::view('/catalogos', 'catalogos.index')->name('catalogos.index');
    Route::get('/catalogos', [CatalogosController::class, 'index'])->name('catalogos.index');
    Route::post('/catalogos', [CatalogosController::class, 'store'])->name('catalogos.store');
    Route::post('/catalogos/factores', [CatalogosController::class, 'storeFactor'])->name('catalogos.factores.store');
    Route::post('/catalogos/agravantes', [CatalogosController::class, 'storeAgravante'])->name('catalogos.agravantes.store');
    Route::patch('/catalogos/{tipo}/{codigo}/activo', [CatalogosController::class, 'activo'])->name('catalogos.activo');
    Route::post('/catalogos/version', [CatalogosController::class, 'crearVersion'])->name('catalogos.version');

    // Perfil (viene con Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
