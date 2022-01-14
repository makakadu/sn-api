<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use App\Domain\Model\Users\Post\Post;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Application\ApplicationService;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\Exceptions\ValidationException;
use Assert\Assertion;

abstract class PostAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    const TEXT = 'text';
    const MEDIA = 'media';
    
    protected PostRepository $posts;
    protected UserPostsAuth $postsAuth;
    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        UserPostsAuth $postsAuth
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsAuth = $postsAuth;
    }
    
    protected function findPost(string $postId): ?Post {
        return $this->posts->getById($postId);
    }
        
//    protected function findCommentOrFail($postId, bool $asTarget): Post {
//        $post = $this->findPost($postId);
//        
//        if(!$post && $asTarget) {
//            throw new NotExistException('Resource not found');
//        } elseif(!$post && !$asTarget) {
//            throw new UnprocessableRequestException(222, "Post $postId not found");
//        }
//        return $post;
//    }
    
    function findPostOrFail(string $postId, bool $asTarget): Post {
        $post = $this->findPost($postId);
        
        if(!$post && $asTarget) {
            throw new \App\Application\Exceptions\NotExistException('Resource not found');
        } elseif(!$post && !$asTarget) {
            throw new \App\Application\Exceptions\UnprocessableRequestException(222, "Post $postId not found"); // Здесь уже нужно более подробное объяснение причины
        }
        return $post;
    }

    protected function findCommentOrFail(string $commentId): ?Comment {
//        $comment = $this->
//        if(!$comment) {
//            throw new NotFoundException("Not found");
//        }
//        return $comment;
    }
    
    protected function findCommentReactionOrFail(string $reactionId, string $commentId, string $postId): Reaction {
        $post = $this->posts->getById($postId);
        if(!$post) {
            throw new NotFoundException("Post not found");
        }
        
        $comment = $post->getComment($commentId);
        if(!$comment) {
            throw new NotFoundException("Comment not found");
        }
        
        $reaction = $comment->getReaction($reactionId);
        if(!$comment) {
            throw new NotFoundException("Not found");
        }
        return $reaction;
    }
    
    /** @param mixed $value */
    protected function validateParamText($value): void {
        try {
            Assertion::string($value, "Param 'text' should be a string");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
        try {
            Assertion::maxLength($value, 500, "Text of post should have no more than 500 symbols");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    protected function validateParamAttachments($value): void {
        try {
            Assertion::isArray($value, "Attachments should be an array");
            if(count($value) > 10) {
                throw new \App\Application\Exceptions\ValidationException("No more than 10 media files allowed in post");
            }
            foreach ($value as $attachment) {
                Assertion::isArray($attachment, "Attachment data should be an array");
                Assertion::keyExists($attachment['id'], "Attachment data should contains 'id' key");
                Assertion::keyExists($attachment['type'], "Attachment data should contains 'type' key");
                Assertion::string($attachment['id'], "Property id in attachment data should be a string");
                Assertion::string($attachment['type'], "Property type in attachment data should be a string");
            }
        } catch(\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    protected function validateParamDisableComments($value): void {
        try {
            Assertion::boolean($value, "Param 'disableComments' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    protected function validateParamIsPublic($value): void {
        try {
            Assertion::boolean($value, "Param 'public' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    /** @param mixed $value */
    protected function validateParamShared($value): void {
        try {
            Assertion::isArray($value, "Param 'shared' should be an array");
            foreach ($value as $attachment) {
                Assertion::isArray($attachment, "Shared data should be an array");
                Assertion::keyExists($attachment['id'], "Shared data should contains 'id' key");
                Assertion::keyExists($attachment['type'], "Shared data should contains 'type' key");
                Assertion::string($attachment['id'], "Property id in shared data should be a string");
                Assertion::string($attachment['type'], "Property type in shared data should be a string");
            }
        } catch(\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
}