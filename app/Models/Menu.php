<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    protected $table = 'menus';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name','category','status','description','notes', 'diet_type',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredient', 'menu_id', 'ingredient_id')
            ->using(MenuIngredient::class)
            ->withPivot(['id','qty','is_optional','notes'])
            ->withTimestamps();
    }
    public function menuIngredients()
    {
        return $this->hasMany(MenuIngredient::class, 'menu_id');
    }
}
