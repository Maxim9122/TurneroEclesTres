<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Direccion extends Model
{
    use HasFactory;

    protected $table = 'direcciones';

    protected $fillable = [
        'ciudad', 'barrio', 'calle', 'altura', 'lat', 'lng',
    ];

    public function direccionable(): MorphTo
    {
        return $this->morphTo();
    }
}