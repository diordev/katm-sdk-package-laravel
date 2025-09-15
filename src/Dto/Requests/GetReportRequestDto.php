<?php

namespace Katm\KatmSdk\Dto\Requests;

use Katm\KatmSdk\Enums\KatmLanguageTypeEnum;
use Spatie\LaravelData\Data;

final class GetReportRequestDto extends Data
{
    public function __construct(
        public KatmLanguageTypeEnum $language,
        public string $pClientId,
        public string $pToken
    ) {}

}
