<?php

use App\Http\Controllers\Publico\EmpresaPublicaController;
use App\Http\Controllers\Staff\EmpresaBrandingController;
use App\Http\Controllers\Staff\EmpresaAdminController;
use App\Http\Controllers\Auth\ClienteAuthController;
use App\Http\Controllers\Auth\EmpresaRegisterController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Staff\EmpresaDireccionController;
use App\Http\Controllers\Staff\OperadorController;
use App\Http\Controllers\Staff\ServicioController;
use App\Http\Controllers\Staff\ProfesionalController;
use App\Http\Controllers\Staff\ProfesionalHorarioController;
use App\Http\Controllers\Staff\EmpresaHorarioController;
use App\Http\Controllers\Publico\ReservaController;
use App\Http\Controllers\Staff\TurnoController;
use App\Http\Controllers\Cliente\MisTurnosController;
use App\Http\Controllers\Auth\ClientePasswordController;
use App\Http\Controllers\Auth\StaffPasswordController;
use App\Http\Controllers\Staff\ProductoController;
use App\Http\Controllers\Publico\CarritoController;
use App\Http\Controllers\Publico\PedidoController;
use App\Http\Controllers\Staff\PedidoController as StaffPedidoController;
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
Route::get('/negocio/{empresa:slug}/reservar', [ReservaController::class, 'iniciar'])->name('publico.reserva.iniciar');
Route::get('/negocio/{empresa:slug}/horarios-disponibles', [ReservaController::class, 'horariosDisponibles'])->name('publico.reserva.horarios');
Route::post('/negocio/{empresa:slug}/reservar', [ReservaController::class, 'confirmar'])->name('publico.reserva.confirmar');
Route::get('/negocio/{empresa:slug}/carrito', [CarritoController::class, 'index'])->name('publico.carrito.index');
Route::post('/negocio/{empresa:slug}/carrito/agregar', [CarritoController::class, 'agregar'])->name('publico.carrito.agregar');
Route::post('/negocio/{empresa:slug}/carrito/{producto}/actualizar', [CarritoController::class, 'actualizar'])->name('publico.carrito.actualizar');
Route::post('/negocio/{empresa:slug}/carrito/{producto}/quitar', [CarritoController::class, 'quitar'])->name('publico.carrito.quitar');

Route::get('/negocio/{empresa:slug}/comprar', [PedidoController::class, 'iniciar'])->name('publico.pedido.iniciar');
Route::post('/negocio/{empresa:slug}/comprar', [PedidoController::class, 'confirmar'])->name('publico.pedido.confirmar');

/*
|--------------------------------------------------------------------------
| Auth de personal (super_admin, admin, operador) - guard 'web'
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('/olvide-password', [StaffPasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/olvide-password', [StaffPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/resetear-password/{token}', [StaffPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/resetear-password', [StaffPasswordController::class, 'reset'])->name('password.update');
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
            Route::get('/empresa/pedidos', [StaffPedidoController::class, 'index'])->name('empresa.pedidos.index');
            Route::post('/empresa/pedidos/{pedido}/estado', [StaffPedidoController::class, 'cambiarEstado'])->name('empresa.pedidos.estado');
            Route::post('/empresa/operadores/{usuario}/password', [OperadorController::class, 'resetearPassword'])->name('empresa.operadores.password');
            Route::get('/empresa/turnos', [TurnoController::class, 'index'])->name('empresa.turnos.index');
            Route::get('/empresa/turnos/{turno}/editar', [TurnoController::class, 'edit'])->name('empresa.turnos.edit');
            Route::put('/empresa/turnos/{turno}', [TurnoController::class, 'update'])->name('empresa.turnos.update');
            Route::post('/empresa/turnos/{turno}/estado', [TurnoController::class, 'cambiarEstado'])->name('empresa.turnos.estado');
        });

        Route::middleware(['rol:admin'])->group(function () {
            Route::get('/empresa/productos', [ProductoController::class, 'index'])->name('empresa.productos.index');
            Route::get('/empresa/productos/nuevo', [ProductoController::class, 'create'])->name('empresa.productos.create');
            Route::post('/empresa/productos', [ProductoController::class, 'store'])->name('empresa.productos.store');
            Route::get('/empresa/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('empresa.productos.edit');
            Route::put('/empresa/productos/{producto}', [ProductoController::class, 'update'])->name('empresa.productos.update');
            Route::post('/empresa/productos/{producto}/alternar', [ProductoController::class, 'alternarEstado'])->name('empresa.productos.alternar');
            Route::get('/empresa/branding', [EmpresaBrandingController::class, 'edit'])->name('empresa.branding.edit');
            Route::post('/empresa/branding', [EmpresaBrandingController::class, 'update'])->name('empresa.branding.update');
            Route::get('/empresa/direccion', [EmpresaDireccionController::class, 'edit'])->name('empresa.direccion.edit');
            Route::post('/empresa/direccion', [EmpresaDireccionController::class, 'update'])->name('empresa.direccion.update');
            Route::get('/empresa/operadores', [OperadorController::class, 'index'])->name('empresa.operadores.index');
            Route::get('/empresa/operadores/nuevo', [OperadorController::class, 'create'])->name('empresa.operadores.create');
            Route::post('/empresa/operadores', [OperadorController::class, 'store'])->name('empresa.operadores.store');
            Route::post('/empresa/operadores/{usuario}/alternar', [OperadorController::class, 'alternarEstado'])->name('empresa.operadores.alternar');
            Route::get('/empresa/servicios', [ServicioController::class, 'index'])->name('empresa.servicios.index');
            Route::get('/empresa/servicios/nuevo', [ServicioController::class, 'create'])->name('empresa.servicios.create');
            Route::post('/empresa/servicios', [ServicioController::class, 'store'])->name('empresa.servicios.store');
            Route::get('/empresa/servicios/{servicio}/editar', [ServicioController::class, 'edit'])->name('empresa.servicios.edit');
            Route::put('/empresa/servicios/{servicio}', [ServicioController::class, 'update'])->name('empresa.servicios.update');
            Route::post('/empresa/servicios/{servicio}/alternar', [ServicioController::class, 'alternarEstado'])->name('empresa.servicios.alternar');
            Route::get('/empresa/profesionales', [ProfesionalController::class, 'index'])->name('empresa.profesionales.index');
            Route::get('/empresa/profesionales/nuevo', [ProfesionalController::class, 'create'])->name('empresa.profesionales.create');
            Route::post('/empresa/profesionales', [ProfesionalController::class, 'store'])->name('empresa.profesionales.store');
            Route::get('/empresa/profesionales/{profesional}/editar', [ProfesionalController::class, 'edit'])->name('empresa.profesionales.edit');
            Route::put('/empresa/profesionales/{profesional}', [ProfesionalController::class, 'update'])->name('empresa.profesionales.update');
            Route::post('/empresa/profesionales/{profesional}/alternar', [ProfesionalController::class, 'alternarEstado'])->name('empresa.profesionales.alternar');
            Route::get('/empresa/horario-general', [EmpresaHorarioController::class, 'edit'])->name('empresa.horario-general.edit');
            Route::post('/empresa/horario-general', [EmpresaHorarioController::class, 'store'])->name('empresa.horario-general.store');
            Route::delete('/empresa/horario-general/{horario}', [EmpresaHorarioController::class, 'destroy'])->name('empresa.horario-general.destroy');
        });

        Route::middleware(['rol:admin,operador'])->group(function () {
            Route::get('/empresa/profesionales/{profesional}/horarios', [ProfesionalHorarioController::class, 'edit'])->name('empresa.profesionales.horarios.edit');
            Route::post('/empresa/profesionales/{profesional}/horarios', [ProfesionalHorarioController::class, 'store'])->name('empresa.profesionales.horarios.store');
            Route::delete('/empresa/profesionales/{profesional}/horarios/{horario}', [ProfesionalHorarioController::class, 'destroy'])->name('empresa.profesionales.horarios.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Auth de clientes (público) - guard 'cliente'
|--------------------------------------------------------------------------
*/
Route::prefix('cuenta')->name('cliente.')->group(function () {
    Route::get('/olvide-password', [ClientePasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/olvide-password', [ClientePasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/resetear-password/{token}', [ClientePasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/resetear-password', [ClientePasswordController::class, 'reset'])->name('password.update');
    Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClienteAuthController::class, 'login']);
    Route::get('/registro', [ClienteAuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [ClienteAuthController::class, 'register']);
    Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:cliente')->group(function () {
        Route::get('/', function () {
            return view('cliente.home');
        })->name('home');
        Route::get('/mis-pedidos', [\App\Http\Controllers\Cliente\MisPedidosController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'confirmado'])->name('pedidos.confirmado');
        Route::get('/turnos/{turno}', [ReservaController::class, 'confirmado'])->name('turnos.confirmado');
        Route::get('/mis-turnos', [MisTurnosController::class, 'index'])->name('turnos.index');
        Route::post('/mis-turnos/{turno}/cancelar', [MisTurnosController::class, 'cancelar'])->name('turnos.cancelar');
    });
});