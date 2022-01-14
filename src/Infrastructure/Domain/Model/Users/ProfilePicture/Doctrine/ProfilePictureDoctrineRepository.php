<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\ProfilePicture\Doctrine;

use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository;

class ProfilePictureDoctrineRepository extends AbstractDoctrineRepository implements ProfilePictureRepository {
    protected string $entityClass = ProfilePicture::class;
    public function getById(string $id): ?ProfilePicture {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
