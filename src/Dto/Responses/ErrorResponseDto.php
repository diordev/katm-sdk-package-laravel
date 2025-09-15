<?php

namespace Katm\KatmSdk\Dto\Responses;

use Spatie\LaravelData\Data;

final class ErrorResponseDto extends Data
{
    public function __construct(
        public int $errId,
        public bool $isFriendly,
        public string $errMsg,
    ) {}
}
