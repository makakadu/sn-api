<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Users\Connection\Doctrine;

use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\ProfileCommentRepository;

class ProfileCommentDoctrineRepository extends AbstractDoctrineRepository implements ProfileCommentRepository {
    
    public function getById(string $id): ?\App\Domain\Model\ProfileComment {
        
    }

}
