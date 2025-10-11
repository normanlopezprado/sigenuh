<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bed extends Model
{
    protected $table = 'beds';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'hospital_floor_service_id',
        'code',
        'status',
        'notes',
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function hospitalFloorService()
    {
        return $this->belongsTo(HospitalFloorService::class);
    }
    
    public function getServicePathAttribute(): string
    {
        $hospital = $this->hospitalFloorService?->hospitalFloor?->hospital?->name;
        $nivel    = $this->hospitalFloorService?->hospitalFloor?->nivel?->name;
        $servicio = $this->hospitalFloorService?->service?->name;
        $categoria = $this->hospitalFloorService?->service?->category;

        return collect([$hospital, $nivel, $servicio, $categoria])
            ->filter(fn($v) => filled($v))
            ->implode(' → ');
    }

}
