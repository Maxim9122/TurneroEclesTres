<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'cliente_id', 'profesional_id', 'creado_por_usuario_id',
        'fecha', 'hora_inicio', 'hora_fin', 'estado', 'notas',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por_usuario_id');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'turno_servicios')
            ->withPivot(['precio_al_momento', 'duracion_al_momento'])
            ->withTimestamps();
    }

    /**
     * Un turno nunca pasa al día siguiente: como mucho termina a medianoche (se guarda 23:59 y se
     * muestra 00:00). Si pasara, quedaría por ejemplo 23:45–03:15 en el mismo día y se calcularían
     * mal las superposiciones y los horarios libres.
     */
    public const FIN_DEL_DIA = '23:59';

    /**
     * Hora de fin de un turno que empieza a $inicio y dura $minutos, sin pasar de medianoche.
     *
     * @return array{0: string, 1: bool} [hora de fin "H:i", true si hubo que recortarlo]
     */
    public static function calcularFin(string $inicio, int $minutos): array
    {
        [$h, $m] = array_map('intval', explode(':', substr($inicio, 0, 5)));
        $fin = $h * 60 + $m + max(0, $minutos);

        if ($fin > 23 * 60 + 59) {
            return [self::FIN_DEL_DIA, true];
        }

        return [sprintf('%02d:%02d', intdiv($fin, 60), $fin % 60), false];
    }

    /** "00:00" en vez de "23:59" para un turno que termina a medianoche. */
    public function horaFinParaMostrar(): string
    {
        $fin = substr((string) $this->hora_fin, 0, 5);

        return $fin === self::FIN_DEL_DIA ? '00:00' : $fin;
    }

    /** Adicionales elegidos (diseño, líneas, etc.), con el precio y la duración del momento. */
    public function adicionales(): HasMany
    {
        return $this->hasMany(TurnoAdicional::class)->orderBy('id');
    }

    public function precioTotal(): float
    {
        return (float) $this->servicios->sum('pivot.precio_al_momento')
            + (float) $this->adicionales->sum('precio_al_momento');
    }

    public function duracionTotalMinutos(): int
    {
        return (int) $this->servicios->sum('pivot.duracion_al_momento')
            + (int) $this->adicionales->sum('duracion_al_momento');
    }

    /** "Corte (+ Diseño, Líneas) + Barba": los servicios con sus adicionales. */
    public function descripcionServicios(): string
    {
        $porServicio = $this->adicionales->groupBy('servicio_id');

        return $this->servicios->map(function ($servicio) use ($porServicio) {
            $extras = $porServicio->get($servicio->id, collect())->pluck('nombre_al_momento');

            return $extras->isEmpty()
                ? $servicio->nombre
                : $servicio->nombre . ' (+ ' . $extras->implode(', ') . ')';
        })->implode(' + ');
    }
}