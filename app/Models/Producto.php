<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id', 'categoria_id', 'nombre', 'descripcion', 'precio', 'stock', 'foto_path', 'activo',
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

    public function hayStock(int $cantidad = 1): bool
    {
        return $this->stock >= $cantidad;
    }

    public function categoria(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
    }

    public function imagenes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductoImagen::class)->orderBy('orden');
    }

    /**
     * Devuelve hasta 3 rutas de imagen para mostrar: la principal (foto_path)
     * primero si existe, seguida de las adicionales cargadas en producto_imagenes.
     */
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