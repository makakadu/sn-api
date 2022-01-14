<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\DTO\Users\CommentDTO;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\DataTransformer\Users\CommentAttachmentTransformer;
use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

class CommentTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(ProfileComment $comment): CommentDTO {
        //$rootComment = $comment->rootId();

        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;

        
//        /** @var ?\App\Domain\Model\Pages\Page\Page $page */
//        $page = $comment->page();
        /** @var ?\App\Domain\Model\Users\User\User $creator */
//        $creator = $page ? null : $comment->creator();
        
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $comment->reactions();
        
        return new \App\DTO\Users\CommentDTO(
            $comment->id(),
            $comment->text(),
            $comment->rootId(),
            $comment->repliedId(),
            $attachmentDTO,
            $this->creatorToDTO($comment->creator(0)),
            //$page ? $this->pageToSmallDTO($page) : null,
            $comment->repliesCount(),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
//            $comment->isDeleted(),
//            $comment->isDeletedByManager()
        );
    }
    
}