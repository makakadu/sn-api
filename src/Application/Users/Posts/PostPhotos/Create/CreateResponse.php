<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Create;

use App\Application\BaseResponse;

class CreateResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id= $id;
    }
}
