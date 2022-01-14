<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Post\Post;

class UserPostToDtoTransformer {
    /**
     * @return array<mixed>
     */
    function transform(Post $post, int $commentsCount, string $commentsType, string $commentsOrder): array {
        $attachmentToDtoVisitor = new PostAttachmentToDtoVisitor();
        $sharedToDtoVisitor = new SharedToDtoVisitor();
        
        $creator = $post->creator();

        $attachments = [];
        foreach($post->attachments() as $attachment) {
            $attachments[] = $attachment->accept($attachmentToDtoVisitor);
        }

        $sharedDto = null;
        $shared = $post->shared();
        if($shared) {
            $sharedDto = $shared->accept($sharedToDtoVisitor);
        }

        $picture = $creator->currentPicture();
        
        $reactions = $post->reactions();
        $this->reactionsCount['all'] = $reactions->count();
        
        $reactionsTypes = (new \ReflectionClass(\App\Domain\Model\Common\ReactionsTypes::class))->getConstants();
        
        /** @var array<string, string> $reactionsCount */
        $reactionsCount = [];
        foreach($reactionsTypes as $key => $reactionType) {
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq("reactionType", $reactionType));
            $count = $reactions->matching($criteria)->count();
            if($count) {
                $reactionsCount[$reactionType] = $count;
            }
        }
        
        /*
        $comments = [];
        if($commentsCount) {
            $criteria = Criteria::create();
            if($commentsType === 'root') {
                $criteria->where(Criteria::expr()->eq($commentsType, null));
            }
            $criteria
                ->orderBy(array('id' => $commentsOrder))
                ->setMaxResults($commentsCount);
            $comments = $post->comments()->matching($criteria)->toArray();

            
            foreach($comments as $comment) {
                $commentCreator = $comment->creator();
                $reactions = $comment->reactions();
                $reactionsCount = [];
                $reactionsCount['all'] = $reactions->count();

                foreach($reactionsTypes as $reactionType) {
                    $criteria = Criteria::create()
                        ->where(Criteria::expr()->eq("reactionType", $reactionType));
                    $count = $reactions->matching($criteria)->count();
                    if($count) {
                        $reactionsCount[$reactionType] = $count;
                    }
                }

                $rootComment = $comment->root();
                $picture = $commentCreator->currentPicture();
                $comments[] = [
                    'id' => $comment->id(),
                    'root_id' => $rootComment ? $rootComment->id() : null,
                    'replied_id' => $comment->repliedId(),
                    'text' => $comment->text(),
                    'creator' => [
                        'id' => $commentCreator->id(),
                        'fullname' => $commentCreator->fullname(),
                        'picture' => $picture ? $picture->small() : null
                    ],
                    'replies_count' => $comment->replies()->count(),
                    'reactions_count' => $reactionsCount
                ];
            }
        } 
        */
        /** @var array<string, mixed> $transformed */
        $transformed = [
            'id' => $post->id(),
            'creator' => [
                'id' => $creator->id(),
                'avatar' => $picture ? $picture->small() : null,
                'firstname' => $creator->firstName(), 
                'lastname' => $creator->lastName()
            ],
            'attachments' => $attachments,
            'shared' => $sharedDto,
            'comments' => $this->transformProfileComments($post->comments(), $commentsCount, $commentsType, $commentsOrder),
            'reactions_count' => $reactionsCount
        ];
        
        return $transformed;
    }
    
    /**
     * @param Collection<string, ProfileReaction> $reactions
     * @return array<mixed>
     */
    function prepareProfileReactionsCount(Collection $reactions): array {
        
    }
    
    /**
     * @param Collection<string, ProfileComment> $comments
     * @return array<mixed>
     */
    function transformProfileComments(Collection $comments, int $commentsCount, string $commentsType, string $commentsOrder): array {
        /** @var array<string, mixed> $commentsDto */
        $commentsDto = [];
        
        if($commentsCount) {
            $criteria = Criteria::create();
            if($commentsType === 'root') {
                $criteria->where(Criteria::expr()->eq($commentsType, null));
            }
            $criteria
                ->orderBy(array('id' => $commentsOrder))
                ->setMaxResults($commentsCount);

            /** @var \App\Domain\Model\Users\Photos\Comment\Comment $comment */
            foreach($comments->matching($criteria)->toArray() as $comment) {
                $commentCreator = $comment->creator();
                $reactions = $comment->reactions();
                $reactionsCount = [];
                $reactionsCount['all'] = $reactions->count();

                $reactionsTypes = (new \ReflectionClass(\App\Domain\Model\Common\ReactionsTypes::class))->getConstants();
                foreach($reactionsTypes as $reactionType) {
                    $criteria = Criteria::create()
                        ->where(Criteria::expr()->eq("reactionType", $reactionType));
                    $count = $reactions->matching($criteria)->count();
                    if($count) {
                        $reactionsCount[$reactionType] = $count;
                    }
                }

                $rootComment = $comment->root();
                $picture = $commentCreator->currentPicture();
                
                $commentsDto = [
                    'id' => $comment->id(),
                    'root_id' => $rootComment ? $rootComment->id() : null,
                    'replied_id' => $comment->repliedId(),
                    'text' => $comment->text(),
                    'creator' => [
                        'id' => $commentCreator->id(),
                        'fullname' => $commentCreator->fullname(),
                        'picture' => $picture ? $picture->small() : null
                    ],
                    'replies_count' => $comment->replies()->count(),
                    'reactions_count' => $reactionsCount
                ];
            }
        }
        
        return $commentsDto;
    }
}