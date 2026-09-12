<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use App\Models\Direccion;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'slug', 'rubro', 'estado',
        'logo_path', 'color_fondo', 'imagen_fondo_path',
        'descripcion', 'telefono', 'email_contacto',
    ];

    protected static function booted(): void
    {
        static::creating(function (Empresa $empresa) {
            if (empty($empresa->slug)) {
                $empresa->slug = Str::slug($empresa->nombre) . '-' . Str::random(4);
            }
        });
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function categoriasProductos(): HasMany
    {
        return $this->hasMany(CategoriaProducto::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    public function servicios(): HasMany
    {
        return $this->hasMany(Servicio::class);
    }

    public function profesionales(): HasMany
    {
        return $this->hasMany(Profesional::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(EmpresaHorario::class);
    }

    public function direcciones(): MorphMany
    {
        return $this->morphMany(Direccion::class, 'direccionable');
    }

    public function estaActiva(): bool
    {
        return $this->estado === 'activa';
    }

    public function fondoCss(): string
    {
        if ($this->imagen_fondo_path) {
            return "background-image: url('" . asset('storage/' . $this->imagen_fondo_path) . "'); background-size: cover; background-position: center;";
        }

        return 'background-color: ' . ($this->color_fondo ?: '#EDE7DD') . ';';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function direccionPrincipal(): ?Direccion
    {
        return $this->direcciones()->first();
    }
}