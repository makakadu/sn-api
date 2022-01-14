<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Photos\Photo;

interface PostAttachableVisitor {
    function visitPhoto(Photo $photo): array;
    //function getVideoData(Video $photo): array;
}