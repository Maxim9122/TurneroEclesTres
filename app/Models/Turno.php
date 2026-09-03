<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function precioTotal(): float
    {
        return (float) $this->servicios->sum('pivot.precio_al_momento');
    }

    public function duracionTotalMinutos(): int
    {
        return (int) $this->servicios->sum('pivot.duracion_al_momento');
    }
}