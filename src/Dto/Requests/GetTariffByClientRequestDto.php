<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

class GetTariffByClientRequestDto extends Data
{
    public function __construct(
        public string $pClientId
    ) {}

}
