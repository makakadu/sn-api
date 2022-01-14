<?php
declare(strict_types=1);
namespace App\Application\Groups\Settings\Patch;

use App\Application\BaseRequest;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Groups\Group\GroupRepository;

class Patch implements \App\Application\ApplicationService { 
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\Posts\PostAppServiceTrait;
    use \App\Application\Groups\GroupAppServiceTrait;
    
    public function __construct(UserRepository $users, GroupRepository $groups) {
        $this->users = $users;
        $this->groups = $groups;
    }
    
    public function execute(BaseRequest $request): PatchResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $group = $this->findGroupOrFail($request->groupId, true);
        $groupSettings = $group->settings();

        foreach ($request->payload as $key => $value) {
            if($key === 'auto_approvals') {
                $value
                    ? $groupSettings->enableAutoApproving($requester)
                    : $groupSettings->disableAutoApproving($requester);
            }
            elseif($key === 'comments_sorting') {
                $groupSettings->changeCommentsSorting($requester, $value);
            }
            elseif($key === 'visible') {
                $value ? $groupSettings->unhide($requester) : $groupSettings->hide($requester);
            }
            elseif($key === 'private') {
                $value ? $groupSettings->makePrivate($requester) : $groupSettings->makePublic($requester);
            }
            elseif($key === 'wall_section') {
                $groupSettings->editWallSection($requester, $value);
            }
            elseif($key === 'photos_section') {
                $groupSettings->editPhotosSection($requester, $value);
            }
            elseif($key === 'videos_section') {
                $groupSettings->editVideosSection($requester, $value);
            }
            else {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
            }
        }

        return new PatchResponse('OK');
    }

}
