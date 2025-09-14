<?php

namespace Katm\KatmSdk\Dto\Requests;

use Spatie\LaravelData\Data;

class AuthRequestDto extends Data
{
    public function __construct(
        public string $login,
        public string $password,
    ) {}
}
