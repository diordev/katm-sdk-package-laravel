<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmGenderTypeEnum;
use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

/**
 * InitClientRequestDto
 *
 * KATM API orqali mijozni ro‘yxatdan o‘tkazish (initClient) uchun zarur barcha ma’lumotlarni ifodalovchi DTO.
 *
 * Shuningdek, ushbu DTO dan `CreditBanActiveRequestDto` va `CreditBanStatusRequestDto` obyektlariga konvertatsiya qilish imkoniyati mavjud.
 *
 * @property string $pPinfl Foydalanuvchining PINFL yoki INN
 * @property string $pDocSeries Hujjat seriyasi (pasport)
 * @property string $pDocNumber Hujjat raqami
 * @property string $pFirstName Ismi
 * @property string $pLastName Familiyasi
 * @property string $pMiddleName Otasining ismi
 * @property string $pBirthDate Tug‘ilgan sana (format: YYYY-MM-DD)
 * @property string $pIssueDocDate Hujjat berilgan sana
 * @property string $pExpiredDocDate Hujjat amal qilish muddati
 * @property KatmGenderTypeEnum $pGender Jinsi (enum: Male/Female)
 * @property string $pDistrictId Hudud (tuman/shahar) identifikatori
 * @property string $pResAddress Yashash manzili
 * @property string $pRegAddress Ro‘yxatdagi manzili
 * @property string $pPhone Telefon raqami
 * @property string $pEmail Elektron pochta manzili
 */
final class InitClientRequestDto extends Data
{
    public function __construct(
        public string $pPinfl,
        public string $pDocSeries,
        public string $pDocNumber,
        public string $pFirstName,
        public string $pLastName,
        public string $pMiddleName,
        public string $pBirthDate,
        public string $pIssueDocDate,
        public string $pExpiredDocDate,
        public KatmGenderTypeEnum $pGender,
        public string $pDistrictId,
        public string $pResAddress,
        public string $pRegAddress,
        public string $pPhone,
        public string $pEmail
    ) {}

    /**
     * `CreditBanActiveRequestDto` uchun kerakli ma'lumotlarni hosil qiladi
     *
     * @return array Mavjud DTO dan kredit ban active formatidagi massiv
     */
    public function toCreditBanActiveDto(): array
    {
        $dto = new CreditBanActiveRequestDto(
            pIdentifier: $this->pPinfl,
            pFullName: $this->pFirstName.' '.$this->pLastName.' '.$this->pMiddleName,
            pIdenDate: $this->pBirthDate,
            pSubjectType: KatmSubjectTypeEnum::Individual
        );

        return $dto->toArray();
    }

    /**
     * `CreditBanStatusRequestDto` uchun kerakli ma'lumotlarni hosil qiladi
     *
     * @return array Mavjud DTO dan kredit ban status formatidagi massiv
     */
    public function toCreditBanStatusDto(): array
    {
        $dto = new CreditBanStatusRequestDto(
            pIdentifier: $this->pPinfl,
            pSubjectType: KatmSubjectTypeEnum::Individual
        );

        return $dto->toArray();
    }
}
