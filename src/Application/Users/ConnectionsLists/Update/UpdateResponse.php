<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Update;

use App\Application\BaseResponse;

class UpdateResponse implements BaseResponse {
    public string $message;
    
    function __construct(string $message) {
        $this->message = $message;
    }


}
