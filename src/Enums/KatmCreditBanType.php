<?php

namespace Katm\KatmSdk\Enums;

enum KatmCreditBanType: string
{
    case ACTIVE = 'active';
    case STATUS = 'status';

    public function endpoint(): string
    {
        return match ($this) {
            self::ACTIVE => KatmApiEndpointEnum::CreditBanActive->value,
            self::STATUS => KatmApiEndpointEnum::CreditBanStatus->value,
        };
    }

    public function dtoMethod(): string
    {
        return match ($this) {
            self::ACTIVE => 'toCreditBanActiveDto',
            self::STATUS => 'toCreditBanStatusDto',
        };
    }
}
