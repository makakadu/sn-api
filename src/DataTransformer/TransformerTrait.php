<?php
declare(strict_types=1);
namespace App\DataTransformer;

use App\Domain\Model\Users\User\User;
use App\Application\Users\CommentTransformer;
use App\DTO\CreatorDTO;
use App\Domain\Model\Users\ProfileReaction;
use App\Domain\Model\Pages\Page\Page;
use Doctrine\Common\Collections\Collection;
use App\DTO\Users\CommentDTO;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\DataTransformer\Users\PostAttachmentTransformer;
use App\Domain\Model\Common\Shares\Shared;
use App\DTO\Common\AttachmentDTO;
use App\DTO\Shares\SharedDTO;
use App\DTO\Users\ReactionDTO;
use App\Domain\Model\Groups\Group\Group;
use App\DTO\Groups\GroupSmallDTO;
use App\DTO\Pages\PageSmallDTO;
use App\Domain\Model\Groups\GroupReaction;
use App\Domain\Model\Common\Post\PostAttachment;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Post\Attachment as ProfilePostAttachment;
use App\Domain\Model\Groups\Post\Attachment as GroupPostAttachment;
use App\DTO\Common\DTO;
use App\Domain\Model\Common\ReactionsTypes;
use App\DTO\Users\UserSmallDTO;

trait TransformerTrait {

    private function creationTimeToTimestamp(\DateTime $time): int {
        return $time->getTimestamp() * 1000;
    }
    
    function creatorToDTO(User $user): CreatorDTO {
        $picture = $user->currentPicture();
        $active = !$user->isBlocked();
        
        return new CreatorDTO(
            $user->id(),
            $picture && $active ? $picture->versions()['cropped_medium'] : null,
            $user->firstName(), 
            $user->lastName(),
            $user->username()->username()
        );
    }
    
    function userToSmallDTO(User $user): UserSmallDTO {
        $picture = $user->currentPicture();
        $active = !$user->isBlocked();
        
        return new UserSmallDTO(
            $user->id(),
            $picture && $active ? $picture->versions()['cropped_medium'] : null,
            $user->firstName(), 
            $user->lastName(),
            $user->username()->username()
        );
    }
    
    function profileToSmallDTO(User $owner): \App\DTO\Users\ProfileSmallDTO {
        $picture = $owner->currentPicture();
        
        return new \App\DTO\Users\ProfileSmallDTO(
            $owner->id(),
            $picture ? $picture->small() : null,
            $owner->firstName(), 
            $owner->lastName()
        );
    }
    
    function pageToSmallDTO(Page $page): PageSmallDTO {
        $picture = $page->currentPicture();
        $active = !$page->isBlocked();
        
        return new PageSmallDTO(
            $page->id(),
            $picture && $active ? $picture->small() : null,
            $page->name()
        );
    }
    
    function groupToSmallDTO(Group $group): GroupSmallDTO {
        $picture = $group->currentPicture();
        
        return new GroupSmallDTO(
            $group->id(),
            $picture ? $picture->small() : null,
            $group->name()
        );
    }

    /**
     * @param Collection<string, \App\Domain\Model\Common\Reaction> $reactions
     * @return array<mixed>
     */
    function prepareReactionsCount(Collection $reactions): array {
        $reactionsTypes = (new \ReflectionClass(\App\Domain\Model\Common\ReactionsTypes::class))->getConstants();
        
        /** @var array<string, string> $reactionsCount */
        $reactionsCount = [];
        
        /** @var ArrayCollection <string, \App\Domain\Model\Common\Reaction> $reactions */
        $reactions2 = $reactions;
        
        foreach($reactionsTypes as $key => $reactionType) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("reactionType", $reactionType));
            $count = $reactions2->matching($criteria)->count();

            if($count) {
                $reactionsCount[] = ['type' => $reactionType, 'count' => $count];
            }
            
        }
        return $reactionsCount;
    }
    
    /**
     * @param Collection<string, \App\Domain\Model\Common\Reaction> $reactions
     */
    function prepareRequesterReaction(User $requester, Collection $reactions): ?ReactionDTO {
        /** @var ArrayCollection <string, \App\Domain\Model\Common\Reaction> $reactions */
        $reactions2 = $reactions;
        
        $criteria = Criteria::create()->where(Criteria::expr()->eq("creator", $requester));
        $result = $reactions2->matching($criteria);
        if(count($result)) {
            /** @var \App\Domain\Model\Common\Reaction $reaction */
            $reaction = $result[0];
            return new \App\DTO\Users\ReactionDTO(
                $reaction->id(), 
                $reaction->getReactionType(), 
                $this->creatorToDTO($reaction->creator()),
                $this->creationTimeToTimestamp($reaction->createdAt())
            );
        } else {
            return null;
        }
    }
    
//    /**
//     * @param Collection<string, ProfilePostAttachment> $attachments
//     * @return array<int, AttachmentDTO>
//     */
//    function profilePostAttachmentsToDTO(Collection $attachments) {
//        $attachmentTransformer = new PostAttachmentTransformer();
//        
//        /** @var array<int, AttachmentDTO> $attachments */
//        $attachmentsDTOs = [];
//        /** @var ProfilePostAttachment $attachment */
//        foreach($attachments as $attachment) {
//            $attachmentsDTOs[] = $attachment->acceptAttachmentVisitor($attachmentTransformer);
//        }
//        return $attachmentsDTOs;
//    }
//    
//    /**
//     * @param Collection<string, GroupPostAttachment> $attachments
//     * @return array<int, AttachmentDTO>
//     */
//    function groupPostAttachmentsToDTO(Collection $attachments) {
//        $attachmentTransformer = new PostAttachmentTransformer();
//        
//        /** @var array<int, AttachmentDTO> $attachments */
//        $attachmentsDTOs = [];
//        /** @var GroupPostAttachment $attachment */
//        foreach($attachments as $attachment) {
//            $attachmentsDTOs[] = $attachment->acceptAttachmentVisitor($attachmentTransformer);
//        }
//        return $attachmentsDTOs;
//    }
//    
//    /**
//     * @param Collection<string, PostAttachment> $attachments
//     * @return array<int, AttachmentDTO>
//     */
//    function postAttachmentsToDTO(Collection $attachments) {
//        $attachmentTransformer = new PostAttachmentTransformer();
//        return $attachmentTransformer->transform($attachments);
//
//    }
}