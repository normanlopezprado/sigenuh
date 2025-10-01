<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StaffMealRecord extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'staff_beneficiary_id',
        'hospital_id',
        'delivery_date',
        'meal_type',
        'delivered_by',
        'menu_id',
        'menu_text',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(\App\Models\StaffBeneficiary::class, 'staff_beneficiary_id');
    }

    public function hospital()
    {
        return $this->belongsTo(\App\Models\Hospital::class, 'hospital_id');
    }

    public function deliveredBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'delivered_by');
    }

    public function menu()
    {
        return $this->belongsTo(\App\Models\Menu::class, 'menu_id');
    }
}