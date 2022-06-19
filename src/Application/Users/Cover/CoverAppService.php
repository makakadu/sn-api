<?php
declare(strict_types=1);
namespace App\Application\Users\Cover;

use App\Application\ApplicationService;
use App\Application\AppServiceTrait;
use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\User\UserRepository;
use Assert\Assert;
use App\Domain\Model\Users\Photos\Cover\Cover;
use App\Domain\Model\Users\Photos\Cover\CoverRepository;

abstract class CoverAppService implements ApplicationService {
    use AppServiceTrait;
    
    protected CoverRepository $covers;
    protected PhotoService $photoService;
    protected UserPhotosAuth $photosAuth;
            
    function __construct(
        UserRepository $users,
        CoverRepository $covers,
        PhotoService $photoService,
        UserPhotosAuth $photosAuth
    ) {
        $this->covers = $covers;
        $this->users = $users;
        $this->photoService = $photoService;
        $this->photosAuth = $photosAuth;
    }
    
    protected function findProfileCoverOrFail(string $coverId): Cover {
        $cover = $this->covers->getById($coverId);
        
        if(!$cover) {// || $picture->owner()->isSoftlyDeleted()) {
            throw new NotExistException("Cover $coverId not found");
        }
        return $cover;
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