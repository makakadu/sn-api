<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Create;

use App\Application\BaseRequest;

class CreateRequest implements BaseRequest {
    public string $requesterId;
    public $userId;
//    public $minutes;
//    public $message;

    function __construct(string $requesterId, $userId) {//, $minutes, $message) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
//        $this->minutes = $minutes;
//        $this->message = $message;
    }

}
