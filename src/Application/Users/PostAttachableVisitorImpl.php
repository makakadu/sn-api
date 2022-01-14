<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Photos\Photo\Photo;
use App\Domain\Model\Users\Photos\Photo;

class PostAttachableVisitorImpl {
    function visitPhoto(Photo $photo): array {
        return [
            'type' => 'photo',
            'id' => $photo->id(),
            'width' => $photo->width(),
            'height' => $photo->widht(),
            'preview' => $photo->preview()
        ];
    }
    //function getVideoData(Video $photo): array;
}