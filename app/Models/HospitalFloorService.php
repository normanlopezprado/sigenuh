<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class HospitalFloorService extends Pivot
{
    protected $table = 'hospital_floor_services';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'hospital_floor_id',
        'service_id'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function hospitalFloor()
    {
        return $this->belongsTo(HospitalFloor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function carts()
    {
        return $this->belongsToMany(\App\Models\Cart::class, 'cart_service', 'hospital_floor_service_id', 'cart_id')
            ->using(\App\Models\CartService::class)
            ->withPivot(['id','order','assigned_by','assigned_at'])
            ->withTimestamps();
    }
}
