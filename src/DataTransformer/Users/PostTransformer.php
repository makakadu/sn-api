<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Users\Post\Post;
use App\DTO\Users\UserPostDTO;
use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Common\Reaction;
use App\DataTransformer\SharedTransformer;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Criteria;
use App\DTO\Users\PostCommentDTO;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\Post\Comment\Comment;

class PostTransformer extends Transformer {
    use \App\DataTransformer\TransformerTrait;
    
    private SharedTransformer $sharedTransformer;
    private CommentRepository $comments;
    
    public function __construct(SharedTransformer $sharedTransformer, CommentRepository $comments) {
        $this->sharedTransformer = $sharedTransformer;
        $this->comments = $comments;
    }

    function transformOne(?User $requester, Post $post, int $commentsCount, string $commentsType, string $commentsOrder): UserPostDTO {
        $shared = $post->shared();
        
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $post->reactions();
        
        $comments = [];
        if($commentsCount) {
            $comments = $this->comments->getPartOfActiveByPost($post, null, $commentsCount, $commentsType, $commentsOrder);
        }
        
        return new UserPostDTO(
            $post->id(),
            $post->text(),
            $post->commentingIsDisabled(),
            $post->reactionsAreDisabled(),
            $post->isPublic(),
            $this->creatorToDTO($post->creator()),
            $post->createdAt()->getTimestamp() * 1000,
            $this->reactionsToDTO($post->reactions(), 20),
            $requester ? $this->prepareRequesterReaction($requester, $reactions) : null,
            $this->prepareReactionsCount($reactions),
            $shared ? $this->sharedTransformer->transform($requester, $shared) : null,
            $this->postCommentsToDTO($requester, $comments),
            $this->comments->getCountOfActiveByPost($post),
            $this->postAttachmentsToDTO($post->attachments())
        );
    }
    /**
     * @param array<int, Comment> $comments
     * @return array<int, PostCommentDTO>
     */
    function postCommentsToDTO(?User $requester, array $comments): array {
        $dtos = [];
        foreach($comments as $comment) {
            $dtos[] = $this->commentToDTO($requester, $comment);
        }
        return $dtos;
    }
    
    function commentToDTO(?User $requester, Comment $comment): PostCommentDTO {
        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;

        $onBehalfOfPage = false; //$comment->onBehalfOfPage();
        /** @var ?\App\Domain\Model\Users\User\User $creator */
        $creator = $onBehalfOfPage ? null : $comment->creator();

        /** @var Collection<string, Reaction> $reactions */
        $reactions = $comment->reactions();

        return new PostCommentDTO(
            $comment->id(),
            $comment->text(),
            $comment->rootId(),
            $comment->replied() ? $this->commentToDTO($requester, $comment->replied()) : null,
            $attachmentDTO,
            $creator ? $this->creatorToDTO($creator) : null,
            $onBehalfOfPage ? $this->pageToSmallDTO($onBehalfOfPage) : null,
            [],
            $comment->repliesCount(),
            $this->reactionsToDTO($comment->reactions(), 20),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
            $requester ? $this->prepareRequesterReaction($requester, $reactions) : null
        );
    }
    
    function postCommentsToDTO2(Collection $comments, int $commentsCount, string $commentsType, string $commentsOrder) {
        /** @var array<int, PostCommentDTO> $commentsCollection */
        $commentsCollection = [];
        
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('isDeleted', false));
        
        if($commentsType === 'root') {
            $criteria->andWhere(Criteria::expr()->eq('root', null));
        }
        if(\strtoupper($commentsOrder) === 'INTERESTING') {
            $criteria->orderBy('count(reactions) ASC');
        }
        elseif(\strtoupper($commentsOrder) === 'DESC') {
            $criteria->orderBy(array('id' => 'DESC'));
        }
                
        $criteria->setMaxResults($commentsCount);
        
        foreach($comments->matching($criteria)->toArray() as $comment) {
            $attachment = $comment->attachment();
            $attachmentDTO = $attachment
                ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;

            $onBehalfOfPage = $comment->onBehalfOfPage();
            /** @var ?\App\Domain\Model\Users\User\User $creator */
            $creator = $onBehalfOfPage ? null : $comment->creator();

            /** @var Collection<string, Reaction> $reactions */
            $reactions = $comment->reactions();

            $commentsCollection[] = new PostCommentDTO(
                $comment->id(),
                $comment->text(),
                $comment->rootId(),
                $comment->repliedId(),
                $attachmentDTO,
                $creator ? $this->creatorToDTO($creator) : null,
                $onBehalfOfPage ? $this->pageToSmallDTO($onBehalfOfPage) : null,
                $comment->repliesCount(),
                $this->prepareReactionsCount($reactions),
                $this->creationTimeToTimestamp($comment->createdAt()),
            );
        }
        return $commentsCollection;
    }
    
    /**
     * @param array<int, Post> $posts
     * @return array<int, UserPostDTO>
     */
    function transformMultiple(?User $requester, array $posts, int $commentsCount, string $commentsType, string $commentsOrder): array {
        /** @var array<int, UserPostDTO> */
        $transformed = [];
        foreach ($posts as $post) {
            $transformed[] = $this->transformOne($requester, $post, $commentsCount, $commentsType, $commentsOrder);
        }
        return $transformed;
    }
}