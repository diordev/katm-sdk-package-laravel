<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

final class KatmResponseDto extends Data
{
    public function __construct(
        public TokenResponseDto|array|null $data,
        public ErrorResponseDto|array|null $error,
        public bool $success,
        public array|string|null $total = null,
        public array|string|null $validationError = null,
    ) {}

    /** Qulay: muvaffaqiyat tekshiruvi */
    public function isOk(): bool
    {
        return $this->success === true;
    }

    /** Xabarni qulay olish (error yoki validationError’dan) */
    public function errorMessage(?string $fallback = null): ?string
    {
        // error.errMsg ustuvor
        if (is_array($this->error) && isset($this->error['errMsg'])) {
            return (string) $this->error['errMsg'];
        }

        return $fallback;
    }

    /** Validation bor-yo‘qligini tekshirish */
    public function hasValidationErrors(): bool
    {
        return ! empty($this->validationError);
    }

    /** Birinchi validation xabarini qaytarish */
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

    /** data ichida TokenResponseDto bo‘lsa accessToken qaytaradi, aks holda null */
    public function tokenOrNull(): ?string
    {
        if ($this->data instanceof TokenResponseDto) {
            return $this->data->accessToken ?? null;
        }

        if (is_array($this->data) && array_key_exists('accessToken', $this->data)) {
            return is_string($this->data['accessToken']) ? $this->data['accessToken'] : null;
        }

        return null;
    }

    /** data massiv bo‘lsa — massiv qaytaradi; DTO bo‘lsa toArray(), null bo‘lsa [] */
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
     * data’ni kerakli DTO ga map qilish (agar u hozircha array bo‘lib turgan bo‘lsa).
     * Masalan: $resp->dataAs(UserDto::class)
     */
    public function dataAs(string $dtoClass): ?object
    {
        if ($this->data === null) {
            return null;
        }

        if ($this->data instanceof $dtoClass) {
            return $this->data; // allaqachon to‘g‘ri turda
        }

        if (is_array($this->data)) {
            /** @var class-string<Data> $dtoClass */
            return $dtoClass::from($this->data);
        }

        // Masalan, TokenResponseDto bo‘lsa, lekin siz UserDto so‘rasangiz — mos emas
        return null;
    }
}
