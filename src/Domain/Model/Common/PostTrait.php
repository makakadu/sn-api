<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\DomainException;

trait PostTrait {
    
    protected int $viewsCount;
    protected bool $disableComments;
    protected bool $disableReactions;
    protected string $text;
    
    /** @var array<string> $attachmentsOrder */
    protected array $attachmentsOrder = [];
    //protected ?\DateTime $deletedAt;
    
    /** @return array<string> */
    function mediaOrder(): array {
        return $this->attachmentsOrder;
    }
    
    function changeText(string $text): void {
        $this->text = $text;
    }
    
    private function failIfIsDeleted(): void {
        
    }

    function text(): string {
        return $this->text;
    }
    
    function disableComments(): void {
        $this->disableComments = true;
    }
    
    function enableComments(): void {
        $this->disableComments = false;
    }

    function commentingIsDisabled(): bool {
        return $this->disableComments;
    }

    function reactionsAreDisabled(): bool {
        return $this->disableReactions;
    }
    
    function isDeleted(): bool {
        return (bool)$this->deletedAt;
    }
    
    function setDeletedAt(?\DateTime $deletedAt): void {
        $this->deletedAt = $deletedAt;
    }
    
    function remove(): void {
        $this->deletedAt = new \DateTime('now');
    }
    
}
