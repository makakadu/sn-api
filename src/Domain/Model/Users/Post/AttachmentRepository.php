<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Post;

use App\Domain\Repository;

interface AttachmentRepository extends Repository {
    function getById(string $id): ?Attachment;
    
    /**
     * @param array<int, string> $ids
     * @return array<Attachment>
     */
    function getByIds(array $ids): array;
}
