<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Post\SuggestedPost;

use App\Domain\Model\DomainException;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;

class SuggestedPost extends \App\Domain\Model\Pages\Post\AbstractPost {
    
    private bool $hideSignatureIfEdited;
    private bool $rejected = false;
    private ?\DateTime $rejectedAt = null;
    
    /**
     * @param array<mixed> $attachments
     */
    function __construct(
        Page $owningPage,
        User $creator,
        string $text,
        array $attachments,
        bool $addSignature,
        bool $hideSignatureIfEdited
    ) {
        if(count($attachments) > 10) {
            throw new DomainException('Max 10 attachments can be in post');
        }
        parent::__construct($owningPage, $creator, $text, $attachments, $addSignature);
        $this->hideSignatureIfEdited = $hideSignatureIfEdited;
    }
    
    function delete(User $initiator): void {
        if(!$this->creator->equals($initiator)) {
            throw new DomainException('No rights to delete suggested post');
        }
        if(!$this->deleted) {
            $this->deletedAt = new \DateTime('now');
        }
        $this->deleted = true;
    }
    
    function restore(User $initiator): void {
        if(!$this->creator->equals($initiator)) {
            throw new DomainException('No rights to restore suggested post');
        }
        $this->deleted = false;
        $this->deletedAt = null;
    }
    
    function reject(User $initiator) {
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('No rights to reject suggested post');
        }
        if(!$this->rejected) {
            $this->rejectedAt = new \DateTime('now');
        }
        $this->rejected = true;
    }
    
    function undoRejection(User $initiator) {
        if(!$this->owningPage->isAdminOrEditor($initiator)) {
            throw new DomainException('No rights to cancel suggested post rejection');
        }
        $this->rejectedAt = null;
        $this->rejected = false;
    }
  
    function equals(self $post): bool {
        return $this->id === $post->id;
    }

    function canSee(User $user): bool {
        return $this->creator->equals($user)
            || $this->owningPage->isManager($user);
    }
    
    function hideSignatureIfEdited(): bool {
        return $this->hideSignatureIfEdited;
    }
    
    public function isRejected(): bool {
        return $this->rejected;
    }
    
}
