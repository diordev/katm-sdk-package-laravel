<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

/**
 * KatmResponseDto
 *
 * KATM API’lardan keluvchi universal javob strukturasi uchun DTO.
 *
 * Ushbu DTO quyidagi javoblar uchun mos:
 * - data: asosiy javob ma’lumotlari (masalan: token, user, report va h.k.)
 * - error: xatolik holatlari
 * - success: muvoffaqiyat holati flag’i
 * - validationError: validatsiyaga oid xabarlar
 * - total: umumiy natijalar soni (masalan, pagination uchun)
 *
 * DTO ishlatiladi:
 * - Token olish
 * - Hisobotlar
 * - Har qanday umumiy so‘rovlar
 *
 * @property TokenResponseDto|array|null $data Asosiy javob ma’lumotlari
 * @property ErrorResponseDto|array|null $error Xatolik obyekti yoki massiv
 * @property bool $success Javob muvaffaqiyatli bo‘lgan-bo‘lmaganligini bildiradi
 * @property array|string|null $total (ixtiyoriy) umumiy natijalar soni
 * @property array|string|null $validationError (ixtiyoriy) validatsiya xatolari
 */
final class KatmResponseDto extends Data
{
    public function __construct(
        public mixed $data = null,
        public mixed $error = null,
        public bool $success = false,
        public array|string|null $total = null,
        public array|string|null $validationError = null,
    ) {}

    /**
     * Javob muvaffaqiyatli bo‘lganini bildiradi
     */
    public function isOk(): bool
    {
        return $this->success === true;
    }

    /**
     * Xatolik yoki validatsiya xabari matnini qaytaradi
     *
     * @param  string|null  $fallback  Agar xabar topilmasa — default qiymat
     */
    public function errorMessage(?string $fallback = null): ?string
    {
        if (is_array($this->error) && isset($this->error['errMsg'])) {
            return (string) $this->error['errMsg'];
        }

        return $fallback;
    }

    /**
     * Validatsiya xatolari mavjudligini aniqlaydi
     */
    public function hasValidationErrors(): bool
    {
        return ! empty($this->validationError);
    }

    /**
     * Birinchi validatsiya xatolik xabarini qaytaradi
     */
    public function firstValidationError(): ?string
    {
        if (is_string($this->validationError)) {
            return $this->validationError;
        }
        if (is_array($this->validationError) && ! empty($this->validationError)) {
            return (string) reset($this->validationError);
        }

        return null;
    }

    /**
     * Agar data ichida `TokenResponseDto` bo‘lsa, `accessToken` ni qaytaradi
     */
    public function tokenOrNull(): ?string
    {

        if (is_array($this->data) && array_key_exists('accessToken', $this->data)) {
            return is_string($this->data['accessToken']) ? $this->data['accessToken'] : null;
        }

        return null;
    }

    /**
     * `data` maydonini massiv shaklida qaytaradi
     */
    public function dataArray(): array
    {
        if (is_array($this->data)) {
            return $this->data;
        }
        if ($this->data instanceof Data) {
            return $this->data->toArray();
        }

        return [];
    }

    /**
     * `data` ni kerakli DTO klassga map qiladi.
     * Masalan: `$resp->dataAs(UserDto::class)`
     *
     * @param  class-string<Data>  $dtoClass
     */
    public function dataAs(string $dtoClass): ?object
    {
        if ($this->data === null) {
            return null;
        }

        if ($this->data instanceof $dtoClass) {
            return $this->data;
        }

        if (is_array($this->data)) {
            /** @var class-string<Data> $dtoClass */
            return $dtoClass::from($this->data);
        }

        return null;
    }
}
