<?php
declare(strict_types=1);
namespace App\DataTransformer\Groups;

use App\Domain\Model\Groups\Post\Post;
use App\DTO\Groups\GroupPostDTO;
use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;
use App\DataTransformer\SharedTransformer;
use App\Domain\Model\Users\User\User;

class PostTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    private SharedTransformer $sharedTransformer;
    
    public function __construct(SharedTransformer $sharedTransformer) {
        $this->sharedTransformer = $sharedTransformer;
    }
    
    function transform(User $requester, Post $post, int $commentsCount, string $commentsType, string $commentsOrder): GroupPostDTO {
        $shared = $post->shared();
        $onBehalfOfGroup = $post->onBehalfOfGroup();
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $post->reactions();
        
        return new GroupPostDTO(
            $post->id(),
            $post->text(),
            $post->commentingIsDisabled(),
            $onBehalfOfGroup ? null : $this->creatorToDTO($post->creator()),
            $this->groupToSmallDTO($post->owningGroup()),
            $onBehalfOfGroup,
            $post->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($post->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $shared ? $this->sharedTransformer->transform($requester, $shared) : null,
            $this->commentsToDTO($post->comments(), $commentsCount, $commentsType, $commentsOrder),
            $this->postAttachmentsToDTO($post->attachments())
        );
    }
    
}