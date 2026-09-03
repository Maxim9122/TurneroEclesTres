<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profesional extends Model
{
    use HasFactory;

    protected $table = 'profesionales';

    protected $fillable = [
        'empresa_id', 'usuario_id', 'nombre', 'foto_path', 'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(ProfesionalHorario::class);
    }

    /**
     * Si el profesional tiene su propio horario cargado, se usa ese.
     * Si no tiene ninguno, hereda el horario general de la empresa.
     */
    public function horariosEfectivos()
    {
        $propios = $this->horarios;

        return $propios->isNotEmpty() ? $propios : $this->empresa->horarios;
    }

    public function usaHorarioGeneral(): bool
    {
        return $this->horarios->isEmpty();
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'servicio_profesional');
    }

    /**
     * Si el profesional no tiene servicios restringidos cargados,
     * se asume que puede realizar todos los servicios de la empresa.
     */
    public function puedeRealizar(Servicio $servicio): bool
    {
        if ($this->servicios()->count() === 0) {
            return true;
        }

        return $this->servicios()->where('servicios.id', $servicio->id)->exists();
    }
}