<?php
declare(strict_types=1);
namespace App\Application\Pages\Posts\PatchSectionSettings;

use App\Application\BaseRequest;
use App\Domain\Model\Pages\Post\PostRepository;
use App\Domain\Model\Users\User\UserRepository;

class PatchSectionSettings implements \App\Application\ApplicationService { 
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\Posts\PostAppServiceTrait;

    public function __construct(PostRepository $posts, UserRepository $users) {
        $this->posts = $posts;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, true);
        $sectionsSettings = $page->sectionsSettings();

        foreach ($request->payload as $key => $value) {
            if($key === 'photos') {
                $value
                    ? $sectionsSettings->enablePhotos($requester)
                    : $post->restore($requester);
            }
            elseif($key === 'deleted_by_global_moderation') {
                $value
                    ? $post->deleteByGlobalModerator($requester)
                    : $post->restoreByGlobalManager($requester);
            } else {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
            }
        }

        return new PatchResponse('OK');
    }

}
