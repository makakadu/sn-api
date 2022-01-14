<?php

declare(strict_types=1);

namespace App\Application;

class FBAuthenticator {
    private $appId;
    private $secret;
    private $redirect_uri;

    public function __construct(string $appId, string $secret, string $redirect_uri) {
        $this->appId = $appId;
        $this->secret = $secret;
        $this->redirect_uri = $redirect_uri;
    }

    public function getAccessToken(string $code) : array {
        $conn = \curl_init("https://graph.facebook.com/oauth/access_token?client_id=$this->appId&redirect_uri=$this->redirect_uri&client_secret=$this->secret&code=$code");
        \curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        \curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
        \curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        return json_decode(\curl_exec($conn), true);
    }

    public function getUserData(string $token, string $code) : array {
        return json_decode(
            file_get_contents(
                "https://graph.facebook.com/v6.0/me?access_token=$token&fields=id,name,email,location"
            ),
            true
        );
    }

    public function getUserData2(string $token, string $code) : array {


    }
}
