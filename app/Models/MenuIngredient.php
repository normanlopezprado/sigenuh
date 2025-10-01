<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class MenuIngredient extends Pivot
{
    protected $table = 'menu_ingredient';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['menu_id','ingredient_id','qty','is_optional','notes'];

    protected static function booted()
    {
        static::creating(function ($pivot) {
            if (empty($pivot->id)) {
                $pivot->id = (string) Str::uuid();
            }
            // Por si los checkboxes vienen nulos:
            if ($pivot->is_optional === null) {
                $pivot->is_optional = false;
            }
            if ($pivot->qty === null) {
                $pivot->qty = 0;
            }
        });
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

}
