<?php
declare(strict_types=1);
namespace App\Application\Users\Auth\GetMe;

use App\Application\BaseResponse;

class GetMeResponse implements BaseResponse {
    public string $id;
    public string $username;
    public string $email;
    public ?string $avatar;
    
    public function __construct(string $id, string $username, string $email, ?string $avatar) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->avatar = $avatar;
    }


}
