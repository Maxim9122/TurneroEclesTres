<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Profesional;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DisponibilidadService
{
    private const INTERVALO_MINUTOS = 30;

    /**
     * Devuelve los horarios (formato H:i) disponibles para reservar en una fecha dada,
     * según la duración total de los servicios elegidos.
     *
     * Si $profesionalId es null, se consideran TODOS los profesionales activos que
     * puedan realizar los servicios elegidos, combinando sus horarios libres.
     */
    public function slotsDisponibles(Empresa $empresa, Carbon $fecha, Collection $servicios, ?int $profesionalId = null): array
    {
        $duracionTotal = (int) $servicios->sum('duracion_minutos');

        if ($duracionTotal <= 0) {
            return [];
        }

        $candidatos = $this->profesionalesCandidatos($empresa, $servicios, $profesionalId);

        if ($candidatos->isEmpty()) {
            return [];
        }

        $diaSemana = $fecha->dayOfWeek; // 0 domingo ... 6 sábado, igual que en la tabla

        $slots = collect();

        foreach ($candidatos as $profesional) {
            $rangos = $profesional->horariosEfectivos()->where('dia_semana', $diaSemana);

            if ($rangos->isEmpty()) {
                continue;
            }

            $ocupados = $this->intervalosOcupados($profesional, $fecha);

            foreach ($rangos as $rango) {
                $slots = $slots->merge(
                    $this->generarSlotsDelRango($rango->hora_inicio, $rango->hora_fin, $duracionTotal, $ocupados)
                );
            }
        }

        // Si la fecha es hoy, sacamos los horarios que ya pasaron
        if ($fecha->isToday()) {
            $ahora = Carbon::now();
            $slots = $slots->filter(function ($hora) use ($fecha, $ahora) {
                return Carbon::parse($fecha->toDateString() . ' ' . $hora)->greaterThan($ahora);
            });
        }

        return $slots->unique()->sort()->values()->all();
    }

    private function profesionalesCandidatos(Empresa $empresa, Collection $servicios, ?int $profesionalId): Collection
    {
        $query = $empresa->profesionales()->where('activo', true);

        if ($profesionalId) {
            $query->where('id', $profesionalId);
        }

        return $query->get()->filter(function (Profesional $profesional) use ($servicios) {
            foreach ($servicios as $servicio) {
                if (!$profesional->puedeRealizar($servicio)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Turnos ya reservados (pendientes o confirmados) de ese profesional ese día,
     * como pares [inicio_minutos, fin_minutos] desde las 00:00.
     */
    private function intervalosOcupados(Profesional $profesional, Carbon $fecha): array
    {
        return Turno::where('profesional_id', $profesional->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->get()
            ->map(function (Turno $turno) {
                return [
                    $this->horaAMinutos($turno->hora_inicio),
                    $this->horaAMinutos($turno->hora_fin),
                ];
            })
            ->all();
    }

    private function generarSlotsDelRango(string $horaInicioRango, string $horaFinRango, int $duracionTotal, array $ocupados): array
    {
        $inicio = $this->horaAMinutos($horaInicioRango);
        $fin = $this->horaAMinutos($horaFinRango);

        $slots = [];

        for ($minuto = $inicio; $minuto + $duracionTotal <= $fin; $minuto += self::INTERVALO_MINUTOS) {
            $libre = true;

            foreach ($ocupados as [$ocupadoInicio, $ocupadoFin]) {
                if ($minuto < $ocupadoFin && ($minuto + $duracionTotal) > $ocupadoInicio) {
                    $libre = false;
                    break;
                }
            }

            if ($libre) {
                $slots[] = $this->minutosAHora($minuto);
            }
        }

        return $slots;
    }

    private function horaAMinutos(string $hora): int
    {
        [$h, $m] = explode(':', substr($hora, 0, 5));
        return ((int) $h) * 60 + (int) $m;
    }

    private function minutosAHora(int $minutos): string
    {
        return sprintf('%02d:%02d', intdiv($minutos, 60), $minutos % 60);
    }
}