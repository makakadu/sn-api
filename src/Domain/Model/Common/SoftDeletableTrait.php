<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

trait SoftDeletableTrait {
    protected ?\DateTime $deletedAt;
            
    function isDeleted(): bool {
        return (bool)$this->deletedAt;
    }

    function deletedAt(): ?\DateTime {
        return $this->deletedAt;
    }
    
    function setDeletedAt(?\DateTime $deletedAt): void {
        $this->deletedAt = $deletedAt;
    }
}