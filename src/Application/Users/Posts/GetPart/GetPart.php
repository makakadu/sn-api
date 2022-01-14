<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\GetPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\PostRepository;
use App\DataTransformer\Users\PostTransformer;
use App\Application\GetRequestParamsValidator;

class GetPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PostRepository $posts;
    private PostTransformer $postsTransformer;
    
    public function __construct(UserRepository $users, PostRepository $posts, PostTransformer $postsTransformer) {
        $this->users = $users;
        $this->posts = $posts;
        $this->postsTransformer = $postsTransformer;
    }
    
    public function execute(BaseRequest $request): GetPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $owner = $this->findUserOrFail($request->pageId, true, null);
        
        $countParam = ($request->count ? (int)$request->count : 20) + 1;
        
        
        $posts = $this->posts->getPartOfActiveAndAccessibleToRequesterByOwner(
            $requester, $owner, $request->cursor, $countParam,
             $request->order ? $request->order : 'DESC'
        );
        
        $cursor = null;
        if((count($posts) - $request->count) === 1) {
            $cursor = $posts[count($posts) -1]->id();
            array_pop($posts);
        }

        $count = $this->posts->getCountOfActiveAndAccessibleToRequesterByOwner($requester, $owner);
        return new GetPartResponse(
            $this->postsTransformer->transformMultiple(
                $requester,
                $posts,
                !\is_null($request->commentsCount) ? (int)$request->commentsCount : 2,
                $request->commentsType ? $request->commentsType : "root",
                $request->commentsOrder ? $request->commentsOrder : "ASC"
            ),
            $cursor,
            $count
       );
    }
    
    function validate(GetRequest $request): void {
        GetRequestParamsValidator::validateCountParam($request->count);
        GetRequestParamsValidator::validateOffsetIdParam($request->cursor);
        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
    }
}
