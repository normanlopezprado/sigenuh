<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Collect extends Model
{
    protected $table = 'collects';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'bed_id','date','meal',
        'diet_type','trays_count','disposables_count',
        'user_id','notes',
    ];

    protected $casts = [
        'date' => 'date',
        'trays_count' => 'integer',
        'disposables_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->id)) $m->id = (string) Str::uuid();
        });
    }

    public function bed()   { return $this->belongsTo(Bed::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
