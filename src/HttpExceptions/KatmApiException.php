<?php

namespace Katm\KatmSdk\HttpExceptions;

/**
 * Class KatmApiException
 *
 * API javobining `success: false` bo‘lishi natijasida tashlanadigan biznes-darajadagi xatolik.
 *
 * Bu exception quyidagi holatda yuzaga keladi:
 * - HTTP javob kodi 200 OK
 * - Ammo body ichida `success: false` bo‘lib, `error.errMsg` mavjud
 *
 * Ushbu exception HTTP/transport darajalariga taalluqli emas — bu sof biznes xatolik bo‘lib,
 * `ExceptionMapper::ensureSuccess()` orqali aniqlanadi va tashlanadi.
 *
 * ### Misol javob:
 * ```json
 * {
 *   "success": false,
 *   "error": {
 *     "errId": 101,
 *     "errMsg": "Merchant topilmadi",
 *     "isFriendly": true
 *   }
 * }
 * ```
 *
 * ### Foydali holatlar:
 * - API `validation`, `not found`, `permission` yoki boshqa biznes mantiqdagi xatolarni bildirsa
 * - Transport-level (`ConnectionException`) yoki HTTP-level (`UnauthorizedException`) bilan aralashmasligi kerak
 *
 * @see \Katm\KatmSdk\HttpExceptions\ExceptionMapper::ensureSuccess()
 */
class KatmApiException extends KatmHttpException {}
