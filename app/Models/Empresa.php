<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Models\Direccion;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'slug', 'rubro', 'estado', 'motivo_rechazo',
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

    /**
     * Horario por defecto mientras el negocio no cargue el suyo: todos los días de 07:00 a 00:00.
     * El cierre se guarda como 23:59 para que ningún turno termine "a las 00:00" del día siguiente.
     */
    public const HORARIO_DEFECTO_INICIO = '07:00';
    public const HORARIO_DEFECTO_FIN = '23:59';

    /** El horario que realmente rige: el cargado por el negocio o, si no cargó ninguno, el por defecto. */
    public function horariosEfectivos(): Collection
    {
        return $this->usaHorarioPorDefecto() ? self::horarioPorDefecto($this->id) : $this->horarios;
    }

    public function usaHorarioPorDefecto(): bool
    {
        return $this->horarios->isEmpty();
    }

    public static function horarioPorDefecto(?int $empresaId = null): Collection
    {
        return collect(range(0, 6))->map(fn ($dia) => new EmpresaHorario([
            'empresa_id' => $empresaId,
            'dia_semana' => $dia,
            'hora_inicio' => self::HORARIO_DEFECTO_INICIO,
            'hora_fin' => self::HORARIO_DEFECTO_FIN,
        ]));
    }

    /** "07:00" / "00:00" para mostrar (23:59 se muestra como medianoche). */
    public static function horaParaMostrar(string $hora): string
    {
        $hora = substr($hora, 0, 5);

        return $hora === '23:59' ? '00:00' : $hora;
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