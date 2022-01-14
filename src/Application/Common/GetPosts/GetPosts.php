<?php
declare(strict_types=1);
namespace App\Application\Common\GetPosts;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\PostRepository;

class GetPosts implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PostRepository $posts;

    function __construct(UserRepository $users, PostRepository $posts) {
        $this->posts = $posts;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
                ? $this->findRequesterOrFail($request->requesterId) : null;

        
        $posts = $this->posts->getAllAccessibleToRequester(
            $requester,
            $request->offset,
            $request->text,
            $request->count ? (int)$request->count : 50,
            $request->order ? \mb_strtoupper($request->order) : 'ASC',
            is_null($request->commentsCount) ? 1 : (int)$request->commentsCount,
            $request->commentsOrder ?? 'popular',
            $request->commentsType ?? 'root',
            is_null($request->hideFromUsers) ? 0 : (int)$request->hideFromUsers,
            is_null($request->hideFromGroups) ? 0 : (int)$request->hideFromGroups,
            is_null($request->hideFromPages) ? 0 : (int)$request->hideFromPages
        );
        return new GetNewsResponse($posts);
    }

    function getNews(User $requester, string $offsetId, int $count): array {
        
    }
}
