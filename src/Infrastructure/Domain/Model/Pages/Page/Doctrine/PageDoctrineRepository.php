<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Page\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Pages\Page\Page;

class PageDoctrineRepository extends AbstractDoctrineRepository implements PageRepository {

    protected string $entityClass = Page::class;
    
    public function getByName(string $name): ?Page {
        
    }

    public function getById(string $id): ?Page {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
