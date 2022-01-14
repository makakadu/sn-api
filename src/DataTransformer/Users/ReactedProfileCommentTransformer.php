<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\DTO\Users\CommentDTO;
use App\Domain\Model\Users\Comments\ProfileComment;
use App\DataTransformer\Users\CommentAttachmentTransformer;
use App\Domain\Model\Users\Comments\ProfileCommentVisitor;

use App\Domain\Model\Users\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Users\Photos\ProfilePicture\Comment\Comment as ProfilePictureComment;
use App\Domain\Model\Users\Post\Comment\Comment as PostComment;
use App\Domain\Model\Users\Videos\Comment\Comment as VideoComment;
use App\Domain\Model\Users\User\User;

use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

use App\DTO\Common\ReactedCommentDTO;
use App\DTO\Users\ReactedProfileCommentDTO;
use App\DTO\Common\UnaccessibleReactedCommentDTO;
use App\DTO\Common\CommentedDTO;

/**
 * @implements ProfileCommentVisitor<ReactedCommentDTO>
 */
class ReactedProfileCommentTransformer extends Transformer implements ProfileCommentVisitor {
    use \App\DataTransformer\TransformerTrait;

    private User $requester;
    
    function transform(ProfileComment $comment, User $requester): ReactedCommentDTO {
        $this->requester = $requester;
        return $comment->acceptProfileCommentVisitor($this);
    }
    
    /**
     * @return ReactedProfileCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitAlbumPhotoComment(AlbumPhotoComment $comment) {
        $albumPhoto = $comment->commentedAlbumPhoto();
        $commentedDTO = new CommentedDTO('user_album_photo', $albumPhoto->id());
        //if(!$this->albumPhotosAuth->canSee($this->requester, $albumPhoto)) {
        if($albumPhoto->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedProfileCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitPostComment(PostComment $comment) {
        $post = $comment->commentedPost();
        $commentedDTO = new CommentedDTO('user_post', $post->id());
        //if(!$this->postsAuth->canSee($this->requester, $post)) {
        if($post->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedProfileCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitProfilePictureComment(ProfilePictureComment $comment) {
        $picture = $comment->commentedPicture();
        $commentedDTO = new CommentedDTO('user_picture', $picture->id());
        //if(!$this->profilePicturesAuth->canSee($this->requester, $picture)) {
        if($picture->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedProfileCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitVideoComment(VideoComment $comment) {
        $video = $comment->commentedVideo();
        $commentedDTO = new CommentedDTO('user_video', $video->id());
        //if(!$this->videosAuth->canSee($this->requester, $video)) {
        if($video->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    function createDTO(ProfileComment $comment, CommentedDTO $commentedDTO): ReactedProfileCommentDTO {
        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;
        
        $owner = $comment->owner();
        $onBehalfOfPage = $comment->onBehalfOfPage();
        
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $comment->reactions();
        
        return new ReactedProfileCommentDTO(
            $comment->id(),
            $comment->text(),
            $attachmentDTO,
            $this->profileToSmallDTO($owner),
            $onBehalfOfPage ? null : $this->creatorToDTO($comment->creator()),
            $onBehalfOfPage ? $this->pageToSmallDTO($onBehalfOfPage) : null,
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
            $commentedDTO
        );
    }
}