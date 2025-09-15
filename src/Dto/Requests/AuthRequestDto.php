<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

final class AuthRequestDto extends Data
{
    public function __construct(
        public string $login,
        public string $password,
    ) {}
}
