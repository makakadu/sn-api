<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Delete;

use App\Application\BaseResponse;

class DeleteResponse implements BaseResponse {
    
    public string $message;
    
    function __construct(string $message) {
        $this->message = $message;
    }
    
}
