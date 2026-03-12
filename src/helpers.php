<?php

use Hardcodear\ApiResponseService\ApiResponseService;

if (! function_exists('apiresponse')) {
    function apiresponse(): ApiResponseService
    {
        return app('apiresponse');
    }
}
