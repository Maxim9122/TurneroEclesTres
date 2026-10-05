<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\WebauthnCredencial;
use App\Services\WebauthnService;
use App\Support\Paneles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Ingreso del staff con huella / rostro / patrón (llaves de acceso WebAuthn).
 */
class StaffWebauthnController extends Controller
{
    public function __construct(private WebauthnService $webauthn)
    {
    }

    // --- Login (sin sesión) ---

    public function opcionesIngreso(Request $request): JsonResponse
    {
        return $this->respuestaJson($this->webauthn->opcionesIngreso($request));
    }

    public function ingresar(Request $request): JsonResponse
    {
        $usuario = $this->verificando(fn () => $this->webauthn->verificarIngreso($request, $request->all()));

        if (!$usuario->activo) {
            return response()->json(['message' => 'Tu usuario está inactivo. Contactá al administrador de tu empresa.'], 422);
        }

        Auth::guard('web')->login($usuario);
        $request->session()->regenerate();

        return response()->json(['redirect' => Paneles::panel($request)]);
    }

    // --- Mi perfil (con sesión) ---

    public function opcionesRegistro(Request $request): JsonResponse
    {
        return $this->respuestaJson($this->webauthn->opcionesRegistro($request, Auth::guard('web')->user()));
    }

    public function registrar(Request $request): JsonResponse
    {
        $credencial = $this->verificando(fn () => $this->webauthn->registrar($request, Auth::guard('web')->user(), $request->all()));

        $request->session()->flash('status', "Listo: ya podés ingresar con huella desde {$credencial->nombre}.");

        return response()->json(['ok' => true]);
    }

    public function eliminar(WebauthnCredencial $credencial): RedirectResponse
    {
        abort_unless($credencial->usuario_id === Auth::guard('web')->id(), 404);

        $credencial->delete();

        return back()->with('status', "Se quitó el ingreso con huella de {$credencial->nombre}.");
    }

    // --- Auxiliares ---

    private function respuestaJson(string $json): JsonResponse
    {
        return JsonResponse::fromJsonString($json);
    }

    /** Las fallas de verificación (firma, origen, desafío) se devuelven como 422 con un mensaje claro. */
    private function verificando(callable $accion): mixed
    {
        try {
            return $accion();
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::warning('WebAuthn: verificación fallida - ' . $e->getMessage());
            abort(422, 'No pudimos verificar tu huella. Volvé a intentarlo.');
        }
    }
}
