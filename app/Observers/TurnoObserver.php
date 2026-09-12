<?php

namespace App\Observers;

use App\Models\ClienteEmpresaReciente;
use App\Models\Turno;
use Illuminate\Support\Facades\DB;

class TurnoObserver
{
    public function created(Turno $turno): void
    {
        if ($turno->estado === 'confirmado') {
            $this->confirmar($turno);
        }
    }

    public function updated(Turno $turno): void
    {
        if (!$turno->wasChanged('estado')) {
            return;
        }

        if ($turno->estado === 'confirmado') {
            $this->confirmar($turno);
        }

        if ($turno->estado === 'completado') {
            $this->calcularRenovaciones($turno);
        }
    }

    private function confirmar(Turno $turno): void
    {
        ClienteEmpresaReciente::registrar($turno->cliente_id, $turno->empresa_id);

        // Nota: la confirmación al cliente se maneja por WhatsApp desde el panel
        // de turnos (botón "Enviar WhatsApp"), no por email automático.
    }

    /**
     * Por cada servicio del turno que tenga días de renovación configurados,
     * calcula y guarda la fecha en la que el cliente debería volver a hacérselo.
     */
    private function calcularRenovaciones(Turno $turno): void
    {
        $turno->load('servicios');

        foreach ($turno->servicios as $servicio) {
            if (!$servicio->dias_renovacion) {
                continue;
            }

            $fechaRenovacion = $turno->fecha->copy()->addDays($servicio->dias_renovacion);

            DB::table('turno_servicios')
                ->where('turno_id', $turno->id)
                ->where('servicio_id', $servicio->id)
                ->update(['fecha_renovacion' => $fechaRenovacion->toDateString()]);
        }
    }
}