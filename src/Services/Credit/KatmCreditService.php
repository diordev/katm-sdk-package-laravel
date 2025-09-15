<?php

namespace Katm\KatmSdk\Services\Credit;

use Katm\KatmSdk\Dto\Requests\InitClientRequestDto;
use Katm\KatmSdk\Dto\Responses\KatmResponseDto;
use Katm\KatmSdk\Enums\KatmAuthTypeEnum;
use Katm\KatmSdk\Enums\KatmCreditBanType;
use Katm\KatmSdk\HttpExceptions\Client\BadRequestException;
use Katm\KatmSdk\HttpExceptions\Client\UnauthorizedException;
use Katm\KatmSdk\Services\AbstractHttpClientService;
use Katm\KatmSdk\Services\Auth\KatmAuthService;

final class KatmCreditService extends AbstractHttpClientService
{
    public function __construct(private readonly KatmAuthService $auth)
    {
        parent::__construct();
    }

    public function creditBan(KatmCreditBanType $type, InitClientRequestDto $dto): KatmResponseDto
    {
        // bearer yo‘q — auth
        if (($this->bearer ?? '') === '') {
            $this->auth->authenticate();
            $this->withBearer($this->auth->currentToken());
        }

        $endpoint = $type->endpoint();
        $method = $type->dtoMethod();
        $payload = $dto->{$method}(); // to‘g‘ri payload builder

        $send = fn () => $this->post($endpoint, $payload, KatmAuthTypeEnum::AuthBearer->value);

        try {
            $res = $send();

        } catch (UnauthorizedException) {
            // 401 -> re-auth va retry
            $this->auth->authenticate();
            $this->withBearer($this->auth->currentToken());
            $res = $send();

        } catch (BadRequestException $e) {
            // 400 -> client not found -> init + retry
            if ($this->isClientNotFound($e)) {
                $this->auth->initClient($dto);
                $res = $send();
            } else {
                throw $e;
            }
        }

        return KatmResponseDto::from($res);
    }

    private function isClientNotFound(BadRequestException $e): bool
    {
        dd('ini');
        if (property_exists($e, 'errId') && (int) $e->errId === 102) {
            return true;
        }
        $m = mb_strtolower($e->getMessage());

        return str_contains($m, 'пользователь не найден')
            || str_contains($m, 'client not found')
            || str_contains($m, 'not found');
    }
}
