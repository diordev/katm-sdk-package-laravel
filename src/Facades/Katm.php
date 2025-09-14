<?php

namespace Katm\KatmSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Katm\KatmSdk\Providers\KatmSdkServiceProvider;

class Katm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KatmSdkServiceProvider::class;
    }
}
