<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmGenderTypeEnum;
use Spatie\LaravelData\Data;

class InitClientRequestDto extends Data
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

}
