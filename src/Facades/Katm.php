<?php

namespace Katm\KatmSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Katm\KatmSdk\Services\KatmManagerService;

/**
 * @method static \Katm\KatmSdk\Dto\Responses\KatmResponseDto authenticate()
 * @method static \Katm\KatmSdk\Dto\Responses\KatmResponseDto initClient(\Katm\KatmSdk\Dto\Requests\InitClientRequestDto $dto)
 */
class Katm extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KatmManagerService::class;
    }
}
