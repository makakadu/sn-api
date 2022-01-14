<?php
declare(strict_types=1);
namespace App\Application\Common\Feed;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\DataTransformer\Users\PostTransformer;

class Feed implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PostRepository $posts;
    private PostTransformer $postsTransformer;
    
    public function __construct(UserRepository $users, PostRepository $posts, PostTransformer $postsTransformer) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsTransformer = $postsTransformer;
    }
    
    public function execute(BaseRequest $request): FeedResponse {
        $requester = $this->findRequesterOrFail($request->requesterId);
        
        $countParam = ($request->count ? (int)$request->count : 20) + 1;
        
        $posts = $this->posts->getFeed2($requester, $request->cursor, $countParam);
        
        $cursor = null;
        if((count($posts) - $request->count) === 1) {
            $cursor = $posts[count($posts) -1]->id();
            array_pop($posts);
        }
        
        $dtos = $this->postsTransformer->transformMultiple(
            $requester,
            $posts,
            !\is_null($request->commentsCount) ? (int)$request->commentsCount : 2,
            $request->commentsType ? $request->commentsType : "root",
            $request->commentsOrder ? $request->commentsOrder : "ASC"
        );
        
        return new FeedResponse($dtos, $cursor);
    }
    
//    function validate(GetRequest $request): void {
//        GetRequestParamsValidator::validateCountParam($request->count);
//        GetRequestParamsValidator::validateOffsetIdParam($request->cursor);
//        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
//        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
//        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
//    }
}
