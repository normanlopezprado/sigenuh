<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, \Spatie\Permission\Traits\HasRoles;

    protected $guard_name = 'web';


    protected $fillable = [
        'id',
        'name',
        'user',
        'avatar',
        'email',
        'email_verified_at',
        'hospital_selected',
        'password',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function hospital()
    {
        return $this->belongsTo(\App\Models\Hospital::class, 'hospital_selected');
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/'.$this->avatar)   
            : asset('storage/avatars/default.jpg'); 
    }


    public static function baseUsernameFromName(string $fullName): string
    {
        $fullName = trim($fullName);
        if ($fullName === '') return '';

        $parts = array_values(array_filter(preg_split('/\s+/', Str::of($fullName)->squish())));
        $asciiParts = array_map(fn($w) => strtolower(Str::of(Str::ascii($w))->replaceMatches('/[^a-z0-9]/', '')), $parts);
        $asciiParts = array_values(array_filter($asciiParts)); 

        if (empty($asciiParts)) return '';

        $n = count($asciiParts);

        if ($n === 1) {
            return substr($asciiParts[0], 0, 4);
        }

        $base = '';

        $hasta = max(0, $n - 2);
        for ($i = 0; $i < $hasta; $i++) {
            $base .= substr($asciiParts[$i], 0, 2);
        }

        if ($n >= 2) {
            $base .= substr($asciiParts[$n - 2], 0, 1);
        }

        $base .= substr($asciiParts[$n - 1], 0, 2);

        return $base;
    }

    public static function generateUniqueUsernameFromBase(string $base): string
    {
        // normaliza: ascii, minúsculas, solo [a-z0-9]
        $base = trim(mb_strtolower(\Illuminate\Support\Str::ascii($base)));
        $base = preg_replace('/[^a-z0-9]/', '', $base) ?? '';

        if ($base === '') {
            $base = 'user';
        }

        // tronco base razonable
        $base = substr($base, 0, 32);

        // garantiza unicidad respetando el límite de 50 chars del campo
        $username = $base;
        $i = 1;
        while (static::where('user', $username)->exists()) {
            $suffix = (string)$i;
            $maxBaseLen = 50 - strlen($suffix);
            $username = substr($base, 0, $maxBaseLen) . $suffix;
            $i++;
        }

        return $username;
    }

    /**
     * (opcional) Mantén este por compatibilidad cuando te pasen nombre completo.
     */
    public static function generateUniqueUsername(string $fullName): string
    {
        $base = self::baseUsernameFromName($fullName);
        return self::generateUniqueUsernameFromBase($base);
    }

}
