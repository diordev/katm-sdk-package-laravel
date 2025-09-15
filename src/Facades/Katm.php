<?php

namespace Katm\KatmSdk\Facades;

use Illuminate\Support\Facades\Facade;
use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Dto\Responses\KatmResponseDto;
use Katm\KatmSdk\Services\KatmManagerService;

/**
 * Katm
 *
 * Laravel Facade orqali KatmManagerService metodlariga statik tarzda murojaat qilish imkonini beradi.
 *
 * Foydalanish:
 * ```php
 * Katm::authenticate();
 * Katm::initClient($dto);
 * ```
 *
 * @method static KatmResponseDto authenticate() Mavjud token orqali yoki yangidan autentifikatsiya qilish
 * @method static KatmResponseDto initClient(InitClientRequestDto $dto) Mijozni sistemaga ro‘yxatdan o‘tkazish
 */
class Katm extends Facade
{
    /**
     * Facade orqali yo‘naltiriladigan asosiy servis klass nomi
     */
    protected static function getFacadeAccessor(): string
    {
        return KatmManagerService::class;
    }
}
