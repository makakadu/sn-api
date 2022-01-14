<?php
declare(strict_types=1);
namespace App\DataTransformer\Pages;

use App\Domain\Model\Pages\Post\Post;
use App\DTO\Pages\PagePostDTO;
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
    
    function transformOne(?User $requester, Post $post, int $commentsCount, string $commentsType, string $commentsOrder): PagePostDTO {
        $shared = $post->shared();
        $addSignature = $post->addSignature();
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $post->reactions();

        $owningPage = $post->owningPage();
        $requesterIsAdminOrEditor = $requester ? $owningPage->isAdminOrEditor($requester) : null;
        
        return new PagePostDTO(
            $post->id(),
            $post->text(),
            $post->commentsAreDisabled(),
            $post->reactionsAreDisabled(),
            $this->pageToSmallDTO($owningPage),
            $requesterIsAdminOrEditor ? $this->creatorToDTO($post->publisher()) : null,
            $addSignature || $requesterIsAdminOrEditor ? $this->creatorToDTO($post->creator()) : null,
            $addSignature,
            $post->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($post->reactions(), 10),
            $this->prepareReactionsCount($reactions),
            $shared ? $this->sharedTransformer->transform($requester, $shared) : null,
            $this->commentsToDTO($post->comments(), $commentsCount, $commentsType, $commentsOrder),
            $this->postAttachmentsToDTO($post->attachments())
        );
    }
    
    /**
     * @param array<int, Post> $posts
     * @return array<int, PagePostDTO>
     */
    function transformMultiple(?User $requester, array $posts, int $commentsCount, string $commentsType, string $commentsOrder): array {
        /** @var array<int, PagePostDTO> */
        $transformed = [];
        foreach ($posts as $post) {
            $transformed[] = $this->transformOne($requester, $post, $commentsCount, $commentsType, $commentsOrder);
        }
        return $transformed;
    }
    
}