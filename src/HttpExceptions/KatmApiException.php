<?php

namespace Katm\KatmSdk\HttpExceptions;

/**
 * KatmApiException
 *
 * Ushbu istisno (exception) KATM API’dan HTTP 200 OK javobi kelgan holatlarda,
 * lekin javob body ichida `success=false` bo‘lsa tashlanadi.
 *
 * Ya’ni bu transport yoki HTTP darajadagi xato emas, balki
 * **biznes-darajadagi xato** hisoblanadi.
 *
 * Misol uchun:
 *  {
 *      "success": false,
 *      "error": {
 *          "errMsg": "Merchant topilmadi",
 *          "code": 101
 *      }
 *  }
 *
 * Bunday vaziyatda ExceptionMapper::ensureSuccess() metodidan foydalaniladi
 * va natijada KatmApiException tashlanadi.
 *
 * Foydalanuvchi uchun qulaylik:
 * - Transport-level xatolar (tarmoq, timeout) boshqa exceptionlar bilan ajratiladi.
 * - HTTP-level xatolar (400, 401, 403, 500) alohida exceptionlar bilan ajratiladi.
 * - KatmApiException esa aynan API’dan kelgan biznes xatoni bildiradi.
 */
class KatmApiException extends KatmHttpException {}
