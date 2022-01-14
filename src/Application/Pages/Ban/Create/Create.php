<?php
declare(strict_types=1);
namespace App\Application\Pages\Ban\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Authorization\PagesAuth;
use App\Domain\Model\Pages\Ban\BanRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    //private PagesAuth $pagesAuth;
    private BanRepository $bans;
    
    public function __construct(UserRepository $users, PageRepository $pages, BanRepository $bans) {//, PagesAuth $pagesAuth) {
        $this->users = $users;
        $this->pages = $pages;
        $this->bans = $bans;
        //$this->pagesAuth = $pagesAuth;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $page = $this->findPageOrFail($request->pageId, false, null);
        $target = $this->findUserOrFail($request->userId, false, null);
        
        $existingBan = $this->bans->getByPageAndUser($page, $target);
        if($existingBan) {
            $banEnd = $existingBan->getEnd();
            if($banEnd && $banEnd < (new \DateTime('now')) && $existingBan->deletedAt() === "") {
                $this->bans->remove($existingBan);
                $this->bans->flush();
            }

        }
        $ban = $page->banUser($requester, $target, $request->minutes, $request->reason, $request->message);
        return new CreateResponse($ban);
    }

}
