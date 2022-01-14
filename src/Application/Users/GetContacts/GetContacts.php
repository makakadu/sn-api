<?php
declare(strict_types=1);
namespace App\Application\Users\GetContacts;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\ProfileTransformer;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\DataTransformer\Users\ConnectionTransformer;

class GetContacts implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ConnectionRepository $connections;
    
    public function __construct(UserRepository $users, ConnectionRepository $connections) {
        $this->users = $users;
        $this->connections = $connections;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $user = $this->findUserOrFail($request->userId, true, null);
        
        $count = \is_null($request->count) ? 20 : (int)$request->count;
        
        $contacts = [];
        $contactsCount = 0;
        if($request->commonWith) {
            $commonWith = $this->findUserOrFail($request->commonWith, false, null);
            $contacts = $this->users->getCommonContacts($user, $commonWith, $request->cursor, ($count + 1));
            $contactsCount = $this->users->getCommonContactsCount($user, $commonWith);
        } else {
            $contacts = $this->users->getUserContacts($user, $request->cursor, ($count + 1));
            $contactsCount = $this->users->getUserContactsCount($user);
        }
        
        $cursor = null;
        if((count($contacts) - $count) === 1) {
            $cursor = $contacts[count($contacts) -1]->id();
            array_pop($contacts);
        }
        
        $dtos = [];
        foreach($contacts as $contact) {
            $picture = $contact->currentPicture();
            $pictureDTO = $picture
                ? $picture->versions()['small']
                : null;
            
            $dtos[] = [
                'id' => $contact->id(),
                'firstname' => $contact->firstName(),
                'lastname' => $contact->lastName(),
                'username' => (string)$contact->username(),
                'picture' => $pictureDTO
            ];
        }
        return new GetContactsResponse($dtos, $contactsCount, $cursor);
    }
}
