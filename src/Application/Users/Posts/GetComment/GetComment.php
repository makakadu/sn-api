<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetComment;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Users\PostTransformer;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Application\Users\Posts\PostParamsValidator;
use App\Domain\Model\Users\Post\Comment\CommentRepository;

class GetComment implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    
    private PostTransformer $postTransformer;
    private UserPostsAuth $postsAuth;
    private CommentRepository $comments;
    
    public function __construct(CommentRepository $comments, PostTransformer $postTransformer, UserPostsAuth $postsAuth, UserRepository $users) {
        $this->postTransformer = $postTransformer;
        $this->postsAuth = $postsAuth;
        $this->users = $users;
        $this->comments = $comments;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validate($request);
        
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $comment = $this->comments->getById($request->commentId);
        if(!$comment) {
            throw new \App\Application\Exceptions\NotExistException('Not found');
        }

        return new GetCommentResponse($this->postTransformer->commentToDTO(
            $requester, $comment,
        ));
    }

}
