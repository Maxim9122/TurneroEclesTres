<?php

use App\Support\Paneles;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rol' => \App\Http\Middleware\EnsureUsuarioRole::class,
            'empresa.activa' => \App\Http\Middleware\EnsureEmpresaActiva::class,
        ]);

        // Sin sesión en una página protegida → al login que corresponde (staff o cliente).
        $middleware->redirectGuestsTo(fn (Request $request) => Paneles::login($request));

        // Con sesión iniciada en el login/registro → directo a su panel principal.
        $middleware->redirectUsersTo(fn (Request $request) => Paneles::panel($request));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 419 "Página expirada": el formulario tenía un token de seguridad viejo, por ejemplo
        // porque se inició sesión desde otra ventana o desde la app instalada (comparten sesión).
        // En lugar de la pantalla de error, se vuelve a la página con un aviso para reintentar.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419 || !$e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if ($request->expectsJson()) {
                return null; // las llamadas fetch manejan su propio error
            }

            // Quería cerrar sesión: se cierra igual.
            foreach (['staff.logout' => 'web', 'cliente.logout' => 'cliente'] as $ruta => $guard) {
                if ($request->routeIs($ruta)) {
                    Auth::guard($guard)->logout();
                    $request->session()->regenerate();

                    return redirect()->route($guard === 'web' ? 'staff.login' : 'cliente.login');
                }
            }

            return redirect()->back(fallback: route('home'))
                ->withInput($request->except(['password', 'password_confirmation', 'password_actual', '_token']))
                ->withErrors(['sesion' => 'La página estaba desactualizada (por ejemplo, porque ingresaste desde otra ventana o desde la app). Ya la actualizamos: volvé a intentarlo.']);
        });
    })->create();
