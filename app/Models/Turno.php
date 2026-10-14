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