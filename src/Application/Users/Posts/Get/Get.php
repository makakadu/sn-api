<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Users\PostTransformer;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Application\Users\Posts\PostParamsValidator;

class Get implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Users\Posts\PostAppServiceTrait;
    
    private PostTransformer $postTransformer;
    private UserPostsAuth $postsAuth;
    
    public function __construct(PostTransformer $postTransformer, UserPostsAuth $postsAuth, PostRepository $posts, UserRepository $users) {
        $this->postTransformer = $postTransformer;
        $this->postsAuth = $postsAuth;
        $this->users = $users;
        $this->posts = $posts;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $this->validate($request);
        
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $post = $this->findPostOrFail($request->postId, true);
        
        if($requester) {
            $this->postsAuth->failIfCannotSee($requester, $post);
        } else {
            $this->postsAuth->failIfGuestsCannotSee($post);
        }
        
        return new GetResponse($this->postTransformer->transformOne(
            $requester, $post,
            $request->commentsCount ? (int)$request->commentsCount : 4,
            $request->commentsType ? (string)$request->commentsType : "root",
            $request->commentsOrder ? (string)$request->commentsOrder : "ASC",
            $request->fields ? explode(',', $request->fields) : []
        ));
    }
    
    function validate(GetRequest $request): void {
//        PostParamsValidator::validateCommentsTypeParam($request->commentsType);
//        PostParamsValidator::validateCommentsCountParam($request->commentsCount);
//        PostParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
    }
}
