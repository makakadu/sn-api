<?php
declare(strict_types=1);
namespace App\Application\Users\GetPhoto;

use App\Domain\Model\Users\Photos\Photo;

class GetPhotoResponse implements \App\Application\BaseResponse {

    public function __construct(Photo $photo) {

    }
}
