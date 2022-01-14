<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\DTO\Pages\CommentDTO;
use App\Domain\Model\Pages\Comments\PageComment;
use App\DataTransformer\Pages\CommentAttachmentTransformer;
use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

class CommentTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(PageComment $comment): CommentDTO {
        //$rootComment = $comment->rootId();

        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;

        $onBehalfOfPage = $comment->onBehalfOfPage();
        /** @var ?\App\Domain\Model\Users\User\User $creator */
        $creator = $onBehalfOfPage ? null : $comment->creator();
        
        /** @var Collection<string, Reaction> $reactions*/
        $reactions = $comment->reactions();
        
        $root = $comment->root();
        
        return new \App\DTO\Pages\CommentDTO(
            $comment->id(),
            $comment->text(),
            $root ? $root->id() : null,
            $comment->repliedId(),
            $attachmentDTO,
            $this->pageToSmallDTO($comment->owningPage()),
            $onBehalfOfPage ? $this->pageToSmallDTO($onBehalfOfPage) : null,
            $creator ? $this->creatorToDTO($creator) : null,
            $comment->repliesCount(),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
        );
    }
}