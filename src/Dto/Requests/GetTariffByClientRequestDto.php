<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

final class GetTariffByClientRequestDto extends Data
{
    public function __construct(
        public string $pClientId
    ) {}

}
