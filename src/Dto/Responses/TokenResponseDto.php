<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

final class TokenResponseDto extends Data
{
    public function __construct(
        public string $accessToken,
    ) {}
}
