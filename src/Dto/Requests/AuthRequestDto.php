<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

/**
 * AuthRequestDto
 *
 * KATM API uchun autentifikatsiya ma’lumotlarini ifodalovchi Data Transfer Object (DTO).
 *
 * Bu DTO quyidagi maqsadlarda ishlatiladi:
 * - Autentifikatsiya so‘rovlarini yuborishda
 * - `Spatie\LaravelData` orqali avtomatik serialization va validation
 * - Kodda ma’lumotlar uzatishni aniqroq va tiplangan shaklda bajarish
 *
 * @property string $login Foydalanuvchi logini (yoki username/email)
 * @property string $password Foydalanuvchi paroli
 */
final class AuthRequestDto extends Data
{
    /**
     * AuthRequestDto constructor.
     *
     * @param  string  $login  Foydalanuvchi logini
     * @param  string  $password  Foydalanuvchi paroli
     */
    public function __construct(
        public string $login,
        public string $password,
    ) {}
}
