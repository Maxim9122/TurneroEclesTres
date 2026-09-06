<?php

namespace App\Observers;

use App\Models\ClienteEmpresaReciente;
use App\Models\Turno;

class TurnoObserver
{
    public function created(Turno $turno): void
    {
        if ($turno->estado === 'confirmado') {
            ClienteEmpresaReciente::registrar($turno->cliente_id, $turno->empresa_id);
        }
    }

    public function updated(Turno $turno): void
    {
        if ($turno->wasChanged('estado') && $turno->estado === 'confirmado') {
            ClienteEmpresaReciente::registrar($turno->cliente_id, $turno->empresa_id);
        }
    }
}