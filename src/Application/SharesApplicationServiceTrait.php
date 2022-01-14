<?php
declare(strict_types=1);
namespace App\Application;

use App\Domain\Model\Common\Shareable;
use App\Domain\Model\Users\Post\PostRepository as ProfilePostRepository;
use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Domain\Model\Users\Video\VideoRepository as ProfileVideoRepository;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Common\SharesService;
use App\Domain\Model\Authorization\SharesAuthorization;

trait SharesApplicationServiceTrait {
    
    protected SharesService $sharesService;
    protected SharesAuthorization $sharesAuthorization;
    protected ProfilePostRepository $profilePosts;
    protected PhotoRepository $profilePhotos;
    protected ProfileVideoRepository $profileVideos;
    protected GroupPostRepository $groupPosts;
    protected GroupPhotoRepository $groupPhotos;
    protected GroupVideoRepository $groupVideos;
    protected PagePostRepository $pagePosts;
    protected PagePhotoRepository $pagePhotos;
    protected PageVideoRepository $pageVideos;

    function findShareable(string $path): ?Shareable {
        $type = explode('/', $path)[1];
        $id = explode('/', $path)[2];
                
        $shareable = null;
        
        switch ($type) {
            case 'profile-photos':
                $shareable = $this->profilePhotos->getById($id);
                if($shareable) {
                    $this->failIfOwnerIsDeleted($shareable->creator(), "Shared photo not found");
                }
            case 'profile-posts':
                $shareable = $this->profilePosts->getById($id);
                if($shareable) {
                    $this->failIfOwnerIsDeleted($shareable->creator(), "Shared post not found");
                }
            case 'profile-videos':
                $shareable = $this->profileVideos->getById($id);
                if($shareable) {
                    $this->failIfOwnerIsDeleted($shareable->creator(), "Shared video not found");
                }
            case 'group-photos':
            case 'group-posts':
            case 'group-videos':
            case 'page-photos':
            case 'page-posts':
            case 'page-videos':
            default: 
        }

        return $shareable;
    }
    
    function failIfOwnerIsDeleted(User $user, string $message): void {
        if($user->isDeleted()) {
            throw new Exceptions\NotExistException($message);
        }
    }
}