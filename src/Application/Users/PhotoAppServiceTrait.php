<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\RequestParamsValidator;
use App\Application\ApplicationService;
use App\Domain\Model\Users\Albums\CustomAlbum\CustomAlbum;
use App\Domain\Model\Authorization\ProfilePhotosAuthorization;
use App\Domain\Model\Users\Photos\PhotoService;
use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Domain\Model\Users\Photos\Photo;

trait PhotoAppServiceTrait {
    private PhotoRepository $photos;
            
    function __construct(
        PhotoRepository $photos
    ) {
        $this->photos = $photos;
    }
    
    public function findPhotoOrFail(string $photoId, bool $asTarget): Photo {
        $photo = $this->photos->getById($photoId);
        
        
        if(!$photo || ($photo && $photo->isSoftlyDeleted()) || ($photo && $photo->creator()->isSoftlyDeleted())) {
            if($asTarget) {
                throw new NotExistException('Photo not found');
            } else {
                throw new UnprocessableRequestException(['code' => 222, 'message' =>"Photo $photoId not found"]);
            }
        }
        
        return $photo;
    }

    private function validateDescription($value): void {
        $this->validator->string($value, "Description of photo should be a string");
        $this->validator->maxLength($value, 500, "Description of photo should have no more than 300 symbols");
    }
}