<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\CreateManager;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;

class CreateManager implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    function __construct(
        UserRepository $users,
        PageRepository $pages
    ) {
        $this->pages = $pages;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        //$this->validateRequest($request);
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, true, null);
        $user = $this->findUserOrFail($request->userId, false, null);
        
        $page->addManager(
            $requester, 
            $user,
            $request->position, 
            (bool)$request->showInContacts, 
            (bool)$request->allowExternalActivity
        );
        return new CreateManagerResponse("OK");
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

