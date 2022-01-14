<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\DTO\Groups\CommentDTO;
use App\Domain\Model\Groups\Comments\GroupComment;
use App\DataTransformer\Groups\CommentAttachmentTransformer;
use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

class CommentTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    function transform(GroupComment $comment): CommentDTO {

        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;

        $onBehalfOfGroup = $comment->onBehalfOfGroup();
        $owningGroup = $comment->owningGroup();
        
        /** @var Collection<string, Reaction> $reactions*/
        $reactions = $comment->reactions();
        
        $root = $comment->root();
        
        return new \App\DTO\Groups\CommentDTO(
            $comment->id(),
            $comment->text(),
            $root ? $root->id() : null,
            $comment->repliedId(),
            $attachmentDTO,
            $this->groupToSmallDTO($owningGroup),
            $onBehalfOfGroup,
            $onBehalfOfGroup ? null : $this->creatorToDTO($comment->creator()),
            $comment->repliesCount(),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
        );
    }
}