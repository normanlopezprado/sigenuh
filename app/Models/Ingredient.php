<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ingredient extends Model
{
    protected $table = 'ingredients';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    public function menus()
    {
        return $this->belongsToMany(\App\Models\Menu::class, 'menu_ingredient', 'ingredient_id', 'menu_id')
            ->withPivot(['id','qty','is_optional','notes'])
            ->withTimestamps();
    }
}
