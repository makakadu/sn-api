<?php
declare(strict_types=1);
namespace App\Application\Users\Saves\GetAllUserSaves;

use App\Application\BaseRequest;
use App\DataTransformer\Users\SavesTransformer;

class GetAllUserSaves {
    
    private \App\Domain\Model\Authorization\SaveableAuth $auth;
    
    public function execute(BaseRequest $request): GetItemsResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $owner = $this->findUserOrFail($request->ownerId, false, null);
        
        if(!$owner->equals($requester)) { // Это вся авторизация
            throw new ForbiddenException(\App\Application\Errors::NO_RIGHTS, "Forbidden");
        }
        
        $saves = $this->savesCollections->getByOwnerId(
            $owner->id(),
            $request->offsetId,
            $request->count ? (int)$request->count : 50
        );
        
        $transformer = new SavesTransformer(
            $requester,
            $this->auth,
            $request->commentsCount,
            $request->commentsType,
            $request->commentsOrder
        );
        return new GetItemsResponse($transformer->transform($saves));
    }
}
