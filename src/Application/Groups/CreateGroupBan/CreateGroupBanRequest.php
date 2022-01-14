<?php
declare(strict_types=1);
namespace App\Application\Groups\CreateGroupBan;

use App\Application\BaseRequest;

class CreateGroupBanRequest implements BaseRequest {
    public string $requesterId;
    
    public function __construct(string $requesterId) {
        $this->requesterId = $requesterId;
    }
}
