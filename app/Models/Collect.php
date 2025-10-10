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
        'diet_type','has_disponsable', 'user_id','notes','has_minor','has_companion','companion_diet_type',
        'companion_notes','companion_has_disposable',
    ];

    protected $casts = [
        'date' => 'date',
        'has_disponsable' => 'boolean',
        'companion_has_disposable' => 'boolean',
        'has_minor' => 'boolean',
        'has_companion' => 'boolean',
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
