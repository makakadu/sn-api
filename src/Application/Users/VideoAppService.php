<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\ApplicationService;
use App\Domain\Model\Users\Videos\VideoRepository;
use App\Domain\Model\Users\Videos\Video;
use App\Application\CheckPhotoIsActiveVisitor;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Common\VideoService;
use App\Domain\Model\Users\PrivacyService\PrivacyService;

abstract class VideoAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;

    const LINK_MAX_LENGTH = 1000;
    
    protected VideoRepository $videos;
    protected PhotoService $photoService;
    protected VideoService $videoService;
    
    function __construct(
        VideoRepository $videos,
        UserRepository $users
    ) {
        $this->videos = $videos;
        $this->users = $users;
    }
    
    protected function findVideoOrFail(string $videoId, bool $asTarget): Video {
        $video = $this->videos->getById($videoId);
        
//        if(!$video || ($video && !$video->accept(new CheckPhotoIsActiveVisitor($this->comments)))) { // || ($photo && $photo->creator()->isDeleted())) {
//            if($asTarget) {
//                throw new NotExistException("Video $videoId not found");
//            } else {
//                throw new UnprocessableRequestException(['code' => 222, 'message' => "Video $videoId not found"]);
//            }
//        }
        return $video;
    }

    protected function validateDescription($value): void {
        $this->validator->string($value, "Description of photo should be a string");
        $this->validator->maxLength($value, 500, "Description of photo should have no more than 300 symbols");
    }
}