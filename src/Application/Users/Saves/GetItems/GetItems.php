<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetItems;

use App\Application\BaseRequest;
use App\DataTransformer\Users\SavesTransformer;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\SavesCollection\SavesCollectionRepository;
use App\Domain\Model\Users\SavesCollection\SavedItemRepository;
use App\Application\GetRequestParamsValidator;
use App\Domain\Model\Authorization\SavesCollectionsAuth;

class GetItems implements ApplicationService {
    use \App\Application\AppServiceTrait;

    private SavesCollectionRepository $savesCollections;
    private SavedItemRepository $savesCollectionItems;
    private SavesTransformer $savesTransformer;
    private SavesCollectionsAuth $auth;
    
    function __construct(
        UserRepository $users,
        SavesCollectionRepository $savesCollections,
        SavedItemRepository $savesCollectionItems,
        SavesTransformer $savesTransformer,
        SavesCollectionsAuth $auth
    ) {
        $this->users = $users;
        $this->savesCollections = $savesCollections;
        $this->savesCollectionItems = $savesCollectionItems;
        $this->savesTransformer = $savesTransformer;
        $this->auth = $auth;
    }
    
    public function execute(BaseRequest $request): GetItemsResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        $this->validate($request);
        
        $collection = $this->savesCollections->getById($request->collectionId);
        if(!$collection) {
            throw new \App\Application\Exceptions\NotExistException("Collection not found");
        }
        $this->auth->failIfCannotSee($requester, $collection);
        
        $items = $this->savesCollectionItems->getPartByCollection(
            $requester,
            $collection->id(),
            $request->offsetId,
            $request->count ? (int)$request->count : 50,
            $request->order ? (string)$request->order : "asc"
        );
        
        return new GetItemsResponse($this->savesTransformer->transform(
            $items, $requester,
            $request->commentsCount ? (int)$request->commentsCount : 1,
            $request->commentsType ? $request->commentsType : "all",
            $request->commentsOrder ? $request->commentsOrder : "asc"
        ));
    }
    
    function validate(GetItemsRequest $request): void {
        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
        GetRequestParamsValidator::validateOffsetIdParam($request->offsetId);
        GetRequestParamsValidator::validateCountParam($request->count);
        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc']);
        GetRequestParamsValidator::validateOrderParam($request->order, ['asc', 'desc']);        
    }
}
