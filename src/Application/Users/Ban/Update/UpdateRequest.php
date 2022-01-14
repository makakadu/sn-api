<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Update;

use App\Application\BaseRequest;

class UpdateRequest implements BaseRequest {
    public string $requesterId;
    public string $banId;
    public $payload;

    function __construct(string $requesterId, string $banId, $payload) {
        $this->requesterId = $requesterId;
        $this->banId = $banId;
        $this->payload = $payload;
    }

}
