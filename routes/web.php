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
use App\Http\Controllers\Staff\ReporteController;
use App\Http\Controllers\Staff\MiPerfilController;
use App\Http\Controllers\Staff\PersonasController;
use App\Http\Controllers\Staff\RemitoController;
use App\Http\Controllers\Cliente\MiPerfilController as ClienteMiPerfilController;
use App\Http\Controllers\Staff\CategoriaProductoController;
use App\Http\Controllers\Staff\RenovacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (sin login, o con login opcional de cliente más adelante)
|--------------------------------------------------------------------------
*/

// Home público / buscador de empresas
Route::get('/', [\App\Http\Controllers\Publico\BuscadorController::class, 'index'])->name('home');

// Perfil público del negocio
Route::get('/negocio/{empresa:slug}', [EmpresaPublicaController::class, 'show'])->name('publico.empresa');

// Flujo público de reserva de turno
Route::get('/negocio/{empresa:slug}/reservar', [ReservaController::class, 'iniciar'])->name('publico.reserva.iniciar');
Route::get('/negocio/{empresa:slug}/horarios-disponibles', [ReservaController::class, 'horariosDisponibles'])->name('publico.reserva.horarios');
Route::post('/negocio/{empresa:slug}/reservar', [ReservaController::class, 'confirmar'])->name('publico.reserva.confirmar');

// Flujo público de carrito y compra de productos
Route::get('/negocio/{empresa:slug}/carrito', [CarritoController::class, 'index'])->name('publico.carrito.index');
Route::post('/negocio/{empresa:slug}/carrito/agregar', [CarritoController::class, 'agregar'])->name('publico.carrito.agregar');
Route::post('/negocio/{empresa:slug}/carrito/{producto}/actualizar', [CarritoController::class, 'actualizar'])->name('publico.carrito.actualizar');
Route::post('/negocio/{empresa:slug}/carrito/{producto}/quitar', [CarritoController::class, 'quitar'])->name('publico.carrito.quitar');
Route::get('/negocio/{empresa:slug}/comprar', [PedidoController::class, 'iniciar'])->name('publico.pedido.iniciar');
Route::post('/negocio/{empresa:slug}/comprar', [PedidoController::class, 'confirmar'])->name('publico.pedido.confirmar');

// PDFs de remito/comprobante (públicos pero protegidos con firma temporal, no requieren login)
Route::get('/remitos/pedido/{pedido}', [RemitoController::class, 'verPedido'])->name('remitos.pedido')->middleware('signed');
Route::get('/remitos/turno/{turno}', [RemitoController::class, 'verTurno'])->name('remitos.turno')->middleware('signed');

/*
|--------------------------------------------------------------------------
| AUTH DE PERSONAL (super_admin, admin, operador) - guard 'web'
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->name('staff.')->group(function () {

    // --- Login / recuperación de contraseña (público) ---
    Route::get('/olvide-password', [StaffPasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/olvide-password', [StaffPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/resetear-password/{token}', [StaffPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/resetear-password', [StaffPasswordController::class, 'reset'])->name('password.update');
    Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StaffAuthController::class, 'login']);
    Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');

    // --- Registro de empresa nueva (público) ---
    Route::get('/registro-empresa', [EmpresaRegisterController::class, 'showRegister'])->name('registro-empresa');
    Route::post('/registro-empresa', [EmpresaRegisterController::class, 'register']);

    // --- Todo lo de acá abajo requiere estar logueado y empresa activa ---
    Route::middleware(['auth:web', 'empresa.activa'])->group(function () {

        // Accesible para CUALQUIER staff logueado (admin, operador o super_admin)
        Route::get('/mi-perfil', [MiPerfilController::class, 'edit'])->name('mi-perfil.edit');
        Route::put('/mi-perfil', [MiPerfilController::class, 'update'])->name('mi-perfil.update');
        Route::put('/mi-perfil/password', [MiPerfilController::class, 'actualizarPassword'])->name('mi-perfil.password');

        // ==============================
        // SOLO SUPER_ADMIN (dueño de la plataforma)
        // ==============================
        Route::middleware(['rol:super_admin'])->group(function () {
            Route::get('/plataforma/dashboard', function () {
                return view('staff.plataforma-dashboard');
            })->name('plataforma.dashboard');            

            // Gestión de empresas registradas
            Route::get('/plataforma/empresas', [EmpresaAdminController::class, 'index'])->name('plataforma.empresas.index');
            Route::post('/plataforma/empresas/{empresa}/activar', [EmpresaAdminController::class, 'activar'])->name('plataforma.empresas.activar');
            Route::post('/plataforma/empresas/{empresa}/rechazar', [EmpresaAdminController::class, 'rechazar'])->name('plataforma.empresas.rechazar');
            Route::post('/plataforma/empresas/{empresa}/suspender', [EmpresaAdminController::class, 'suspender'])->name('plataforma.empresas.suspender');
            Route::post('/plataforma/empresas/{empresa}/reactivar', [EmpresaAdminController::class, 'reactivar'])->name('plataforma.empresas.reactivar');

            // Listados de clientes y staff, con generación manual de link de recuperación por WhatsApp
            Route::get('/plataforma/clientes', [PersonasController::class, 'clientes'])->name('plataforma.clientes.index');
            Route::get('/plataforma/clientes/{cliente}/recuperar', [PersonasController::class, 'generarLinkCliente'])->name('plataforma.clientes.recuperar');
            Route::get('/plataforma/staff', [PersonasController::class, 'staff'])->name('plataforma.staff.index');
            Route::get('/plataforma/staff/{usuario}/recuperar', [PersonasController::class, 'generarLinkStaff'])->name('plataforma.staff.recuperar');
        });

        // ==============================
        // ADMIN Y OPERADOR (tareas del día a día de la empresa)
        // ==============================
        Route::middleware(['rol:admin,operador'])->group(function () {
            Route::get('/empresa/dashboard', function () {
                return view('staff.empresa-dashboard');
            })->name('empresa.dashboard');

            // Pedidos
            Route::get('/empresa/pedidos', [StaffPedidoController::class, 'index'])->name('empresa.pedidos.index');
            Route::post('/empresa/pedidos/{pedido}/estado', [StaffPedidoController::class, 'cambiarEstado'])->name('empresa.pedidos.estado');
            Route::get('/empresa/pedidos/{pedido}/remito', [RemitoController::class, 'enviarPedido'])->name('empresa.pedidos.remito');

            // Turnos
            Route::get('/empresa/turnos', [TurnoController::class, 'index'])->name('empresa.turnos.index');
            Route::get('/empresa/turnos/{turno}/editar', [TurnoController::class, 'edit'])->name('empresa.turnos.edit');
            Route::put('/empresa/turnos/{turno}', [TurnoController::class, 'update'])->name('empresa.turnos.update');
            Route::post('/empresa/turnos/{turno}/estado', [TurnoController::class, 'cambiarEstado'])->name('empresa.turnos.estado');
            Route::get('/empresa/turnos/{turno}/remito', [RemitoController::class, 'enviarTurno'])->name('empresa.turnos.remito');
            Route::get('/empresa/renovaciones', [RenovacionController::class, 'index'])->name('empresa.renovaciones.index');
            
            // Reseteo de contraseña de operador (lo hace un compañero admin, ver abajo en rol:admin también)
            Route::post('/empresa/operadores/{usuario}/password', [OperadorController::class, 'resetearPassword'])->name('empresa.operadores.password');

            // Edición de datos propios de un operador (admin puede editar cualquiera; el propio operador podría editar los suyos si se habilita)
            Route::get('/empresa/operadores/{usuario}/editar', [OperadorController::class, 'edit'])->name('empresa.operadores.edit');
            Route::put('/empresa/operadores/{usuario}', [OperadorController::class, 'update'])->name('empresa.operadores.update');

            // Horarios de profesionales (solo lo gestiona el admin en la práctica, pero la ruta permite ambos roles)
            Route::get('/empresa/profesionales/{profesional}/horarios', [ProfesionalHorarioController::class, 'edit'])->name('empresa.profesionales.horarios.edit');
            Route::post('/empresa/profesionales/{profesional}/horarios', [ProfesionalHorarioController::class, 'store'])->name('empresa.profesionales.horarios.store');
            Route::delete('/empresa/profesionales/{profesional}/horarios/{horario}', [ProfesionalHorarioController::class, 'destroy'])->name('empresa.profesionales.horarios.destroy');
        });

        // ==============================
        // SOLO ADMIN (configuración y gestión del negocio)
        // ==============================
        Route::middleware(['rol:admin'])->group(function () {
            Route::get('/empresa/categorias-productos', [CategoriaProductoController::class, 'index'])->name('empresa.categorias.index');
            Route::post('/empresa/categorias-productos', [CategoriaProductoController::class, 'store'])->name('empresa.categorias.store');
            Route::put('/empresa/categorias-productos/{categoria}', [CategoriaProductoController::class, 'update'])->name('empresa.categorias.update');
            Route::delete('/empresa/categorias-productos/{categoria}', [CategoriaProductoController::class, 'destroy'])->name('empresa.categorias.destroy');
            
            // Reportes
            Route::get('/empresa/reportes/comisiones', [ReporteController::class, 'comisiones'])->name('empresa.reportes.comisiones');
            Route::get('/empresa/reportes/pedidos', [ReporteController::class, 'pedidos'])->name('empresa.reportes.pedidos');

            // Productos
            Route::get('/empresa/productos', [ProductoController::class, 'index'])->name('empresa.productos.index');
            Route::get('/empresa/productos/nuevo', [ProductoController::class, 'create'])->name('empresa.productos.create');
            Route::post('/empresa/productos', [ProductoController::class, 'store'])->name('empresa.productos.store');
            Route::get('/empresa/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('empresa.productos.edit');
            Route::put('/empresa/productos/{producto}', [ProductoController::class, 'update'])->name('empresa.productos.update');
            Route::post('/empresa/productos/{producto}/alternar', [ProductoController::class, 'alternarEstado'])->name('empresa.productos.alternar');

            // Branding (logo / fondo)
            Route::get('/empresa/branding', [EmpresaBrandingController::class, 'edit'])->name('empresa.branding.edit');
            Route::post('/empresa/branding', [EmpresaBrandingController::class, 'update'])->name('empresa.branding.update');

            // Dirección de la empresa
            Route::get('/empresa/direccion', [EmpresaDireccionController::class, 'edit'])->name('empresa.direccion.edit');
            Route::post('/empresa/direccion', [EmpresaDireccionController::class, 'update'])->name('empresa.direccion.update');

            // Gestión de operadores
            Route::get('/empresa/operadores', [OperadorController::class, 'index'])->name('empresa.operadores.index');
            Route::get('/empresa/operadores/nuevo', [OperadorController::class, 'create'])->name('empresa.operadores.create');
            Route::post('/empresa/operadores', [OperadorController::class, 'store'])->name('empresa.operadores.store');
            Route::post('/empresa/operadores/{usuario}/alternar', [OperadorController::class, 'alternarEstado'])->name('empresa.operadores.alternar');

            // Gestión de servicios
            Route::get('/empresa/servicios', [ServicioController::class, 'index'])->name('empresa.servicios.index');
            Route::get('/empresa/servicios/nuevo', [ServicioController::class, 'create'])->name('empresa.servicios.create');
            Route::post('/empresa/servicios', [ServicioController::class, 'store'])->name('empresa.servicios.store');
            Route::get('/empresa/servicios/{servicio}/editar', [ServicioController::class, 'edit'])->name('empresa.servicios.edit');
            Route::put('/empresa/servicios/{servicio}', [ServicioController::class, 'update'])->name('empresa.servicios.update');
            Route::post('/empresa/servicios/{servicio}/alternar', [ServicioController::class, 'alternarEstado'])->name('empresa.servicios.alternar');

            // Gestión de profesionales
            Route::get('/empresa/profesionales', [ProfesionalController::class, 'index'])->name('empresa.profesionales.index');
            Route::get('/empresa/profesionales/nuevo', [ProfesionalController::class, 'create'])->name('empresa.profesionales.create');
            Route::post('/empresa/profesionales', [ProfesionalController::class, 'store'])->name('empresa.profesionales.store');
            Route::get('/empresa/profesionales/{profesional}/editar', [ProfesionalController::class, 'edit'])->name('empresa.profesionales.edit');
            Route::put('/empresa/profesionales/{profesional}', [ProfesionalController::class, 'update'])->name('empresa.profesionales.update');
            Route::post('/empresa/profesionales/{profesional}/alternar', [ProfesionalController::class, 'alternarEstado'])->name('empresa.profesionales.alternar');

            // Horario general del negocio (heredado por profesionales sin horario propio)
            Route::get('/empresa/horario-general', [EmpresaHorarioController::class, 'edit'])->name('empresa.horario-general.edit');
            Route::post('/empresa/horario-general', [EmpresaHorarioController::class, 'store'])->name('empresa.horario-general.store');
            Route::delete('/empresa/horario-general/{horario}', [EmpresaHorarioController::class, 'destroy'])->name('empresa.horario-general.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| AUTH DE CLIENTES (público) - guard 'cliente'
|--------------------------------------------------------------------------
*/
Route::prefix('cuenta')->name('cliente.')->group(function () {

    // --- Login / recuperación de contraseña / registro (público) ---
    Route::get('/olvide-password', [ClientePasswordController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/olvide-password', [ClientePasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/resetear-password/{token}', [ClientePasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/resetear-password', [ClientePasswordController::class, 'reset'])->name('password.update');
    Route::get('/login', [ClienteAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [ClienteAuthController::class, 'login']);
    Route::get('/registro', [ClienteAuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [ClienteAuthController::class, 'register']);
    Route::post('/logout', [ClienteAuthController::class, 'logout'])->name('logout');

    // --- Requiere estar logueado como cliente ---
    Route::middleware('auth:cliente')->group(function () {
        Route::get('/', function () {
            return view('cliente.home');
        })->name('home');
        Route::get('/mi-perfil', [ClienteMiPerfilController::class, 'edit'])->name('mi-perfil.edit');
        Route::put('/mi-perfil', [ClienteMiPerfilController::class, 'update'])->name('mi-perfil.update');
        Route::put('/mi-perfil/password', [ClienteMiPerfilController::class, 'actualizarPassword'])->name('mi-perfil.password');

        Route::get('/mis-pedidos', [\App\Http\Controllers\Cliente\MisPedidosController::class, 'index'])->name('pedidos.index');
        Route::get('/pedidos/{pedido}', [PedidoController::class, 'confirmado'])->name('pedidos.confirmado');

        Route::get('/mis-turnos', [MisTurnosController::class, 'index'])->name('turnos.index');
        Route::post('/mis-turnos/{turno}/cancelar', [MisTurnosController::class, 'cancelar'])->name('turnos.cancelar');
        Route::get('/turnos/{turno}', [ReservaController::class, 'confirmado'])->name('turnos.confirmado');
    });
});