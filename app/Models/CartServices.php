<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CartService extends Pivot
{
    use HasUuids;

    protected $table = 'cart_service';

    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

    protected $fillable = [
        'id',
        'cart_id',
        'hospital_floor_service_id',
        'order',         
        'assigned_by',   
        'assigned_at',   
    ];

    protected $casts = [
        'order'       => 'integer',
        'assigned_at' => 'datetime',
    ];


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
