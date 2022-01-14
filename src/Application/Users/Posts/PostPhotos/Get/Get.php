<?php
declare(strict_types=1);
namespace App\Application\Users\Posts\PostPhotos\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Post\Photo\PhotoRepository;
use App\Application\Exceptions\NotExistException;
use App\DataTransformer\Users\PostPhotoTransformer;

class Get implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private PhotoRepository $photos;
    
    function __construct(PhotoRepository $photos, UserRepository $users) {
        $this->photos = $photos;
        $this->users = $users;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $photo = $this->photos->getById($request->photoId);
        if(!$photo) {
            throw new NotExistException('Photo not found');
        }
//        
//        if($requester) {
//            $this->postsAuth->failIfCannotSee($requester, $photo);
//        } else {
//            $this->postsAuth->failIfGuestsCannotSee($photo);
//        }
        
        $dto = (new PostPhotoTransformer())->transform($photo);
        
        return new GetResponse($dto);
    }
    
    function validate(GetRequest $request): void {
//        PostParamsValidator::validateCommentsTypeParam($request->commentsType);
//        PostParamsValidator::validateCommentsCountParam($request->commentsCount);
//        PostParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
    }

}
