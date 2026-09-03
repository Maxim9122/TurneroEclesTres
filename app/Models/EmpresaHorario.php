<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaHorario extends Model
{
    use HasFactory;

    protected $table = 'empresa_horarios';

    protected $fillable = [
        'empresa_id', 'dia_semana', 'hora_inicio', 'hora_fin',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}