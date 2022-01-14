<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\PatchManager;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;

class PatchManager implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    function __construct(
        UserRepository $users,
        PageRepository $pages
    ) {
        $this->pages = $pages;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, true, null);
        
        $page->editManager(
            $requester,
            $request->managerId, 
            $request->position, 
            (bool)$request->showInContacts, 
            (bool)$request->allowExternalActivity
        );
        return new PatchManager("OK");
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

