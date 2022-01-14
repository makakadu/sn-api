<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Groups\Photos\Doctrine;

use App\Domain\Model\Groups\Photos\Photo;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Groups\Photos\PhotoRepository;

class PhotoDoctrineRepository extends AbstractDoctrineRepository implements PhotoRepository {
    protected string $entityClass = Photo::class;
    
    public function getById(string $id): ?Photo {
        
    }

}
