<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateReaction;

use App\Application\BaseResponse;

class CreateReactionResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
