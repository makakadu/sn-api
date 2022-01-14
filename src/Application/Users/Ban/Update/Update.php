<?php
declare(strict_types=1);
namespace App\Application\Users\Ban\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use Assert\Assert;
use App\Domain\Model\Users\Ban\Ban;
use App\Domain\Model\Users\Ban\BanRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Domain\Model\Users\User\UserRepository;

class Update  implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    // BanRepository
    // ConnectionRepository
    
    private BanRepository $bans;
    private ConnectionRepository $connections;
            
    function __construct(UserRepository $users, BanRepository $bans, ConnectionRepository $connections) {
        $this->users = $users;
        $this->bans = $bans;
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);

        $ban = $this->bans->getById($request->banId);
        
        if(!$ban) {
            throw new NotExistException("Ban {$ban->id()} not found");
        }
        
        if(!$requester->equals($ban->creator())) {
            throw new UnprocessableRequestException(123, "No rights");
        }
        foreach ($request->payload as $key => $value) {
            if($key === 'minutes') {
                $banMaxDuration = Ban::MAX_DURATION;
                Assert::that($value)->nullOr()->integer()->between(
                    1, $banMaxDuration, "Ban duration should be integer from 1 to $banMaxDuration or NULL"
                );
                $ban->changeDuration($value);
                if(\is_null($value)) {
                    $connection = $this->connections->getByUsersIds($requester->id(), $ban->banned()->id());
                    if($connection) {
                        $this->connections->remove($connection);
                    }
                }
            }
            elseif($key === 'message') {
                $banMessageMaxLength = Ban::MESSAGE_MAX_LENGTH;
                Assert::that($value)->nullOr()->string(
                    $value, "'message' should be a string up to $banMessageMaxLength characters or NULL"
                );
                $ban->changeMessage($value);
            }
        }
        return new UpdateResponse("OK");
    }
    
    public function execute_alt(BaseRequest $request): BaseResponse {        
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $user = $this->findUserOrFail($request->userId, false);
        
        // $this->authorization->failIfCannotBan($requester, $user);
        $requester->ban($user);
    }

}