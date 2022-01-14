<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Domain\Model\Users\SavesCollection\SavesCollection;

class SavesCollectionsAuth {
    use AuthorizationTrait;
    
    function __construct(\App\Domain\Model\Users\PrivacyService\PrivacyResolver_new $privacy) {
        $this->privacy = $privacy;
    }
    
    function failIfCannotSee(User $requester, SavesCollection $collection): void {
        $collectionCreator = $collection->creator();
        
        $this->failIfUserIsInactive($collectionCreator, "Access to collection is prohibited, collection owner is %");
        //$this->failIfUserIsDeleted($postOwner, "Post owner is deleted");
        
        if($collection->isSoftlyDeleted()) {
            throw new ForbiddenException(Errors::SOFTLY_DELETED, "Access to post is prohibited, post in trash");
        }
        $this->failIfUnaccessibleToUserByPrivacy($requester, $collection, "Access to post is prohibited by post privacy settings");
    }
    
    function failIfUnaccessibleToUserByPrivacy(User $requester, SavesCollection $collection, string $failMessage): void {
        if($collection->creator()->equals($requester)) {
            return;
        }
        if(!$this->privacy->hasAccess($requester, $collection->whoCanSee())) {
            throw new ForbiddenException(Errors::PROHIBITED_BY_PRIVACY, $failMessage);
        }
    }
    
}
