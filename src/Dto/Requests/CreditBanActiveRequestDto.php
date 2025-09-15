<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

final class CreditBanActiveRequestDto extends Data
{
    public function __construct(
        public string $pIdentifier,
        public string $pFullName,
        public string $pIdenDate,
        public KatmSubjectTypeEnum $pSubjectType,
    ) {}

}
