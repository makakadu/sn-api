<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Post\Attachment as PostAttachment;
use App\Domain\Model\Pages\PageReaction;
use App\DTO\Pages\PageReactionDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Pages\Comments\PageComment;
use App\DTO\Common\AttachmentDTO;
use App\DTO\Pages\CommentDTO;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;

class Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    /**
     * @param Collection<string, PageComment> $comments
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
        
        /** @var ArrayCollection<string, PageComment> $comments */
        $comments = $comments;

        foreach($comments->matching($criteria)->toArray() as $comment) {
            $commentsCollection[] = $commentToDTO->transform($comment);
        }
        
        return $commentsCollection;
    }
    
    /**
     * @param Collection<string, PageReaction> $reactions
     * @return array<int, PageReactionDTO>
     */
    function reactionsToDTO(Collection $reactions, int $count): array {
        /** @var array<int, PageReactionDTO> $reactionsCollection */
        $reactionsCollection = [];
        
        $criteria = Criteria::create();
        $criteria
            ->orderBy(array('createdAt' => 'DESC'))
            ->setMaxResults($count);

        $reactionToDTO = new ReactionTransformer();

        /** @var ArrayCollection<string, PageReaction> $reactions */
        $reactions = $reactions;
        foreach($reactions->matching($criteria)->toArray() as $reactions) {
            $reactionsCollection[] = $reactionToDTO->transform($reactions);
        }
        
        return $reactionsCollection;
    }

//    /**
//     * @param Collection<string, \App\Domain\Model\Users\ProfileReaction> $reactions
//     * @return array<mixed>
//     */
//    function prepareReactionsCount($reactions): array {
//        $reactionsTypes = (new \ReflectionClass(\App\Domain\Model\Common\ReactionsTypes::class))->getConstants();
//        
//        /** @var array<string, string> $reactionsCount */
//        $reactionsCount = [ 'all' => $reactions->count() ];
//        
//        foreach($reactionsTypes as $key => $reactionType) {
//            $criteria = Criteria::create()
//                ->where(Criteria::expr()->eq("reactionType", $reactionType));
//            $count = $reactions->matching($criteria)->count();
//            if($count) {
//                $reactionsCount[$reactionType] = $count;
//            }
//        }
//        return $reactionsCount;
//    }
    
    /**
     * @param Collection<string, PostAttachment> $attachments
     * @return array<int, AttachmentDTO>
     */
    function postAttachmentsToDTO(Collection $attachments) {
        $attachmentTransformer = new PostAttachmentsTransformer();
        return $attachmentTransformer->transform($attachments);
    }
}