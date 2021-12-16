<?php

namespace App\Models\Traits;


trait HasName
{
    public abstract function name();

    protected function getNameAttribute()
    {
        return $this->name();
    }


}
