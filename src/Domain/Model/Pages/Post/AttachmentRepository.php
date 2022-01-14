<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post;

use App\Domain\Repository;

interface AttachmentRepository extends Repository {
    function getById(string $id): ?Attachment;
}
