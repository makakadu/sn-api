<?php
declare(strict_types=1);
namespace App\Infrastructure\Domain\Model\Pages\Videos\Doctrine;

use App\Domain\Model\Pages\Videos\Video;
use App\Infrastructure\Persistence\Doctrine\AbstractDoctrineRepository;
use App\Domain\Model\Pages\Videos\VideoRepository;

class VideoDoctrineRepository extends AbstractDoctrineRepository implements VideoRepository {
    protected string $entityClass = Video::class;
    
    public function addComment(\App\Domain\Model\Users\User\User $user, string $comment): void {
        
    }

    public function comments(): \Doctrine\Common\Collections\Collection {
        
    }

    public function commentsAreDisabled(): bool {
        
    }

    public function creator(): \App\Domain\Model\Users\User\User {
        
    }

    public function disableComments(): void {
        
    }

    public function enableComments(): void {
        
    }

    public function isAllowedByPrivacyTo(\App\Domain\Model\Users\User\User $user, string $action): bool {
        
    }

    public function removeComment(string $commentId): void {
        
    }

    public function getById(string $id): ?Video {
        return $this->entityManager->find($this->entityClass, $id);
    }

}
