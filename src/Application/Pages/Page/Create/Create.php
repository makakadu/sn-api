<?php
declare(strict_types=1);
namespace App\Application\Pages\Page\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Page\PageRepository;

class Create implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
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

        $page = $requester->createPage(
            $request->name,
            $request->description ? $request->description : "",
            $request->subject
        );
        $this->pages->add($page);

        return new CreateResponse($page->id());
    }
    
//    private function validateRequest(CreateRequest $request): void {
//        
//    }
}        

