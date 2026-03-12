<?php

namespace Hardcodear\ApiResponseService\Facades;

use Illuminate\Support\Facades\Facade;

class ApiResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'apiresponse';
    }
}
