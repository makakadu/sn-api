<?php
declare(strict_types=1);
namespace App\Application\Users\Search;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\ProfileTransformer;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\DataTransformer\Users\ConnectionTransformer;

class Search implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ProfileTransformer $transformer;
    
    public function __construct(UserRepository $users, ProfileTransformer $transformer) {
        $this->users = $users;
        $this->transformer = $transformer;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
//        echo $request->text;exit();
        $count = $request->count ? (int)$request->count : 20;
        $users = $this->users->search($requester, $request->text, $request->cursor, $count+1);
        
        $cursor = null;
        if((count($users) - $count) === 1) {
            $cursor = $users[count($users) -1]->id();
            array_pop($users);
        }
        
        $allCount = 222;
        
        $dtos = [];
        if($request->full) {
            foreach($users as $user) {
                $dtos[] = $this->transformer->transform($requester, $user);
            }
        }
        else {
            foreach($users as $user) {
                $picture = $user->currentPicture();
                $pictureDTO = $picture
                    ? $picture->versions()['cropped_small']
                    : null;

                $dtos[] = [
                    'id' => $user->id(),
                    'firstname' => $user->firstName(),
                    'lastname' => $user->lastName(),
                    'username' => (string)$user->username(),
                    'picture' => $pictureDTO
                ];
            }
        }
        

        return new SearchResponse($dtos, $allCount, $cursor);
    }
}
