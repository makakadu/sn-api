<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetReplies;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\Comment\CommentRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\DataTransformer\Users\PostTransformer;

class GetReplies implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PostRepository $posts;
    private CommentRepository $comments;
    private PostTransformer $postsTransformer;
    
    public function __construct(UserRepository $users, PostRepository $posts, PostTransformer $postsTransformer, CommentRepository $comments) {
        $this->users = $users;
        $this->posts = $posts;
        $this->comments = $comments;
        $this->postsTransformer = $postsTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {               
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;

        $comment = $this->comments->getById($request->commentId);
        if(!$comment) {
            throw new \App\Application\Exceptions\NotExistException('Comment not found');
        }
        
        $replies = $this->comments->getPartOfActiveByRootComment($comment, $request->offsetId, $request->count ?? 10);
        $count = $this->comments->getCountOfActiveByRootComment($comment);
        
        $dtos = $this->postsTransformer->postCommentsToDTO($requester, $replies);
        
        return new GetRepliesResponse($dtos, $count);
    }
}