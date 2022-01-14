<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\CreateReaction;

use App\Application\Users\Posts\PostAppService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Pages\Page\PageRepository;

class CreateReaction extends PostAppService {

    protected PostRepository $posts;
    protected UserPostsAuth $postsAuth;
    protected PageRepository $pages;
    
    function __construct(
        PostRepository $posts,
        UserRepository $users,
        UserPostsAuth $postsAuth,
        PageRepository $pages
    ) {
        parent::__construct($posts, $users, $postsAuth);
        $this->pages = $pages;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $post = $this->findPostOrFail($request->postId, true);
        $asPage = null;
        if($request->asPageId) {
            $asPage = $this->pages->getById($request->asPageId);
            if(!$asPage) {
                throw new UnprocessableRequestException(Errors::NOT_FOUND, "Page not found");
            }
        }
        $this->postsAuth->failIfCannotReact($requester, $post, $asPage);

        $reaction = $post->react($requester, $request->type, $asPage);
        try {
            $this->posts->flush();
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $ex) {
            throw new \App\Domain\Model\DomainException('User already reacted');
        }
        

        return new CreateReactionResponse($reaction->id());
    }
}
