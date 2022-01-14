<?php
namespace App\Auth;

use \Firebase\JWT\JWT;

final class JwtAuthHS256
{

    private $issuer;
    private $lifetime;
    private $secret;

    public function __construct(
        string $issuer,
        string $secret,
        int $lifetime = 3600
    ) {
        $this->issuer = $issuer;
        $this->lifetime = $lifetime;
        $this->secret = $secret;
    }

    public function getLifetime(): int {
        return $this->lifetime;
    }

    public function createJwt($userId, $roles): string {
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time();
        $notbefore_claim = $issuedat_claim;
        $this->lifetime = $issuedat_claim + $this->lifetime;

        $token = array(
            "iss" => $this->issuer,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $this->lifetime,
            "data" => array(
                "userId" => $userId,
        ));

        return JWT::encode($token, $this->secret);
    }

    public function decodeToken(string $accessToken) : array {
        $decoded = JWT::decode($accessToken, $this->secret, array('HS256'));
        return json_decode(json_encode($decoded), true);
    }
}
