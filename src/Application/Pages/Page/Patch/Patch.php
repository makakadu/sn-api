<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\Patch;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Application\Errors;

class Patch implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    use \App\Application\Pages\PageAppService;
    
    function __construct(UserRepository $users, PageRepository $pages) {
        $this->pages = $pages;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $page = $this->findPageOrFail($request->pageId, true, null);
        
        if(!$page->isAdmin($requester)) {
            throw new \App\Application\Exceptions\ForbiddenException(Errors::NO_RIGHTS, "No rights to change page");
        }
        foreach ($request->payload as $param => $value) {
            if($param === 'name') {
                $page->changeName($requester, $value);
            }
            elseif($param === 'description') {
                $page->changeDescription($requester, $value);
            }
            elseif($param === 'subject') {
                $page->changeSubject($requester, $value);
            }
            else {
                throw new \App\Application\Exceptions\UnprocessableRequestException(123, "Incorrect param name");
            }
        }
        return new PatchResponse("OK");
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

