<?php
declare(strict_types=1);
namespace App\Application\Users\Auth\GetMe;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;

class GetMe implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    function __construct(UserRepository $users) {
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        return new GetMeResponse(
            $requester->id(),
            (string)$requester->username(),
            $requester->firstName(),
            $requester->lastName(),
            $requester->email(),
            $requester->currentPicture() ? $requester->currentPicture()->versions()['cropped_original'] : null,
            $requester->lastRequestsCheck()->getTimestamp() * 1000
        );
    }

}