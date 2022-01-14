<?php
declare(strict_types=1);
namespace App\Application\Users\Posts;

use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\Post\Comment\Comment;
use App\Application\Common\CommentAppServiceTrait;
use App\Domain\Model\Users\Post\Comment\CommentRepository as PostCommentRepository;

trait PostCommentAppService {
    use CommentAppServiceTrait;

    private PostCommentRepository $comments;

    protected function findCommentOrFail(string $commentId, bool $asTarget): Comment {
        $comment = $this->comments->getById($commentId);
        
        $notFound = false;
        if(!$comment) {// || $comment->isDeleted()) {
            $notFound = true;
        }
//        elseif($comment->commentedPost()->creator()->isDeleted()){
//            $notFound = true;
//        }
        
        if($notFound && $asTarget) {
            throw new NotExistException("Comment $commentId not found");
        } elseif($notFound && !$asTarget) {
            throw new UnprocessableRequestException(111, "Comment $commentId not found");
        }
        return $comment;
    }

}