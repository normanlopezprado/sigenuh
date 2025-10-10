<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Calendar extends Model
{
    protected $table = 'calendars';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id', 'date', 'user_id', 'notes', 'menu_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
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
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
