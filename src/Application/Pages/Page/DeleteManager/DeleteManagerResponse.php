<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\DeleteManager;

use App\Application\BaseResponse;

class DeleteManagerResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
