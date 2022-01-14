<?php
declare(strict_types=1);
namespace App\Application\Groups;

use App\Domain\Model\Groups\Post\PostRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\RequestParamsValidator;
use App\Domain\Model\Groups\Post\Post;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\ApplicationService;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Groups\Group\GroupRepository;
use App\Application\Exceptions\ForbiddenException;
use App\Domain\Model\Users\Post\Comment\Comment;
use App\Domain\Model\Users\Post\Comment\Reply\Reply;
use App\Application\Users\CreatePost\CreatePostRequest;
use App\Domain\Model\Users\Post\Comment\Reply\ReplyRepository;

abstract class PostAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    const TEXT = 'text';
    const MEDIA = 'media';
    
    protected PostRepository $posts;
            
    function __construct(
        PostRepository $posts,
        GroupRepository $groups,
        UserRepository $users
    ) {
        $this->users = $users;
        $this->groups = $groups;
        $this->posts = $posts;
    }
    
    protected function findPostOrFail($postId, bool $asTarget): Post {
        $post = $this->posts->getById($postId);
        
        if($post && $post->group()->isBlocked()) {
            throw new ForbiddenException(22, "Group-owner was blocked");
        } elseif($post) {
            return $post;
        }
        
        if($asTarget) {
            throw new NotExistException('Resource not found');
        } else {
            throw new UnprocessableRequestException("Post $postId not found");
        }
    }
            
    protected function findCommentOrFail(string $commentId, bool $asTarget): Comment {
        $comment = $this->comments->getById($commentId);
        
        if($comment) {
            if($comment->commentedPost()->isSoftlyDeleted()) {
                throw new NotExistException('Resource not found');
            } elseif($comment->group()->isBlocked()) {
                throw new ForbiddenException(33, "Group-owner was blocked");
            }
            return $comment;
        }
        
        if($asTarget) {
            throw new NotExistException('Resource not found');
        } else {
            throw new UnprocessableRequestException("Comment $commentId not found");
        }
    }
    
    protected function findReplyOrFail(string $replyId, bool $asTarget): Reply {
        $reply = $this->replies->getById($replyId);
        
        if($reply) {
            if($reply->commentedPost()->isSoftlyDeleted()) {
                throw new NotExistException('Resource not found');
            } elseif($reply->group()->isBlocked()) {
                throw new ForbiddenException(22, "Group-owner was blocked");
            }
            return $reply;
        }
        
        if($asTarget) {
            throw new NotExistException('Resource not found');
        } else {
            throw new UnprocessableRequestException("Reply $replyId not found");
        }
    }
    
    protected function validateText($value): void {
        $this->validator->string($value, "Text of post should be a string");
        $this->validator->maxLength($value, 500, "Text of post should have no more than 500 symbols");
    }
    
    protected function validateMedia($value): void {
        $this->validator->isArray($value, "Media should be an array");
        $this->validator->arrayLength($value, 2, "No more than 10 media files allowed in post");
    }
    
    protected function validateAreCommentsDisabled($value): void {
        $this->validator->boolean($value, "'areCommentsDisabled' should be a boolean");
    }
    
    protected function validateCommentMedia($value): void {
        $this->validator->isArray($value, "Media should be an array");
        $this->validator->arrayLength($value, 2, "No more than 2 media files allowed in comment");
    }
    
    protected function validateCommentText($value): void {
        $this->validator->string($value, "Text of comment should be a string");
        $this->validator->maxLength($value, 500, "Text of comment should have no more than 500 symbols");
    }
    
    protected function findMedia(array $mediaIds): array {
        $found = [];
        foreach($mediaIds as $id) {
            $media = $this->media->getById($id);
            if($media) { $found[] = $media; }
        }
        return $found;
    }
    
    protected function validateRequestData(CreatePostRequest $request): void {        
        $this->validateText($request->text);
        $this->validateMedia($request->attachments);
        $this->validateAreCommentsDisabled($request->disableComments);
    }
}