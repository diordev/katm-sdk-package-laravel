<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmLanguageTypeEnum;
use Spatie\LaravelData\Data;

class SubmitReportRequestDto extends Data
{
    public function __construct(
        public KatmLanguageTypeEnum $language,
        public string $pClientId
    ) {}

}
