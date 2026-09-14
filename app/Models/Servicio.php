<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'nombre', 'foto_path', 'duracion_minutos', 'dias_renovacion', 'precio', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function profesionales(): BelongsToMany
    {
        return $this->belongsToMany(Profesional::class, 'servicio_profesional');
    }

    public function imagenes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServicioImagen::class)->orderBy('orden');
    }

    public function todasLasImagenes(): array
    {
        $rutas = [];

        if ($this->foto_path) {
            $rutas[] = $this->foto_path;
        }

        foreach ($this->imagenes as $imagen) {
            if (count($rutas) >= 3) break;
            $rutas[] = $imagen->path;
        }

        return $rutas;
    }
}