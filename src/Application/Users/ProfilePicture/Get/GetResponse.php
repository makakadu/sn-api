<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\Get;

use App\Application\BaseResponse;
use App\DTO\Users\PictureDTO;

class GetResponse implements BaseResponse {
    public PictureDTO $picture;
    
    function __construct(PictureDTO $picture) {
        $this->picture = $picture;
    }
}
