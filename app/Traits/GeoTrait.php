<?php
namespace App\Traits;

trait GeoTrait
{
    public static function distanceKm($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // نصف قطر الأرض بالكيلومتر

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo   = deg2rad($lat2);
        $lonTo   = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $earthRadius * $angle;
    }
}

