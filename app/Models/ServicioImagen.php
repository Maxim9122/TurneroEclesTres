<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioImagen extends Model
{
    use HasFactory;

    protected $table = 'servicio_imagenes';

    protected $fillable = ['servicio_id', 'path', 'orden'];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }
}