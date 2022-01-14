<?php
declare(strict_types=1);
namespace App\Application\Groups\Posts;

use App\Domain\Model\Users\User\User;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Groups\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\ApplicationService;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Authorization\GroupPostsAuth;
use Assert\Assertion;

abstract class PostAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    const TEXT = 'text';
    const MEDIA = 'media';
    
    protected PostRepository $posts;
    protected GroupPostsAuth $postsAuth;

    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        GroupPostsAuth $postsAuth, 
        GroupRepository $groups
    ) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsAuth = $postsAuth;
        $this->groups = $groups;
    }
    
    protected function findPost(string $postId): ?Post {
        return $this->profilePosts->getById($postId);
    }
        
    protected function findCommentOrFail($postId, bool $asTarget): Post {
        $post = $this->findPost($postId);
        
        if(!$post && $asTarget) {
            throw new NotExistException('Resource not found');
        } elseif(!$post && !$asTarget) {
            throw new UnprocessableRequestException(['code' => 222, 'message' =>"Post $postId not found"]);
        }
        return $post;
    }
    
    protected function findPostOrFail($postId, bool $asTarget): Post {
        $post = $this->findPost($postId);
        
        if(!$post && $asTarget) {
            throw new NotExistException('Resource not found');
        } elseif(!$post && !$asTarget) {
            throw new UnprocessableRequestException(['code' => 222, 'message' =>"Post $postId not found"]);
        }
        return $post;
    }
    
//    protected function findReactionOrFail(string $reactionId): Reaction {
//        $reaction = $this->reactions->getById($reactionId);
//        // Возможно Reaction будет содержать ссылку на владельца
//        if(!$reaction || ($reaction && $reaction->post()->owner()->isDeleted())) {
//            throw new NotExistException("Reaction ".$reactionId." not found");
//        }
//        return $reaction;
//    }
     
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
    
    protected function validateParamDisableComments($value): void {
        try {
            Assertion::boolean($value, "Param 'disableComments' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
    protected function validateParamIsPublic($value): void {
        try {
            Assertion::boolean($value, "Param 'public' should be a boolean");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
    }
    
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