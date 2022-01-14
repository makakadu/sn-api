<?php
declare(strict_types=1);
namespace App\Application\Users\Cover\Create;

use App\Application\BaseRequest;
use App\Application\BaseResponse;

class Create extends \App\Application\Users\Cover\CoverAppService {
    
    public function execute(BaseRequest $request): BaseResponse {    
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $this->validateRequest($request);
        
        $versions = [];
        $cropped = [];
        try {
            /* @var array<string> $standardVersions */
            $versions = $this->photoService->createCoverVersionsFromUploaded(
                $request->uploadedPhoto, (int)$request->x, (int)$request->y, (float)$request->width
            );
            $cover = $requester->createCover($versions);
            $this->covers->flush();
            return new CreateResponse($cover->id());
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
