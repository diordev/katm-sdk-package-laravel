<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmSubjectTypeEnum;
use Spatie\LaravelData\Data;

class CreditBanStatusRequestDto extends Data
{
    public function __construct(
        public string $pIdentifier,
        public KatmSubjectTypeEnum $pSubjectType,
    ) {}

}
