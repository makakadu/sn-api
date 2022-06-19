<?php
declare(strict_types=1);
namespace App\Application\Users\Cover\Get;

use App\Application\BaseResponse;
use App\DTO\Users\CoverDTO;

class GetResponse implements BaseResponse {
    public CoverDTO $cover;
    
    function __construct(CoverDTO $cover) {
        $this->cover = $cover;
    }
}
