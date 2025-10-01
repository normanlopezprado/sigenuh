<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CartService extends Pivot
{
    use HasUuids;

    protected $table = 'cart_service';

    // ids UUID en pivot
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'cart_id',
        'hospital_floor_service_id',
        'order',         // orden de visita en la ruta
        'assigned_by',   // usuario que asignó
        'assigned_at',   // fecha/hora de asignación
    ];

    protected $casts = [
        'order'       => 'integer',
        'assigned_at' => 'datetime',
    ];

    /* Relaciones de apoyo (opcionales pero convenientes) */

    public function cart()
    {
        return $this->belongsTo(\App\Models\Cart::class, 'cart_id', 'id');
    }

    public function hospitalFloorService()
    {
        return $this->belongsTo(\App\Models\HospitalFloorService::class, 'hospital_floor_service_id', 'id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by', 'id');
    }
}
