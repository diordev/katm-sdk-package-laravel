<?php

namespace Katm\KatmSdk\Enums;

/**
 * KatmApiEndpointEnum
 *
 * KATM API endpoint'larining rasmiy URL yo‘llarini ifodalovchi enum.
 *
 * Bu enum API chaqiriqlarni yagona manbada saqlab, har qanday endpoint’ga
 * string o‘rniga tiplangan formatda murojaat qilish imkonini beradi.
 *
 * Misol:
 * - KatmApiEndpointEnum::Auth->value → '/auth/login'
 * - KatmApiEndpointEnum::GetReport->value → '/report/get-report'
 */
enum KatmApiEndpointEnum: string
{
    /** Avtorizatsiya (foydalanuvchi login) */
    case Auth = '/auth/login';

    /** Mijozni ro‘yxatdan o‘tkazish (init-client) */
    case AuthClient = '/auth/init-client';

    /** Hisobotni yakuniy tarzda jo‘natish */
    case SubmitReport = '/report/submit-request';

    /** Mijoz bo‘yicha tarif ma’lumotlarini olish */
    case GetTariffByClient = '/report/get-tariff-by-client';

    /** Mijoz bo‘yicha hisobot olish */
    case GetReport = '/report/get-report';

    /** Kredit bo‘yicha aktiv ban qo‘yish (yoki tekshirish) */
    case CreditBanActive = '/client/credit/ban/activate';

    /** Kredit bo‘yicha ban statusini olish */
    case CreditBanStatus = '/client/credit/ban/status';
}
