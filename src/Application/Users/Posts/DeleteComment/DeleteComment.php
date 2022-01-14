<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\DeleteComment;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comments\CommentRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\PostRepository;

class DeleteComment extends PostAppService {
    use \App\Application\Users\Posts\PostCommentAppService;
    
    private CommentRepository $postComments;
    
    function __construct(
        UserRepository $users,
        PostRepository $posts,
        UserPostsAuth $postsAuth,
        CommentRepository $postComments
    ) {
        parent::__construct($posts, $users, $postsAuth);
        $this->postComments = $postComments;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $comment = $this->findCommentOrFail($request->commentId, true);
        
        //$this->postsAuth->failIfCannotRemoveComment($requester, $comment, $request->byManager);
        $comment->delete($request->byManager);

        return new DeleteCommentResponse('Ok');  
    }
    
}