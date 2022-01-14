<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Groups\Comments\GroupComment;
use App\DataTransformer\Groups\CommentAttachmentTransformer;
use App\Domain\Model\Groups\Comments\GroupCommentVisitor;

use App\Domain\Model\Groups\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Groups\Photos\GroupPicture\Comment\Comment as GroupPictureComment;
use App\Domain\Model\Groups\Post\Comment\Comment as PostComment;
use App\Domain\Model\Groups\Videos\Comment\Comment as VideoComment;
use App\Domain\Model\Users\User\User;

use App\DTO\Common\ReactedCommentDTO;
use App\DTO\Groups\ReactedGroupCommentDTO;
use App\DTO\Common\UnaccessibleReactedCommentDTO;
use App\DTO\Common\CommentedDTO;

use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

use App\Domain\Model\Authorization\GroupPostsAuth;
use App\Domain\Model\Authorization\GroupVideosAuth;
use App\Domain\Model\Authorization\GroupAlbumPhotosAuth;
use App\Domain\Model\Authorization\GroupPicturesAuth;



/**
 * @implements GroupCommentVisitor<ReactedCommentDTO>
 */
class ReactedGroupCommentTransformer extends Transformer implements GroupCommentVisitor {
    use \App\DataTransformer\TransformerTrait;

    private User $requester;
    
    private GroupPostsAuth $postsAuth;
    private GroupVideosAuth $videosAuth;
    private GroupAlbumPhotosAuth $albumPhotosAuth;
    private GroupPicturesAuth $picturesAuth;

    
    public function __construct(
        GroupPostsAuth $postsAuth, 
        GroupVideosAuth $videosAuth, 
        GroupAlbumPhotosAuth $albumPhotosAuth, 
        GroupPicturesAuth $picturesAuth
    ) {
        $this->postsAuth = $postsAuth;
        $this->videosAuth = $videosAuth;
        $this->albumPhotosAuth = $albumPhotosAuth;
        $this->picturesAuth = $picturesAuth;
    }
    
    /**
     * @return ReactedCommentDTO
     */
    function transform(GroupComment $comment, User $requester) {
        $this->requester = $requester;
        return $comment->acceptGroupCommentVisitor($this);
    }
    
    /**
     * @return ReactedGroupCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitAlbumPhotoComment(AlbumPhotoComment $comment) {
        $albumPhoto = $comment->commentedAlbumPhoto();
        $commentedDTO = new CommentedDTO('group_album_photo', $albumPhoto->id());
        //if(!$this->albumPhotosAuth->canSee($this->requester, $albumPhoto)) {
        if($albumPhoto->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedGroupCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitPostComment(PostComment $comment) {
        $post = $comment->commentedPost();
        $commentedDTO = new CommentedDTO('group_post', $post->id());
        //if(!$this->postsAuth->canSee($this->requester, $post)) {
        if($post->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedGroupCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitGroupPictureComment(GroupPictureComment $comment) {
        $picture = $comment->commentedPicture();
        $commentedDTO = new CommentedDTO('group_picture', $picture->id());
        //if(!$this->profilePicturesAuth->canSee($this->requester, $picture)) {
        if($picture->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedGroupCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitVideoComment(VideoComment $comment) {
        $video = $comment->commentedVideo();
        $commentedDTO = new CommentedDTO('group_video', $video->id());
        //if(!$this->videosAuth->canSee($this->requester, $video)) {
        if($video->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    function createDTO(GroupComment $comment, CommentedDTO $commentedDTO): ReactedGroupCommentDTO {
        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;
        
        $group = $comment->owningGroup();
        $asGroup = $comment->onBehalfOfGroup();
        $creator = $asGroup ? null : $comment->creator();
        
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $comment->reactions();
        
        return new ReactedGroupCommentDTO(
            $comment->id(),
            $comment->text(),
            $attachmentDTO,
            $creator ? $this->creatorToDTO($creator) : null,
            $this->groupToSmallDTO($group),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
            $commentedDTO
        );
    }
}