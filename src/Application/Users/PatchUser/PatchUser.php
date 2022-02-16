<?php
declare(strict_types=1);
namespace App\Application\Users\PatchUser;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;

class PatchUser implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    public function __construct(
        UserRepository $users
    ) {
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $user = $this->findUserOrFail($request->userId, true, null);
        
        if(!$requester->equals($user)) {
            throw new \App\Application\Exceptions\ForbiddenException(228, 'No rights');
        }
        
        foreach($request->payload as $property => $value) {
            if($property === 'last_requests_check') {
                $user->checkRequests();
            } else if($property === 'first_name') {
                
            }
        }

        return new PatchUserResponse('Ok');
    }
}
