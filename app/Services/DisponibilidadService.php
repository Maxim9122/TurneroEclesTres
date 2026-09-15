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

        $diaSemana = $fecha->dayOfWeek;

        $slots = $profesionalId
            ? $this->slotsConProfesionalEspecifico($candidatos->first(), $fecha, $diaSemana, $duracionTotal)
            : $this->slotsSinPreferencia($empresa, $candidatos, $fecha, $diaSemana, $duracionTotal);

        if ($fecha->isToday()) {
            $ahora = Carbon::now();
            $slots = $slots->filter(function ($hora) use ($fecha, $ahora) {
                return Carbon::parse($fecha->toDateString() . ' ' . $hora)->greaterThan($ahora);
            });
        }

        return $slots->unique()->sort()->values()->all();
    }

    /**
     * Caso: el cliente eligió un profesional puntual. Solo importan los horarios
     * y turnos ya asignados de ESE profesional.
     */
    private function slotsConProfesionalEspecifico(Profesional $profesional, Carbon $fecha, int $diaSemana, int $duracionTotal): Collection
    {
        $rangos = $profesional->horariosEfectivos()->where('dia_semana', $diaSemana);

        if ($rangos->isEmpty()) {
            return collect();
        }

        $ocupados = $this->intervalosOcupadosDeProfesional($profesional->id, $fecha);

        $slots = collect();

        foreach ($rangos as $rango) {
            $slots = $slots->merge(
                $this->generarSlotsDelRango($rango->hora_inicio, $rango->hora_fin, $duracionTotal, $ocupados)
            );
        }

        return $slots;
    }

    /**
     * Caso: "sin preferencia". Hay que contar CUPOS reales: cuántos profesionales
     * están libres a la vez en cada franja, restando tanto los turnos ya asignados
     * a un profesional puntual como los turnos "sin preferencia" que ya reservaron
     * ese mismo horario (y todavía no tienen profesional asignado).
     */
    private function slotsSinPreferencia(Empresa $empresa, Collection $candidatos, Carbon $fecha, int $diaSemana, int $duracionTotal): Collection
    {
        // Minutos ocupados por turnos YA asignados a cada profesional candidato.
        $ocupadosPorProfesional = $candidatos->mapWithKeys(function (Profesional $p) use ($fecha) {
            return [$p->id => $this->intervalosOcupadosDeProfesional($p->id, $fecha)];
        });

        // Turnos "sin preferencia" (todavía sin profesional asignado) de la empresa,
        // ese día, en estados activos. Cada uno consume un cupo genérico del pool.
        $turnosSinAsignar = Turno::where('empresa_id', $empresa->id)
            ->whereNull('profesional_id')
            ->whereDate('fecha', $fecha->toDateString())
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->get(['hora_inicio', 'hora_fin'])
            ->map(fn ($t) => [$this->horaAMinutos($t->hora_inicio), $this->horaAMinutos($t->hora_fin)]);

        // Rango total a explorar: desde el inicio más temprano hasta el cierre más tardío
        // entre todos los candidatos, ese día.
        $inicioMin = null;
        $finMax = null;

        foreach ($candidatos as $p) {
            foreach ($p->horariosEfectivos()->where('dia_semana', $diaSemana) as $rango) {
                $ini = $this->horaAMinutos($rango->hora_inicio);
                $fin = $this->horaAMinutos($rango->hora_fin);
                $inicioMin = is_null($inicioMin) ? $ini : min($inicioMin, $ini);
                $finMax = is_null($finMax) ? $fin : max($finMax, $fin);
            }
        }

        if (is_null($inicioMin)) {
            return collect();
        }

        $slots = collect();

        for ($minuto = $inicioMin; $minuto + $duracionTotal <= $finMax; $minuto += self::INTERVALO_MINUTOS) {
            $inicioSlot = $minuto;
            $finSlot = $minuto + $duracionTotal;

            // ¿Cuántos candidatos están realmente disponibles (dentro de su horario
            // y sin un turno asignado que se cruce) para este slot puntual?
            $capacidad = 0;

            foreach ($candidatos as $p) {
                $dentroDeHorario = $p->horariosEfectivos()
                    ->where('dia_semana', $diaSemana)
                    ->contains(function ($rango) use ($inicioSlot, $finSlot) {
                        return $this->horaAMinutos($rango->hora_inicio) <= $inicioSlot
                            && $this->horaAMinutos($rango->hora_fin) >= $finSlot;
                    });

                if (!$dentroDeHorario) {
                    continue;
                }

                $libre = true;
                foreach ($ocupadosPorProfesional[$p->id] as [$ocupIni, $ocupFin]) {
                    if ($inicioSlot < $ocupFin && $finSlot > $ocupIni) {
                        $libre = false;
                        break;
                    }
                }

                if ($libre) {
                    $capacidad++;
                }
            }

            // A esa capacidad le restamos los turnos "sin preferencia" que ya
            // reservaron un horario que se cruza con este slot.
            $reservadosGenericos = $turnosSinAsignar->filter(function ($ocupado) use ($inicioSlot, $finSlot) {
                [$ocupIni, $ocupFin] = $ocupado;
                return $inicioSlot < $ocupFin && $finSlot > $ocupIni;
            })->count();

            if (($capacidad - $reservadosGenericos) > 0) {
                $slots->push($this->minutosAHora($minuto));
            }
        }

        return $slots;
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

    private function intervalosOcupadosDeProfesional(int $profesionalId, Carbon $fecha): array
    {
        return Turno::where('profesional_id', $profesionalId)
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