<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmGenderTypeEnum;
use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

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

    public function toCreditBanActiveDto(): array
    {
        $dto = new CreditBanActiveRequestDto(
            pIdentifier: $this->pPinfl,
            pFullName: $this->pFirstName.' '.$this->pLastName.' '.$this->pMiddleName,
            pIdenDate: $this->pBirthDate,
            pSubjectType: KatmSubjectTypeEnum::Individual);

        return $dto->toArray();

    }

    public function toCreditBanStatusDto(): array
    {
        $dto = new CreditBanStatusRequestDto(
            pIdentifier: $this->pPinfl,
            pSubjectType: KatmSubjectTypeEnum::Individual
        );

        return $dto->toArray();
    }
}
