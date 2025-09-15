<?php

namespace Katm\KatmSdk\HttpExceptions\Client;

use Katm\KatmSdk\HttpExceptions\KatmHttpException;

/**
 * UnprocessableEntityException
 *
 * HTTP 422 (Unprocessable Entity) holatida yuzaga keladigan exception.
 *
 * So‘rov sintaktik jihatdan to‘g‘ri, ammo semantik (mazmun) jihatdan noto‘g‘ri bo‘lsa,
 * masalan:
 * - So‘rov validatsiyadan o‘tmaydi
 * - Ma’lumotlar formati to‘g‘ri, ammo noto‘liq yoki mantiqan noto‘g‘ri
 * - Kerakli maydonlar yo‘q yoki noto‘g‘ri qiymatda
 *
 * Ushbu exception `ExceptionMapper::fromResponse()` metodida 422 status kodi asosida tashlanadi.
 */
class UnprocessableEntityException extends KatmHttpException {}
