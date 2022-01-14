<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Application\ApplicationService;
use App\Application\AppServiceTrait;
use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository;
use App\Domain\Model\Users\User\UserRepository;
use Assert\Assert;
use App\Domain\Model\Users\Photos\ProfilePicture\ProfilePicture;

abstract class ProfilePictureAppService implements ApplicationService {
    use AppServiceTrait;
    
    protected ProfilePictureRepository $profilePictures;
    protected PhotoService $photoService;
    protected UserPhotosAuth $photosAuth;
            
    function __construct(
        UserRepository $users,
        ProfilePictureRepository $profilePictures,
        PhotoService $photoService,
        UserPhotosAuth $photosAuth
    ) {
        $this->profilePictures = $profilePictures;
        $this->users = $users;
        $this->photoService = $photoService;
        $this->photosAuth = $photosAuth;
    }
    
    protected function findProfilePictureOrFail(string $pictureId): ProfilePicture {
        $picture = $this->profilePictures->getById($pictureId);
        
        if(!$picture) {// || $picture->owner()->isSoftlyDeleted()) {
            throw new NotExistException("Profile picture $pictureId not found");
        }
        return $picture;
    }
    
    /** @param mixed $value */
    protected function validateParamCropData($value): void {
        // Не знаю стоит ли считать это alformed запросом, думаю не стоит
        Assert::lazy()->that($value)
            ->isArray("Param 'crop_data' should be an array")
            ->keyExists('x', "Param 'crop_data' should to contain 'x' property")
            ->keyExists('y', "Param 'crop_data' should to contain 'y' property")
            ->keyExists('width', "Param 'crop_data' should to contain 'width' property")
            ->verifyNow();
    }
}