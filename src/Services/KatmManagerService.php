<?php

namespace Katm\KatmSdk\Services;

use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Dto\Responses\KatmResponseDto;
use Katm\KatmSdk\Services\Auth\KatmAuthService;
use Katm\KatmSdk\Services\Credit\KatmCreditService;

final class KatmManagerService
{
    public function __construct(
        public readonly KatmAuthService $authService,
        public readonly KatmCreditService $creditBanService
        // public readonly ReportService $report, ...
    ) {}

    /**
     * Auth API — login/password orqali token olish.
     */
    public function authenticate(): KatmResponseDto
    {
        return $this->authService->authenticate();
    }

    /**
     * Mijozni ro‘yxatdan o‘tkazish (bearer bilan).
     * 401 bo‘lsa avtomatik re-auth qilib qayta urinadi.
     */
    public function initClient(InitClientRequestDto $dto): KatmResponseDto
    {
        return $this->authService->initClient($dto);
    }

    public function creditBan(InitClientRequestDto $dto): KatmResponseDto
    {
        return $this->creditBanService->creditBanActive($dto);
    }

    public function creditBanStatus(InitClientRequestDto $dto): KatmResponseDto
    {
        return $this->creditBanService->creditBanStatus($dto);
    }
}
