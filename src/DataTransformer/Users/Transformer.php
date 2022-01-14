<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\DTO\Users\ReactionDTO;
use App\DTO\Users\CommentDTO;
use App\Domain\Model\Users\ProfileReaction;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\Domain\Model\Users\Post\Attachment as PostAttachment;
use App\DTO\Common\AttachmentDTO;
use Doctrine\Common\Collections\Criteria;

class Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    /**
     * @param Collection<string, ProfileComment> $comments
     * @return array<int, CommentDTO>
     */
    function commentsToDTO(Collection $comments, int $commentsCount, string $commentsType, string $commentsOrder): array {
        /** @var array<int, CommentDTO> $commentsCollection */
        $commentsCollection = [];
        
        $criteria = Criteria::create();
        if($commentsType === 'root') {
            $criteria->where(Criteria::expr()->eq($commentsType, null));
        }
        $criteria
            ->orderBy(array('id' => $commentsOrder))
            ->setMaxResults($commentsCount);

        $commentToDTO = new CommentTransformer();
        
        foreach($comments->matching($criteria)->toArray() as $comment) {
            $commentsCollection[] = $commentToDTO->transform($comment);
        }
        
        return $commentsCollection;
    }
    
    /**
     * @param Collection<string, ProfileReaction> $reactions
     * @return array<int, ReactionDTO>
     */
    function reactionsToDTO(Collection $reactions, int $count): array {
        /** @var array<int, ReactionDTO> $reactionsCollection */
        $reactionsCollection = [];
        
        $criteria = Criteria::create();
        $criteria
            ->orderBy(array('createdAt' => 'DESC'))
            ->setMaxResults($count);

        
        
        /** @var ArrayCollection<int, ProfileReaction> $reactions */
        $reactions = $reactions;
        foreach($reactions->matching($criteria)->toArray() as $reaction) {
            $reactionsCollection[] = $this->reactionToDTO($reaction);
        }
        
        return $reactionsCollection;
    }
    
    function reactionToDTO(\App\Domain\Model\Users\ProfileReaction $reaction): ReactionDTO {
        $reactionToDTO = new ReactionTransformer();
        return $reactionToDTO->transform($reaction);
    }
    
    /**
     * @param Collection<string, PostAttachment> $attachments
     * @return array<int, AttachmentDTO>
     */
    function postAttachmentsToDTO(Collection $attachments) {
        $attachmentTransformer = new PostAttachmentsTransformer();
        return $attachmentTransformer->transform($attachments);
    }

}