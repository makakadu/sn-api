<?php
declare(strict_types=1);
namespace App\Application\Users\Cover\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ProfilePictureAppService;

class Update extends ProfilePictureAppService {

    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        $this->validateParamCropData($request->cropData);
        
        $picture = $this->findProfilePictureOrFail($request->pictureId);
        $this->photosAuth->failIfCannotUpdatePicture($requester, $picture);                 // ProfilePicture не может быть мягко удален. ProfilePicture считается мягко удаленным, если удалено связанное фото.

        $croppedVersions = $this->photoService->createCroppedVersions( 
            $picture->photo(), $request->x, $request->y, $request->width
        );
        $picture->edit($croppedVersions);
        try {
            $this->profilePictures->flush();
        } catch (\Exception $ex) {
            $this->photoService->deleteFiles($croppedVersions);
            throw $ex;
        }
        return new UpdateResponse('OK');
    }
}