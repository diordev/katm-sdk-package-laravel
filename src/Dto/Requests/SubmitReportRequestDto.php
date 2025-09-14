<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmLanguageEnum;
use Spatie\LaravelData\Data;

class SubmitReportRequestDto extends Data
{
    public function __construct(
        public KatmLanguageEnum $language,
        public string $pClientId
    ) {}

}
