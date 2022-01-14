<?php
declare(strict_types=1);
namespace App\Domain\Model;

trait EntityTrait {
    protected \DateTime $createdAt;
    protected string $id;
    protected int $version;
    
    function createdAt(): \DateTime {
        return $this->createdAt;
    }
    
    function id(): string {
        return $this->id;
    }
}
