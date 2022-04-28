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
        $count = $request->count ? (int)$request->count : 20;
        
        $users = $this->users->search($requester, $request->text, $request->cursor, $count+1);

        $cursor = null;
        if((count($users) - $count) === 1) {
            $cursor = $users[count($users) -1]->id();
            array_pop($users);
        }
        $fields = is_null($request->fields) ? [] : explode(',', $request->fields);
        $dtos = $this->transformer->transformMultiple($requester, $users, $fields);        

        return new SearchResponse($dtos, 123, $cursor);
    }
}
