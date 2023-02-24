<?php

namespace Devsfort\Location\Facade;

use Illuminate\Support\Facades\Facade;

class Location extends Facade
{

    protected static function getFacadeAccessor()
    {
        return "DevsfortLocation";
    }
}