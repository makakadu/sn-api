<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Users\ProfilePictureAppService;

class Create extends ProfilePictureAppService {
    
    public function execute(BaseRequest $request): BaseResponse {    
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $this->validateRequest($request);
        
        $versions = [];
        $cropped = [];
        try {
            /* @var array<string> $standardVersions */
            $versions = $this->photoService->createPictureVersionsFromUploaded(
                $request->uploadedPhoto, (int)$request->x, (int)$request->y, (int)$request->width
            );
            $picture = $requester->createPicture($versions);
            $this->profilePictures->flush();
            return new CreateResponse($picture->id());
        } catch (\Exception $ex) {
            $this->photoService->deleteFiles(\array_merge($versions, $cropped));
            throw $ex;
        }
    }

    public function validateRequest(CreateRequest $request): void {
        //print_r($request);exit();
//        if (!ctype_digit($request->x)) {
//            echo "Contains non-numbers.";
//        }
        \Assert\Assert::lazy()
            ->that($request->x)->numeric("Param 'x' should be numeric")
            ->that($request->y)->numeric("Param 'y' should be numeric")
            ->that($request->width)->numeric("Param 'width' should be numeric")
            ->verifyNow();
    }

}
