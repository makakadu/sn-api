<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\PatchManager;

use App\Application\BaseResponse;

class PatchManagerResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
