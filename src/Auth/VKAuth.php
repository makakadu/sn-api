<?php

namespace App\Auth;

use \Firebase\JWT\JWT;

final class VKAuth {

    private $appId;
    private $secret;

    public function __construct(
        string $appId,
        string $secret
    ) {
        $this->appId = $appId;
        $this->secret = $secret;
    }

    public function redirect(): string {
        return 'kek';
    }

    public function decodeToken(string $accessToken) : array {
        return [];
    }
}
