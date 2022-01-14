<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost\GetPart;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\PostRepository;
use App\DataTransformer\Pages\PostTransformer;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Application\Errors;
use App\Application\Exceptions\ForbiddenException;

class GetPart implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    private PostRepository $posts;
    private SuggestedPostTransformer $suggestedPostsTransformer;
    
    public function __construct(UserRepository $users, PageRepository $pages, PostRepository $posts, SuggestedPostTransformer $suggestedPostsTransformer) {
        $this->users = $users;
        $this->pages = $pages;
        $this->posts = $posts;
        $this->suggestedPostsTransformer = $suggestedPostsTransformer;
    }
    
    public function execute(BaseRequest $request): GetPartResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $page = $this->findPageOrFail($request->pageId, true, null);
        if($page->isAdminOrEditor($requester)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Access to suggested posts is forbidden");
        }
        $posts = $this->posts->getPartOfActiveByPage(
            $page,
            $request->offsetId,
            $request->count ? (int)$request->count : 20
        );
        return new GetPartResponse(
            $this->suggestedPostTransformer->transformMultiple(
                $requester,
                $posts,
                !\is_null($request->commentsCount) ? (int)$request->commentsCount : 1,
                $request->commentsType ? $request->commentsType : "root",
                $request->commentsOrder ? $request->commentsOrder : "ASC"
            )
       );
    }
}
