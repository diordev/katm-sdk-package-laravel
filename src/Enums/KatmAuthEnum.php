<?php

namespace Katm\KatmSdk\Enums;

enum KatmAuthEnum: string
{
    case AuthNone = 'none';
    case AuthBasic = 'basic';
    case AuthBearer = 'bearer';
}
