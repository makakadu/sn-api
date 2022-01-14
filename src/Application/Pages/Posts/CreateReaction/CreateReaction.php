<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\CreateReaction;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;

class CreateReaction implements \App\Application\ApplicationService {
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    public function __construct(UserRepository $users, PostRepository $posts, PageRepository $pages) {
        $this->users = $users;
        $this->posts = $posts;
        $this->pages = $pages;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $post = $this->findPostOrFail($request->postId, true);
        $post->react($requester, $request->type, (bool)$request->onBehalfOfPage);

        return new CreateReactionResponse('ok');
    }
    
    function validate(GetRequest $request): void {
        ReactionValidator::validateTypeParam($request->type);
        ReactionValidator::validateAsPageIdParam($request->asPageId);
    }
}
