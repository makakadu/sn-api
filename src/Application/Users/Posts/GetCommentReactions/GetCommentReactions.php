<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetCommentReactions;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\Post\Comments\ReactionRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\PostTransformer;

class GetCommentReactions implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    use \App\Application\Users\Posts\PostCommentAppService;
    
    private UserPostsAuth $postsAuth;
    private PostTransformer $postTransformer;
    
    function __construct(
        CommentRepository $comments,
        UserRepository $users,
        UserPostsAuth $postsAuth,
        PostTransformer $postTransformer
    ) {
        $this->users = $users;
        $this->comments = $comments;
        $this->postsAuth = $postsAuth;
        $this->postTransformer = $postTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {               
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $comment = $this->findCommentOrFail($request->commentId, false);
//        if($requester) {
//            $this->postsAuth->failIfCannotSee($requester, $comment);
//        } else {
//            $this->postsAuth->failIfGuestsCannotSee($comment);
//        }

        $criteria = \Doctrine\Common\Collections\Criteria::create()->setMaxResults(10);
        $reactions = $comment->reactions()->matching($criteria);

        $dtos = [];
        foreach ($reactions as $reaction) {
            $dtos[] = $this->postTransformer->reactionToDTO($reaction);
        }
        return new GetCommentReactionsResponse($dtos, $this->postTransformer->prepareReactionsCount($comment->reactions()));
    }
}