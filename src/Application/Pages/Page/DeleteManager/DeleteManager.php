<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\DeleteManager;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;

class DeleteManager implements \App\Application\ApplicationService {
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
        $page->deleteManager($requester, $request->managerId);

        return new DeleteManager("OK");
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

