<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\Patch;

use App\Application\BaseResponse;

class PatchResponse implements BaseResponse {
    public string $id;
    
    function __construct(string $id) {
        $this->id = $id;
    }
}
