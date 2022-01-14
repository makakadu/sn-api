<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\Domain\Model\Groups\Comments\GroupComment;
use App\Domain\Model\Groups\GroupReaction;
use App\DTO\Groups\CommentDTO;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\DTO\Groups\GroupReactionDTO;
use App\DTO\Common\AttachmentDTO;
use App\Domain\Model\Groups\Post\Attachment as PostAttachment;
use Doctrine\Common\Collections\Criteria;

class Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    /**
     * @param Collection<string, GroupComment> $comments
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
        
        /** @var ArrayCollection<string, GroupComment> $comments */
        $comments = $comments;

        foreach($comments->matching($criteria)->toArray() as $comment) {
            $commentsCollection[] = $commentToDTO->transform($comment);
        }
        
        return $commentsCollection;
    }
    
    /**
     * @param Collection<string, GroupReaction> $reactions
     * @return array<int, GroupReactionDTO>
     */
    function reactionsToDTO(Collection $reactions, int $count): array {
        /** @var array<int, GroupReactionDTO> $reactionsCollection */
        $reactionsCollection = [];
        
        $criteria = Criteria::create();
        $criteria
            ->orderBy(array('createdAt' => 'DESC'))
            ->setMaxResults($count);

        $reactionToDTO = new ReactionTransformer();
        
        /** @var ArrayCollection<string, GroupReaction> $reactions */
        $reactions = $reactions;
        foreach($reactions->matching($criteria)->toArray() as $reactions) {
            $reactionsCollection[] = $reactionToDTO->transform($reactions);
        }
        
        return $reactionsCollection;
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