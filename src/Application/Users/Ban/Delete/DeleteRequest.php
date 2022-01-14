<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Delete;

use App\Application\BaseRequest;

class DeleteRequest implements BaseRequest {
    public string $requesterId;
    public string $banId;

    function __construct(string $requesterId, string $banId) {
        $this->requesterId = $requesterId;
        $this->banId = $banId;
    }
}
