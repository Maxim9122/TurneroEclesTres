<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfesionalHorario extends Model
{
    use HasFactory;

    protected $fillable = [
        'profesional_id', 'dia_semana', 'hora_inicio', 'hora_fin',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(Profesional::class);
    }

    public static function nombreDia(int $dia): string
    {
        return [
            0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
        ][$dia] ?? '';
    }
}