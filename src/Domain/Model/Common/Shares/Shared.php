<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Shares;

use App\Domain\Model\Users\Post\Post;

abstract class Shared {
    protected string $id;
    protected string $originalId;
    protected \DateTime $createdAt;
    protected \DateTime $originalCreatedAt;
    
    /**
     * @template T
     * @param SharedVisitor <T> $visitor
     * @return T
     */
    abstract function acceptSharedVisitor(SharedVisitor $visitor);
    
    abstract function shared(): ?Shareable;
    
    public function originalId(): string {
        return $this->originalId;
    }
    
    function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }
}
