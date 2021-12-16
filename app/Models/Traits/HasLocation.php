<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Builder;

/**
 * Based on comments from
 * https://stackoverflow.com/questions/2234204/latitude-longitude-find-nearest-latitude-longitude-complex-sql-or-complex-calc
 * Trait HasLocation
 * @package App\Models\Traits
 */
trait HasLocation
{
    public function scopeDistanceNearby(Builder $builder, $lat, $long)
    {
        $builder->selectRaw("*, (" .
            "6371 * " .
            "acos(least(1.0,cos(radians($lat)) * " .
            "cos(radians(latitude)) * " .
            "cos(radians(longitude) - " .
            "radians($long)) + " .
            "sin(radians($lat)) * " .
            "sin(radians(latitude ))))" .
            ") AS distance")
            ->orderBy('distance');
    }

}
