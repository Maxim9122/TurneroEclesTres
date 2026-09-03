<?php

use App\Http\Controllers\Publico\EmpresaPublicaController;
use App\Http\Controllers\Staff\EmpresaBrandingController;
use App\Http\Controllers\Staff\EmpresaAdminController;
use App\Http\Controllers\Auth\ClienteAuthController;
use App\Http\Controllers\Auth\EmpresaRegisterController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Staff\EmpresaDireccionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Home público
Route::get('/', [\App\Http\Controllers\Publico\BuscadorController::class, 'index'])->name('home');

// Perfil público del negocio / empresa
Route::get('/negocio/{empresa:slug}', [EmpresaPublicaController::class, 'show'])->name('publico.empresa');

/*
|--------------------------------------------------------------------------
| Auth de personal (super_admin, admin, operador) - guard 'web'
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StaffAuthController::class, 'login']);
    Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');

    Route::get('/registro-empresa', [EmpresaRegisterController::class, 'showRegister'])->name('registro-empresa');
    Route::post('/registro-empresa', [EmpresaRegisterController::class, 'register']);
    
    Route::middleware(['auth:web', 'empresa.activa'])->group(function () {
        Route::middleware(['rol:super_admin'])->group(function () {
            Route::get('/plataforma/dashboard', function () {
                return view('staff.plataforma-dashboard');
            })->name('plataforma.dashboard');

            Route::get('/plataforma/empresas', [EmpresaAdminController::class, 'index'])->name('plataforma.empresas.index');
            Route::post('/plataforma/empresas/{empresa}/activar', [EmpresaAdminController::class, 'activar'])->name('plataforma.empresas.activar');
            Route::post('/plataforma/empresas/{empresa}/rechazar', [EmpresaAdminController::class, 'rechazar'])->name('plataforma.empresas.rechazar');
            Route::post('/plataforma/empresas/{empresa}/suspender', [EmpresaAdminController::class, 'suspender'])->name('plataforma.empresas.suspender');
            Route::post('/plataforma/empresas/{empresa}/reactivar', [EmpresaAdminController::class, 'reactivar'])->name('plataforma.empresas.reactivar');
        });

        Route::middleware(['rol:admin,operador'])->group(function () {
            Route::get('/empresa/dashboard', function () {
                return view('staff.empresa-dashboard');
            })->name('empresa.dashboard');
        });

        Route::middleware(['rol:admin'])->group(function () {
            Route::get('/empresa/branding', [EmpresaBrandingController::class, 'edit'])->name('empresa.branding.edit');
            Route::post('/empresa/branding', [EmpresaBrandingController::class, 'update'])->name('empresa.branding.update');
            Route::get('/empresa/direccion', [EmpresaDireccionController::class, 'edit'])->name('empresa.direccion.edit');
            Route::post('/empresa/direccion', [EmpresaDireccionController::class, 'update'])->name('empresa.direccion.update');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Auth de clientes (público) - guard 'cliente'
|--------------------------------------------------------------------------
*/
Route::prefix('cuenta')->name('cliente.')->group(function () {
    Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClienteAuthController::class, 'login']);
    Route::get('/registro', [ClienteAuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [ClienteAuthController::class, 'register']);
    Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:cliente')->group(function () {
        Route::get('/', function () {
            return view('cliente.home');
        })->name('home');
    });
});