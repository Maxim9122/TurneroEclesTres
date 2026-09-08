<?php

namespace App\Services;

class CarritoService
{
    private function key(int $empresaId): string
    {
        return "carrito.empresa.{$empresaId}";
    }

    public function items(int $empresaId): array
    {
        return session($this->key($empresaId), []);
    }

    public function agregar(int $empresaId, int $productoId, int $cantidad): void
    {
        $items = $this->items($empresaId);
        $items[$productoId] = ($items[$productoId] ?? 0) + $cantidad;
        session([$this->key($empresaId) => $items]);
    }

    public function actualizar(int $empresaId, int $productoId, int $cantidad): void
    {
        $items = $this->items($empresaId);

        if ($cantidad <= 0) {
            unset($items[$productoId]);
        } else {
            $items[$productoId] = $cantidad;
        }

        session([$this->key($empresaId) => $items]);
    }

    public function quitar(int $empresaId, int $productoId): void
    {
        $this->actualizar($empresaId, $productoId, 0);
    }

    public function vaciar(int $empresaId): void
    {
        session()->forget($this->key($empresaId));
    }

    public function cantidadTotal(int $empresaId): int
    {
        return array_sum($this->items($empresaId));
    }
}