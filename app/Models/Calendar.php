<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Calendar extends Model
{
    protected $table = 'calendars';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'date', 'user_id', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function calendarMenuIngredients()
    {
        return $this->hasMany(CalendarMenuIngredient::class, 'calendar_id');
    }
    public function optionalMenuIngredients()
    {
        return $this->belongsToMany(MenuIngredient::class, 'calendar_menu_ingredient', 'calendar_id', 'menu_ingredient_id')
            ->using(CalendarMenuIngredient::class)
            ->withPivot(['id', 'notes'])
            ->withTimestamps();
    }
}
