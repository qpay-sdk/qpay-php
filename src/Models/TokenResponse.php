<?php

declare(strict_types=1);

namespace QPay\Models;

class TokenResponse
{
    public function __construct(
        public readonly string $tokenType,
        public readonly int $refreshExpiresIn,
        public readonly string $refreshToken,
        public readonly string $accessToken,
        public readonly int $expiresIn,
        public readonly string $scope,
        public readonly string $notBeforePolicy,
        public readonly string $sessionState,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tokenType: $data['token_type'] ?? '',
            refreshExpiresIn: (int) ($data['refresh_expires_in'] ?? 0),
            refreshToken: $data['refresh_token'] ?? '',
            accessToken: $data['access_token'] ?? '',
            expiresIn: (int) ($data['expires_in'] ?? 0),
            scope: $data['scope'] ?? '',
            notBeforePolicy: $data['not-before-policy'] ?? '',
            sessionState: $data['session_state'] ?? '',
        );
    }
}
