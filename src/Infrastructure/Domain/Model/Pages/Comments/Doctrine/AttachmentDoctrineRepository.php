<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Comments\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Comments\AttachmentRepository;
use App\Domain\Model\Pages\Comments\Attachment;

class AttachmentDoctrineRepository extends AbstractDoctrineRepository implements AttachmentRepository {

    protected string $entityClass = Attachment::class;

    public function getById(string $id): ?Attachment {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
