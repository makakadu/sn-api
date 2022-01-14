<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\NotExistException;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\ApplicationService;
use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Authorization\UserPhotosAuth;
use App\Domain\Model\Common\PhotoService;
use App\Domain\Model\Users\Photos\Reaction;
use App\Domain\Model\Users\Photos\ReactionRepository;
use App\Application\Exceptions\ValidationException;
use App\Domain\Model\Users\Albums\AlbumRepository;

abstract class PhotoAppService implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    protected PhotoRepository $photos;
    protected PhotoService $photoService;
    protected UserPhotosAuth $photosAuth;
    protected ReactionRepository $reactions;
    protected PhotoAlbumRepository $albums;
            
    function __construct(PhotoRepository $photos, UserRepository $users, PhotoService $photoService, UserPhotosAuth $photosAuth, ReactionRepository $reactions, PhotoAlbumRepository $albums) {
        $this->photos = $photos;
        $this->users = $users;
        $this->photoService = $photoService;
        $this->photosAuth = $photosAuth;
        $this->reactions = $reactions;
        $this->albums = $albums;
    }

    protected function findCommentOrFail(string $commentId, string $photoId): Comment {
        $photo = $this->posts->getById($photoId);
        if(!$photo) {
            throw new NotFoundException("Album photo not found");
        }
        
        $comment = $photo->getComment($commentId);
        if(!$comment) {
            throw new NotFoundException("Not found");
        }
        return $comment;
    }
    
    protected function findCommentReactionOrFail(string $reactionId, string $commentId, string $photoId): Reaction {
        $photo = $this->posts->getById($photoId);
        if(!$photo) {
            throw new NotFoundException("Post not found");
        }
        
        $comment = $photo->getComment($commentId);
        if(!$comment) {
            throw new NotFoundException("Comment not found");
        }
        
        $reaction = $comment->getReaction($reactionId);
        if(!$comment) {
            throw new NotFoundException("Not found");
        }
        return $reaction;
    }
    
    /** @param mixed $description */
    protected function validateParamDescription($description): void {
        $descriptionMaxLength = Photo::DESCRIPTION_MAX_LENGTH;
        
        \Assert\Assert::lazy()->that($description)
            ->string("Param 'description' should be a string")
            ->maxLength($descriptionMaxLength, "Length of 'description' should be no more than $descriptionMaxLength characters")
            ->verifyNow();
    }
    
    /** @param mixed $albumId */
    function validateParamAlbumId($albumId): void {
        try {
            \Assert\Assertion::string($albumId, "Param 'album_id' should be a string");
        } catch (\Assert\InvalidArgumentException $ex) {
            throw new ValidationException($ex->getMessage());
        }
    }
}