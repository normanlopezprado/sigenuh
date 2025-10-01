<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class CalendarMenuIngredient extends Pivot
{
    protected $table = 'calendar_menu_ingredient';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['calendar_id','menu_ingredient_id','notes'];

    protected static function booted()
    {
        static::creating(function ($pivot) {
            if (empty($pivot->id)) $pivot->id = (string) Str::uuid();
        });
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function menuIngredient()
    {
        return $this->belongsTo(MenuIngredient::class, 'menu_ingredient_id');
    }
}
