<?php
declare(strict_types=1);
namespace App\DataTransformer\Users;

use App\Domain\Model\Pages\Comments\PageComment;
use App\DataTransformer\Pages\CommentAttachmentTransformer;
use App\Domain\Model\Pages\Comments\PageCommentVisitor;


use App\Domain\Model\Pages\Photos\AlbumPhoto\Comment\Comment as AlbumPhotoComment;
use App\Domain\Model\Pages\Photos\PagePicture\Comment\Comment as PagePictureComment;
use App\Domain\Model\Pages\Post\Comment\Comment as PostComment;
use App\Domain\Model\Pages\Videos\Comment\Comment as VideoComment;
use App\Domain\Model\Users\User\User;

use App\DTO\Common\ReactedCommentDTO;
use App\DTO\Pages\ReactedPageCommentDTO;
use App\DTO\Common\UnaccessibleReactedCommentDTO;
use App\DTO\Common\CommentedDTO;

use App\Domain\Model\Common\Reaction;
use Doctrine\Common\Collections\Collection;

use App\Domain\Model\Authorization\PagePostsAuth;
use App\Domain\Model\Authorization\PageVideosAuth;
use App\Domain\Model\Authorization\PageAlbumPhotosAuth;
use App\Domain\Model\Authorization\PagePicturesAuth;

/**
 * @implements PageCommentVisitor<ReactedCommentDTO>
 */
class ReactedPageCommentTransformer extends Transformer implements PageCommentVisitor {
    use \App\DataTransformer\TransformerTrait;
    
    private User $requester;
    
    private PagePostsAuth $postsAuth;
    private PageVideosAuth $videosAuth;
    private PageAlbumPhotosAuth $albumPhotosAuth;
    private PagePicturesAuth $picturesAuth;
    
    public function __construct(
        PagePostsAuth $postsAuth, 
        PageVideosAuth $videosAuth, 
        PageAlbumPhotosAuth $albumPhotosAuth, 
        PagePicturesAuth $picturesAuth
    ) {
        $this->postsAuth = $postsAuth;
        $this->videosAuth = $videosAuth;
        $this->albumPhotosAuth = $albumPhotosAuth;
        $this->picturesAuth = $picturesAuth;
    }
    
    /**
     * @return ReactedCommentDTO
     */
    function transform(PageComment $comment, User $requester) {
        $this->requester = $requester;
        return $comment->acceptPageCommentVisitor($this);
    }
    
    /**
     * @return ReactedPageCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitAlbumPhotoComment(AlbumPhotoComment $comment) {
        $albumPhoto = $comment->commentedAlbumPhoto();
        $commentedDTO = new CommentedDTO('page_album_photo', $albumPhoto->id());
        //if(!$this->albumPhotosAuth->canSee($this->requester, $albumPhoto)) {
        if($albumPhoto->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedPageCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitPostComment(PostComment $comment) {
        $post = $comment->commentedPost();
        $commentedDTO = new CommentedDTO('page_post', $post->id());
        //if(!$this->postsAuth->canSee($this->requester, $post)) {
        if($post->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedPageCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitPagePictureComment(PagePictureComment $comment) {
        $picture = $comment->commentedPicture();
        $commentedDTO = new CommentedDTO('page_picture', $picture->id());
        //if(!$this->profilePicturesAuth->canSee($this->requester, $picture)) {
        if($picture->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    /**
     * @return ReactedPageCommentDTO | UnaccessibleReactedCommentDTO
     */
    public function visitVideoComment(VideoComment $comment) {
        $video = $comment->commentedVideo();
        $commentedDTO = new CommentedDTO('page_video', $video->id());
        //if(!$this->videosAuth->canSee($this->requester, $video)) {
        if($video->id() > 1) {
            return new UnaccessibleReactedCommentDTO($comment->id(), $this->creationTimeToTimestamp($comment->createdAt()), $commentedDTO);
        }
        return $this->createDTO($comment, $commentedDTO);
    }

    function createDTO(PageComment $comment, CommentedDTO $commentedDTO): ReactedPageCommentDTO {
        $attachment = $comment->attachment();
        $attachmentDTO = $attachment
            ? $attachment->acceptAttachmentVisitor(new CommentAttachmentTransformer()) : null;
        
        $page = $comment->owningPage();
        $asPage = $comment->onBehalfOfPage();
        $creator = $asPage ? null : $comment->creator();
        
        /** @var Collection<string, Reaction> $reactions */
        $reactions = $comment->reactions();
        
        return new ReactedPageCommentDTO(
            $comment->id(),
            $comment->text(),
            $attachmentDTO,
            $creator ? $this->creatorToDTO($creator) : null,
            $this->pageToSmallDTO($page),
            $this->prepareReactionsCount($reactions),
            $this->creationTimeToTimestamp($comment->createdAt()),
            $commentedDTO
        );
    }
}