<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\DataTransformer\Pages\PostTransformer;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Application\Pages\Posts\PostParamsValidator;

class Get {
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\AppServiceTrait;
    
    private PostTransformer $postTransformer;
    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        PostTransformer $postTransformer
    ) {
        $this->posts = $posts;
        $this->users = $users;
        $this->postTransformer = $postTransformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $this->validateRequest($request);
        
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $post = $this->findPostOrFail($request->postId, true);

        return new GetResponse($this->postTransformer->transformOne(
            $requester, $post,
            $request->commentsCount ? (int)$request->commentsCount : 1,
            $request->commentsType ? $request->commentsType : "root",
            $request->commentsOrder ? $request->commentsOrder : "ASC" 
        ));
    }
    
    function validate(GetRequest $request): void {
        PostParamsValidator::validateCommentsCountParam($request->commentsCount);
        PostParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc']);
        PostParamsValidator::validateCommentsTypeParam($request->commentsType);
    }
}
