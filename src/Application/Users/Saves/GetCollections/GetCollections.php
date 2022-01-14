<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetCollections;

use App\Application\BaseRequest;
use App\Domain\Model\Users\SavesCollection\SavesCollectionRepository;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;

class GetCollections implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private SavesCollectionRepository $savesCollections;
    
    function __construct(
        UserRepository $users,
        SavesCollectionRepository $savesCollections
    ) {
        $this->users = $users;
        $this->savesCollections = $savesCollections;
    }
    
    public function execute(BaseRequest $request): GetCollectionsResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $owner = $this->findUserOrFail($request->ownerId, true, null);
        
        $collections = $this->savesCollections->getPartOfActiveAndAccessibleToRequesterByOwner(
            $requester,
            $owner,
            $request->count ? (int)$request->count : 0,
            $request->order  ? (string)$request->order : "ASC",
            $request->offsetId ? (string)$request->offsetId : null
        );

        return new GetCollectionsResponse($collections);
    }
}
