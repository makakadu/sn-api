<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\CreateManager;

use App\Application\BaseResponse;

class CreateManagerResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
