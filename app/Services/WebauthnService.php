<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\WebauthnCredencial;
use Illuminate\Http\Request;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

/**
 * Ingreso con llaves de acceso (WebAuthn / passkeys) para el staff.
 *
 * Flujo: el servidor genera un desafío aleatorio de un solo uso (guardado en la sesión), el
 * dispositivo lo firma con la clave privada que solo él tiene (desbloqueada con huella, rostro,
 * patrón o PIN) y acá se verifica la firma con la clave pública guardada para ESE usuario.
 */
class WebauthnService
{
    private const SESION_REGISTRO = 'webauthn.registro';
    private const SESION_INGRESO = 'webauthn.ingreso';
    private const TIMEOUT_MS = 120000;

    private ?SerializerInterface $serializer = null;

    // ---------------------------------------------------------------- Registro

    /** Opciones para que el navegador cree una llave nueva para este usuario (JSON para el navegador). */
    public function opcionesRegistro(Request $request, Usuario $usuario): string
    {
        $excluir = WebauthnCredencial::where('usuario_id', $usuario->id)->get()
            ->map(fn ($c) => $this->registroDe($c))
            ->map(fn (CredentialRecord $r) => PublicKeyCredentialDescriptor::create(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, $r->publicKeyCredentialId, $r->transports
            ))
            ->all();

        $opciones = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create('EclesTres', $this->rpId($request)),
            user: PublicKeyCredentialUserEntity::create($usuario->email, $this->userHandle($usuario), $usuario->nombre),
            challenge: random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::createPk(-7),   // ES256
                PublicKeyCredentialParameters::createPk(-257), // RS256 (Windows Hello)
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
            ),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            excludeCredentials: $excluir,
            timeout: self::TIMEOUT_MS,
        );

        $json = $this->json($opciones);
        $request->session()->put(self::SESION_REGISTRO, $json);

        return $json;
    }

    /** Verifica la respuesta del dispositivo y guarda la clave pública. */
    public function registrar(Request $request, Usuario $usuario, array $respuesta): WebauthnCredencial
    {
        $opcionesJson = $request->session()->pull(self::SESION_REGISTRO);
        abort_unless($opcionesJson, 422, 'La solicitud venció. Volvé a intentarlo.');

        /** @var PublicKeyCredentialCreationOptions $opciones */
        $opciones = $this->serializer()->deserialize($opcionesJson, PublicKeyCredentialCreationOptions::class, 'json');
        $credencial = $this->credencialDe($respuesta);
        abort_unless($credencial->response instanceof AuthenticatorAttestationResponse, 422, 'Respuesta inválida.');

        $registro = AuthenticatorAttestationResponseValidator::create($this->ceremonias($request)->creationCeremony())
            ->check($credencial->response, $opciones, $this->rpId($request));

        return WebauthnCredencial::create([
            'usuario_id' => $usuario->id,
            'credencial_hash' => WebauthnCredencial::hashId($registro->publicKeyCredentialId),
            'registro' => $this->json($registro),
            'nombre' => $this->nombreDispositivo((string) $request->userAgent()),
        ]);
    }

    // ---------------------------------------------------------------- Ingreso

    /** Opciones para ingresar: sin lista de credenciales, así el dispositivo ofrece las cuentas que tenga guardadas. */
    public function opcionesIngreso(Request $request): string
    {
        $opciones = PublicKeyCredentialRequestOptions::create(
            challenge: random_bytes(32),
            rpId: $this->rpId($request),
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout: self::TIMEOUT_MS,
        );

        $json = $this->json($opciones);
        $request->session()->put(self::SESION_INGRESO, $json);

        return $json;
    }

    /**
     * Verifica la firma y devuelve el usuario dueño de la llave. La llave tiene que estar
     * registrada a nombre de ese usuario: una huella o una llave de otra cuenta no sirven.
     */
    public function verificarIngreso(Request $request, array $respuesta): Usuario
    {
        $opcionesJson = $request->session()->pull(self::SESION_INGRESO);
        abort_unless($opcionesJson, 422, 'La solicitud venció. Volvé a intentarlo.');

        /** @var PublicKeyCredentialRequestOptions $opciones */
        $opciones = $this->serializer()->deserialize($opcionesJson, PublicKeyCredentialRequestOptions::class, 'json');
        $credencial = $this->credencialDe($respuesta);
        abort_unless($credencial->response instanceof AuthenticatorAssertionResponse, 422, 'Respuesta inválida.');

        $guardada = WebauthnCredencial::with('usuario')
            ->where('credencial_hash', WebauthnCredencial::hashId($credencial->rawId))
            ->first();
        abort_unless($guardada && $guardada->usuario, 422, 'Esta huella no está activada para ninguna cuenta. Ingresá con tu contraseña y activala desde Mi perfil.');

        $registro = AuthenticatorAssertionResponseValidator::create($this->ceremonias($request)->requestCeremony())
            ->check($this->registroDe($guardada), $credencial->response, $opciones, $this->rpId($request), null);

        // Contador actualizado: si alguien clonara la llave, el contador no cerraría.
        $guardada->update(['registro' => $this->json($registro), 'ultimo_uso_at' => now()]);

        return $guardada->usuario;
    }

    // ---------------------------------------------------------------- Auxiliares

    private function rpId(Request $request): string
    {
        return $request->getHost();
    }

    /** Orígenes aceptados: el del sitio configurado (APP_URL) y el de la petición actual. */
    private function origenes(Request $request): array
    {
        $app = parse_url((string) config('app.url'));
        $origenApp = isset($app['scheme'], $app['host'])
            ? $app['scheme'] . '://' . $app['host'] . (isset($app['port']) ? ':' . $app['port'] : '')
            : null;

        return array_values(array_unique(array_filter([$origenApp, $request->getSchemeAndHttpHost()])));
    }

    private function ceremonias(Request $request): CeremonyStepManagerFactory
    {
        $fabrica = new CeremonyStepManagerFactory();
        $fabrica->setAllowedOrigins($this->origenes($request));

        return $fabrica;
    }

    /** Identificador opaco del usuario dentro de la llave (no contiene datos personales). */
    private function userHandle(Usuario $usuario): string
    {
        return hash_hmac('sha256', 'staff:' . $usuario->id, (string) config('app.key'), true);
    }

    private function credencialDe(array $respuesta): PublicKeyCredential
    {
        try {
            return $this->serializer()->deserialize(json_encode($respuesta), PublicKeyCredential::class, 'json');
        } catch (\Throwable) {
            abort(422, 'Respuesta inválida del dispositivo.');
        }
    }

    private function registroDe(WebauthnCredencial $credencial): CredentialRecord
    {
        return $this->serializer()->deserialize($credencial->registro, CredentialRecord::class, 'json');
    }

    private function json(object $objeto): string
    {
        return $this->serializer()->serialize($objeto, 'json', [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            JsonEncode::OPTIONS => JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
        ]);
    }

    private function serializer(): SerializerInterface
    {
        return $this->serializer ??= (new WebauthnSerializerFactory(
            new AttestationStatementSupportManager([new NoneAttestationStatementSupport()])
        ))->create();
    }

    private function nombreDispositivo(string $ua): string
    {
        $sistema = match (true) {
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'Mac',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Dispositivo',
        };
        $navegador = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Chrome/') || str_contains($ua, 'CriOS') => 'Chrome',
            str_contains($ua, 'Firefox') || str_contains($ua, 'FxiOS') => 'Firefox',
            str_contains($ua, 'Safari') => 'Safari',
            default => null,
        };

        return $navegador ? "{$sistema} · {$navegador}" : $sistema;
    }
}
