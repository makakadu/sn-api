<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Post\Doctrine;

use App\Domain\Model\Users\Post\Attachment;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Post\AttachmentRepository;

class AttachmentDoctrineRepository extends AbstractDoctrineRepository implements AttachmentRepository {
    protected string $entityClass = Attachment::class;

    public function getById(string $id): ?Attachment {
        return $this->entityManager->find($this->entityClass, $id);
    }
    
    /**
     * @param array<int, string> $ids
     * @return array<Attachment>
     */
    function getByIds(array $ids): array {
        $repository = $this->entityManager->getRepository($this->entityClass);
        return $repository->findBy(['id' => $ids]);
    }

}
