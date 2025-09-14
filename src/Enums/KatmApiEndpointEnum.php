<?php

namespace Katm\KatmSdk\Enums;

enum KatmApiEndpointEnum: string
{
    case Auth = '/auth/login';
    case AuthClient = '/auth/init-client';
    case SubmitReport = '/report/submit-request';
    case GetTariffByClient = '/report/get-tariff-by-client';
    case GetReport = '/report/get-report';
    case CreditBanActive = '/client/credit/ban/activate';
    case CreditBanStatus = '/client/credit/ban/status';
}
