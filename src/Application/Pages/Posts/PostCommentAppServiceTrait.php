<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts;

use App\Domain\Model\Pages\Post\Comment\CommentRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\Comment\Comment;

trait PostCommentAppServiceTrait {
    
    protected CommentRepository $comments;

    function findCommentOrFail(string $commentId, bool $asTarget): ?Comment {
        $post = $this->comments->getById($commentId);
        
        $found = true;
        if(!$post) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new \App\Application\Exceptions\NotExistException("Comment $commentId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Comment $commentId not found");
        }
        return $post;
    }
}