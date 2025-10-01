<?php

namespace App\Support;

use App\Models\Hospital;
use Carbon\Carbon;

class MealWindow
{
    public static function currentMealPeriod(Hospital $hospital): string
    {
        $tz = config('app.timezone', 'America/Guatemala');
        $now = Carbon::now($tz);

        $bStart = self::timeOrNull($hospital->breakfast_collection_start, $tz);
        $bEnd   = self::timeOrNull($hospital->breakfast_collection_end,   $tz);

        $lStart = self::timeOrNull($hospital->lunch_collection_start,     $tz);
        $lEnd   = self::timeOrNull($hospital->lunch_collection_end,       $tz);

        $dStart = self::timeOrNull($hospital->dinner_collection_start,    $tz);
        $dEnd   = self::timeOrNull($hospital->dinner_collection_end,      $tz);

        if ($bStart && $bEnd && self::inWindow($now, $bStart, $bEnd))   return 'Desayuno';
        if ($lStart && $lEnd && self::inWindow($now, $lStart, $lEnd))   return 'Almuerzo';
        if ($dStart && $dEnd && self::inWindow($now, $dStart, $dEnd))   return 'Cena';

        return 'Fuera de rango';
    }

    public static function nowWithinHospitalWindow(Hospital $hospital, string $category): bool
    {
        return self::currentMealPeriod($hospital) === $category;
    }


    private static function inWindow(Carbon $now, Carbon $start, Carbon $end): bool
    {
        if ($start->lessThanOrEqualTo($end)) {
            return $now->betweenIncluded($start, $end);
        }
        return $now->greaterThanOrEqualTo($start) || $now->lessThanOrEqualTo($end);
    }


    private static function timeOrNull(?string $time, string $tz): ?Carbon
    {
        if (!$time) return null;
        return Carbon::today($tz)->setTimeFromTimeString($time);
    }
}
