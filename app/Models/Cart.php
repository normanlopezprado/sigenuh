<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Cart extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'carts';

    protected $fillable = [
        'hospital_id',
        'name',       
        'code_name',  
        'color',      
        'order',      
        'status',     
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
        'order'  => 'integer',
    ];

    public function hospital()
    {
        return $this->belongsTo(\App\Models\Hospital::class, 'hospital_id', 'id');
    }


    public function services()
    {
        return $this->belongsToMany(\App\Models\HospitalFloorService::class, 'cart_service', 'cart_id', 'hospital_floor_service_id')
            ->using(\App\Models\CartService::class)
            ->withPivot(['id', 'order', 'assigned_by', 'assigned_at'])
            ->withTimestamps()
            ->orderBy('cart_service.order');
    }


    public function assignedByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'cart_service', 'cart_id', 'assigned_by')
            ->withPivot(['hospital_floor_service_id', 'assigned_at'])
            ->withTimestamps();
    }

    public function scopeActive($q)
    {
        return $q->where('status', true);
    }


    public function scopeForHospital($q, string $hospitalId)
    {
        return $q->where('hospital_id', $hospitalId);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('order')->orderBy('name');
    }

    public function getDisplayLabelAttribute(): string
    {
        $name = $this->name ?? '';
        $code = $this->code_name ? " — {$this->code_name}" : '';
        return "{$name}{$code}";
    }

    public function getUiColorAttribute(): ?string
    {
        return $this->color; 
    }
    
}
