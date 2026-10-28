// Achica las fotos en el navegador ANTES de subirlas (ej.: una foto de iPhone de 5 MB pasa a ~500 KB).
//
// - Lado máximo 2560 px y calidad 90%: más del doble del tamaño en que se muestran, así que no se
//   nota pérdida. El servidor después las ajusta al tamaño final y las pasa a WebP.
// - Respeta la orientación de la cámara (fotos sacadas con el celular girado).
// - PNG/WebP (logos con transparencia) siguen con transparencia; SVG y GIF no se tocan.
// - Si algo falla o el navegador no lo soporta, se sube la foto original como antes.
//
// Aplica a todo <input type="file" accept="image/..."> salvo que tenga data-sin-achicar.

const LADO_MAXIMO = 2560;
const CALIDAD = 0.9;
const LIVIANA = 1.5 * 1024 * 1024; // más chica que esto y dentro del tamaño: se deja igual

const formatear = (bytes) => bytes >= 1024 * 1024
    ? (bytes / 1024 / 1024).toFixed(1).replace('.', ',') + ' MB'
    : Math.round(bytes / 1024) + ' KB';

const aBlob = (canvas, tipo, calidad) => new Promise((resolver) => canvas.toBlob(resolver, tipo, calidad));

async function decodificar(archivo) {
    if ('createImageBitmap' in window) {
        try {
            return await createImageBitmap(archivo, { imageOrientation: 'from-image' });
        } catch (e) {
            // sigue con <img>
        }
    }
    const url = URL.createObjectURL(archivo);
    try {
        const img = new Image();
        img.src = url;
        await img.decode();
        return img;
    } finally {
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
}

export async function achicarImagen(archivo) {
    if (!archivo.type.startsWith('image/') || /svg|gif/i.test(archivo.type)) {
        return archivo;
    }

    let imagen;
    try {
        imagen = await decodificar(archivo);
    } catch (e) {
        return archivo; // formato que el navegador no sabe leer: va el original
    }

    const ancho = imagen.width || imagen.naturalWidth;
    const alto = imagen.height || imagen.naturalHeight;
    const escala = Math.min(1, LADO_MAXIMO / Math.max(ancho, alto));

    if (escala === 1 && archivo.size <= LIVIANA) {
        imagen.close?.();
        return archivo;
    }

    const canvas = document.createElement('canvas');
    canvas.width = Math.round(ancho * escala);
    canvas.height = Math.round(alto * escala);
    const ctx = canvas.getContext('2d');
    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.drawImage(imagen, 0, 0, canvas.width, canvas.height);
    imagen.close?.();

    const conTransparencia = /png|webp/i.test(archivo.type);
    let blob = await aBlob(canvas, conTransparencia ? 'image/webp' : 'image/jpeg', CALIDAD);
    if (conTransparencia && (!blob || blob.type !== 'image/webp')) {
        blob = await aBlob(canvas, 'image/png'); // navegadores que no generan WebP
    }

    if (!blob || blob.size >= archivo.size) {
        return archivo; // no se ganó nada
    }

    const extension = blob.type === 'image/jpeg' ? 'jpg' : blob.type.split('/')[1];
    const nombre = archivo.name.replace(/\.[^.]+$/, '') + '.' + extension;

    return new File([blob], nombre, { type: blob.type, lastModified: Date.now() });
}

function mensajeDe(input) {
    let mensaje = input.parentElement.querySelector(':scope > [data-mensaje-achicar]');
    if (!mensaje) {
        mensaje = document.createElement('p');
        mensaje.dataset.mensajeAchicar = '';
        mensaje.className = 'text-xs text-mate-tinta/60 mt-1';
        input.insertAdjacentElement('afterend', mensaje);
    }
    return mensaje;
}

// Mientras haya fotos procesándose en un formulario, su botón de guardar queda deshabilitado.
// Se cuenta cuántos procesos hay en curso: el botón se libera recién cuando terminan todos
// (por ejemplo si eligen dos fotos seguidas).
const enCurso = new WeakMap();

function bloquearEnvio(form, bloquear) {
    if (!form) return;
    const botones = form.querySelectorAll('button[type=submit], input[type=submit]');
    const cantidad = Math.max(0, (enCurso.get(form) || 0) + (bloquear ? 1 : -1));
    enCurso.set(form, cantidad);

    if (bloquear && cantidad === 1) {
        botones.forEach((boton) => {
            boton.dataset.estabaDeshabilitado = boton.disabled ? '1' : '';
            boton.disabled = true;
        });
    } else if (!bloquear && cantidad === 0) {
        botones.forEach((boton) => {
            boton.disabled = boton.dataset.estabaDeshabilitado === '1';
            delete boton.dataset.estabaDeshabilitado;
        });
    }
}

document.addEventListener('change', async (evento) => {
    const input = evento.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.accept.includes('image')
        || 'sinAchicar' in input.dataset || !input.files?.length || !('DataTransfer' in window)) {
        return;
    }

    const originales = [...input.files];
    const mensaje = mensajeDe(input);
    // Si eligen otra foto mientras se procesa la anterior, solo vale la última elección.
    const turno = (input.turnoAchicar = (input.turnoAchicar || 0) + 1);
    const form = input.form;
    mensaje.textContent = originales.length > 1 ? 'Preparando fotos…' : 'Preparando foto…';
    bloquearEnvio(form, true);

    try {
        const achicadas = [];
        for (const archivo of originales) {
            achicadas.push(await achicarImagen(archivo));
        }

        if (input.turnoAchicar !== turno) {
            return; // quedó vieja: hay una elección más nueva en proceso
        }

        const pesoAntes = originales.reduce((t, a) => t + a.size, 0);
        const pesoDespues = achicadas.reduce((t, a) => t + a.size, 0);

        if (achicadas.some((a, i) => a !== originales[i])) {
            const transferencia = new DataTransfer();
            achicadas.forEach((a) => transferencia.items.add(a));
            input.files = transferencia.files; // no vuelve a disparar "change"
        }

        const lista = originales.length > 1 ? 'Fotos listas' : 'Foto lista';
        mensaje.textContent = pesoDespues < pesoAntes
            ? `${lista}: ${formatear(pesoAntes)} → ${formatear(pesoDespues)}`
            : `${lista} (${formatear(pesoDespues)})`;
    } catch (e) {
        if (input.turnoAchicar === turno) {
            mensaje.textContent = ''; // se sube la original
        }
    } finally {
        bloquearEnvio(form, false);
    }
});
