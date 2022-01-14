<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Comments;

use App\Domain\Model\Users\User\User;

interface AttachmentRepository extends \App\Domain\Repository {

    function getById(string $id): ?Attachment;
    
}
