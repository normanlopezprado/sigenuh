<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cart extends Model
{
    use SoftDeletes, HasUuids;

    // ids UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'carts';

    protected $fillable = [
        'hospital_id',
        'name',       // Nombre del carrito (UI)
        'code_name',  // Apodo / código interno (UI)
        'color',      // Color para UI (badge)
        'order',      // Orden en listados
        'status',     // Activo/Inactivo
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order'  => 'integer',
    ];

    /* --- Relaciones --- */

    // Hospital propietario del carrito
    public function hospital()
    {
        return $this->belongsTo(\App\Models\Hospital::class, 'hospital_id', 'id');
    }

    // Servicios (ruta de reparto) a través del pivot cart_service
    public function services()
    {
        return $this->belongsToMany(\App\Models\HospitalFloorService::class, 'cart_service', 'cart_id', 'hospital_floor_service_id')
            ->using(\App\Models\CartService::class)
            ->withPivot(['id', 'order', 'assigned_by', 'assigned_at'])
            ->withTimestamps()
            ->orderBy('cart_service.order');
    }

    // Usuario(s) que han asignado servicios (útil si necesitas consultarlo desde el carrito)
    public function assignedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'cart_service', 'cart_id', 'assigned_by')
            ->withPivot(['hospital_floor_service_id', 'assigned_at'])
            ->withTimestamps();
    }

    /* --- Scopes útiles --- */

    // Solo activos
    public function scopeActive($q)
    {
        return $q->where('status', true);
    }

    // Por hospital
    public function scopeForHospital($q, string $hospitalId)
    {
        return $q->where('hospital_id', $hospitalId);
    }

    // Ordenados para UI
    public function scopeOrdered($q)
    {
        return $q->orderBy('order')->orderBy('name');
    }

    /* --- Atributos de ayuda para UI --- */

    // Etiqueta compuesta: "Cart #1 — Servicios"
    public function getDisplayLabelAttribute(): string
    {
        $name = $this->name ?? '';
        $code = $this->code_name ? " — {$this->code_name}" : '';
        return "{$name}{$code}";
    }

    // Clase/valor de color usable en badge
    public function getUiColorAttribute(): ?string
    {
        return $this->color; // puede ser "success" o "#198754" según tu implementación
    }
}
