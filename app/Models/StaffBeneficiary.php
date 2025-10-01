<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StaffBeneficiary extends Model
{
    use SoftDeletes, HasUuids; 

    
    public $incrementing = false;
    protected $keyType = 'string';

    public function hospital()
    {
        
        return $this->belongsTo(\App\Models\Hospital::class, 'hospital_id', 'id');
    }

    protected $fillable = [
        'hospital_id',
        'full_name',
        'job_title',
        'status',
    ];
}
