<?php
declare(strict_types=1);
namespace App\Application\Users\CommentPhotos\Get;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Users\Comments\Photo\PhotoRepository;
use App\Application\Exceptions\NotExistException;
use App\DataTransformer\Users\CommentPhotoTransformer;

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
        
        $dto = (new CommentPhotoTransformer())->transform($photo);
        
        return new GetResponse($dto);
    }
    
    function validate(GetRequest $request): void {
//        PostParamsValidator::validateCommentsTypeParam($request->commentsType);
//        PostParamsValidator::validateCommentsCountParam($request->commentsCount);
//        PostParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc', 'top']);
    }
}
