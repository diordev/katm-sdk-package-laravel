<?php

namespace Katm\KatmSdk\Enums;

enum KatmAuthTypeEnum: string
{
    case AuthNone = 'none';
    case AuthBasic = 'basic';
    case AuthBearer = 'bearer';
}
