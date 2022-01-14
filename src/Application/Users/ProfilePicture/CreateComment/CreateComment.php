<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\CreateComment;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\Photos\PhotoAppService;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Common\PhotoService;
use App\Application\Exceptions\ValidationException;
use App\Application\Exceptions\MalformedRequestException;
use App\Domain\Model\Users\ProfilePicture\ProfilePictureRepository;

class CreateComment extends PhotoAppService {
    
    function __construct(
        ProfilePictureRepository $photos,
        UserRepository $users,
        PhotoService $photoService
    ) {
        parent::__construct($photos, $users, $photoService);
    }

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->validateRequest($request);
        
        $picture = $this->findPictureOrFail($request->pictureId, false);
        $this->auth->failIfUserCannotAccessProfile($requester, $picture->owner());
        
        $attachedPhoto = null;
        $attachedVideo = null;
        
        if($request->attachment) {
            if(!isset($request->attachment['type'])) {
                throw new ValidationException("Param 'attachment' should contain property 'type'");
            }
            elseif(!isset($request->attachment['id'])) {
                throw new ValidationException("Param 'attachment' should contain property 'id'");
            }
            $type = $request->attachment['type'];
            $id = $request->attachment['id'];
            
            if($type === 'photo') {
                $attachedPhoto = $this->attachmentsService->preparePhotoForUserComment($requester, $id);
            }
            elseif($type === 'video') { 
                $attachedVideo = $this->attachmentsService->prepareVideoForUserComment($requester, $id);
            }
            else {
                throw new MalformedRequestException("Incorrect attachment type");
            }
        }
        $picture->comment($requester, $request->text, $request->replied, $attachedPhoto, $attachedVideo);

        return new CreateCommentResponse('ok');
    }

    public function validateRequest(\App\Application\Users\Photos\CreateComment\CreateCommentRequest $request): void {
        if($request->attachment) {
            \Assert\Assert::lazy()
                ->that($request->attachment)->isArray("Param 'attachment should be an object")
                ->that($request->attachment)->keyExists('type', "Param 'attachment' should have property 'type'")
                ->that($request->attachment)->keyExists('id', "Param 'attachment' should have property 'id'")
                ->verifyNow();
        }
    }

}