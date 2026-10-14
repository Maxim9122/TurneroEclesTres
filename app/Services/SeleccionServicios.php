<?php

namespace App\Services;

use App\Models\Servicio;
use App\Models\ServicioAdicional;
use App\Models\Turno;
use Illuminate\Support\Collection;

/**
 * Servicios + adicionales elegidos para un turno (por el cliente al reservar o por el staff).
 *
 * Solo se aceptan adicionales activos que pertenezcan a uno de los servicios elegidos de esa
 * empresa: cualquier otro id que llegue en la petición se ignora.
 */
class SeleccionServicios
{
    private function __construct(
        public readonly Collection $servicios,
        public readonly Collection $adicionales,
    ) {
    }

    /**
     * @param  bool  $soloActivos  false al editar un turno, para no perder servicios ya pausados que el turno tenía.
     */
    public static function desde(int $empresaId, array $servicioIds, array $adicionalIds = [], bool $soloActivos = true): self
    {
        $servicios = Servicio::where('empresa_id', $empresaId)
            ->when($soloActivos, fn ($q) => $q->where('activo', true))
            ->whereIn('id', array_map('intval', $servicioIds))
            ->get();

        $adicionales = $servicios->isEmpty() || empty($adicionalIds)
            ? collect()
            : ServicioAdicional::whereIn('servicio_id', $servicios->pluck('id'))
                ->whereIn('id', array_map('intval', $adicionalIds))
                ->where('activo', true)
                ->get();

        return new self($servicios, $adicionales);
    }

    public function vacia(): bool
    {
        return $this->servicios->isEmpty();
    }

    public function minutosAdicionales(): int
    {
        return (int) $this->adicionales->sum('duracion_minutos');
    }

    public function duracionMinutos(): int
    {
        return (int) $this->servicios->sum('duracion_minutos') + $this->minutosAdicionales();
    }

    public function precio(): float
    {
        return (float) $this->servicios->sum('precio') + (float) $this->adicionales->sum('precio');
    }

    /** Turno recién creado: guarda servicios y adicionales con precio y duración del momento. */
    public function guardarEn(Turno $turno): void
    {
        foreach ($this->servicios as $servicio) {
            $turno->servicios()->attach($servicio->id, [
                'precio_al_momento' => $servicio->precio,
                'duracion_al_momento' => $servicio->duracion_minutos,
            ]);
        }

        foreach ($this->adicionales as $adicional) {
            $this->crearAdicional($turno, $adicional);
        }
    }

    /**
     * Turno existente (edición del staff): lo que sigue elegido conserva el precio con el que se
     * reservó; solo lo nuevo toma el precio actual.
     */
    public function sincronizar(Turno $turno): void
    {
        $turno->loadMissing(['servicios', 'adicionales']);

        $idsServicios = $this->servicios->pluck('id')->all();
        $actuales = $turno->servicios->pluck('id')->all();

        if ($quitar = array_diff($actuales, $idsServicios)) {
            $turno->servicios()->detach($quitar);
        }

        foreach ($this->servicios->whereIn('id', array_diff($idsServicios, $actuales)) as $servicio) {
            $turno->servicios()->attach($servicio->id, [
                'precio_al_momento' => $servicio->precio,
                'duracion_al_momento' => $servicio->duracion_minutos,
            ]);
        }

        $idsAdicionales = $this->adicionales->pluck('id')->all();

        foreach ($turno->adicionales as $existente) {
            $sigueElServicio = in_array($existente->servicio_id, $idsServicios);
            // Un adicional que ya no existe en el catálogo (borrado) se conserva mientras siga su servicio.
            $sigueElegido = $existente->servicio_adicional_id === null
                || in_array($existente->servicio_adicional_id, $idsAdicionales);

            if (!$sigueElServicio || !$sigueElegido) {
                $existente->delete();
            }
        }

        $yaEstaban = $turno->adicionales->pluck('servicio_adicional_id')->filter()->all();

        foreach ($this->adicionales->whereNotIn('id', $yaEstaban) as $adicional) {
            $this->crearAdicional($turno, $adicional);
        }

        $turno->unsetRelation('servicios')->unsetRelation('adicionales');
    }

    private function crearAdicional(Turno $turno, ServicioAdicional $adicional): void
    {
        $turno->adicionales()->create([
            'servicio_id' => $adicional->servicio_id,
            'servicio_adicional_id' => $adicional->id,
            'nombre_al_momento' => $adicional->nombre,
            'precio_al_momento' => $adicional->precio,
            'duracion_al_momento' => $adicional->duracion_minutos,
        ]);
    }
}
