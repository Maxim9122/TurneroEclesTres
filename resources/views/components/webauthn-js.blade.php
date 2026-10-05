{{-- Ayudante JS para llaves de acceso (huella / rostro / patrón). Se incluye una sola vez por página. --}}
@once
<script>
    window.EclesHuella = (() => {
        const aBuffer = (b64url) => {
            let s = b64url.replace(/-/g, '+').replace(/_/g, '/');
            while (s.length % 4) s += '=';
            return Uint8Array.from(atob(s), (c) => c.charCodeAt(0)).buffer;
        };
        const aB64 = (buffer) => {
            let s = '';
            new Uint8Array(buffer).forEach((b) => { s += String.fromCharCode(b); });
            return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        };
        const token = () => document.querySelector('input[name=_token]')?.value
            || document.querySelector('meta[name=csrf-token]')?.content || '';

        async function post(url, cuerpo) {
            const r = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token() },
                body: JSON.stringify(cuerpo || {}),
            });
            const datos = await r.json().catch(() => ({}));
            if (!r.ok) {
                const mensaje = r.status === 419
                    ? 'La página estaba desactualizada. Recargala e intentá de nuevo.'
                    : r.status === 429 ? 'Demasiados intentos. Esperá un minuto.' : (datos.message || 'Ocurrió un error. Intentá de nuevo.');
                throw new Error(mensaje);
            }
            return datos;
        }

        // Errores del navegador al cancelar o si el dispositivo no puede verificar.
        function mensajeError(e) {
            if (e && e.name === 'NotAllowedError') return 'Se canceló o no se pudo verificar. Volvé a intentarlo.';
            if (e && e.name === 'InvalidStateError') return 'Este dispositivo ya tiene el ingreso con huella activado para tu cuenta.';
            if (e && e.name === 'SecurityError') return 'Este navegador no permite usar la huella en esta dirección.';
            return (e && e.message) || 'Ocurrió un error. Intentá de nuevo.';
        }

        return {
            soportado: () => !!(window.PublicKeyCredential && navigator.credentials && window.isSecureContext),
            mensajeError,

            async registrar(urlOpciones, urlGuardar) {
                const o = await post(urlOpciones);
                o.challenge = aBuffer(o.challenge);
                o.user.id = aBuffer(o.user.id);
                (o.excludeCredentials || []).forEach((c) => { c.id = aBuffer(c.id); });

                const cred = await navigator.credentials.create({ publicKey: o });
                return post(urlGuardar, {
                    id: cred.id,
                    rawId: aB64(cred.rawId),
                    type: cred.type,
                    response: {
                        clientDataJSON: aB64(cred.response.clientDataJSON),
                        attestationObject: aB64(cred.response.attestationObject),
                        transports: cred.response.getTransports ? cred.response.getTransports() : [],
                    },
                });
            },

            async ingresar(urlOpciones, urlIngreso) {
                const o = await post(urlOpciones);
                o.challenge = aBuffer(o.challenge);
                (o.allowCredentials || []).forEach((c) => { c.id = aBuffer(c.id); });

                const cred = await navigator.credentials.get({ publicKey: o });
                return post(urlIngreso, {
                    id: cred.id,
                    rawId: aB64(cred.rawId),
                    type: cred.type,
                    response: {
                        clientDataJSON: aB64(cred.response.clientDataJSON),
                        authenticatorData: aB64(cred.response.authenticatorData),
                        signature: aB64(cred.response.signature),
                        userHandle: cred.response.userHandle ? aB64(cred.response.userHandle) : null,
                    },
                });
            },
        };
    })();
</script>
@endonce
