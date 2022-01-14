<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\Common\AttachmentDTO;
use App\DTO\CreatorDTO;
use App\DTO\Shares\SharedDTO;
use App\DTO\Common\PostDTO;

class UserPostDTO implements PostDTO {
    public string $id;
    public string $text;
    
    public bool $commentingIsDisabled;
    public bool $reactionsAreDisabled;
    
    public bool $isPublic;
    
    public ?CreatorDTO $creator;
//    /** @var array<mixed> $photos */
//    public array $photos = [];
//    /** @var array<mixed> $videos */
//    public array $videos = [];
    
    public int $timestamp;
    
    /** @var array<int, ReactionDTO> $reactions */
    public array $reactions;
    
    public ?ReactionDTO $requesterReaction;
    
    public int $allReactionsCount;
    /** @var array<int> $reactionsCount */
    public array $reactionsCount;
    
    public ?SharedDTO $shared;
    
//    public bool $isDeleted;
//    public bool $isDeletedByManager;
    
    /** @var array<int, CommentDTO> $comments */
    public array $comments = [];
    
    public int $comments_count;
    
    /** @var array<int, AttachmentDTO> $attachments */
    public array $attachments = [];
    
    /**
     * @param array<int, ReactionDTO> $reactions
     * @param array<string, int> $reactionsCount
     * @param array<int, CommentDTO> $comments
     * @param array<int, AttachmentDTO> $attachments
     */
    function __construct(
        string $id,
        string $text,
        bool $commentsAreDisabled,
        bool $reactionsAreDisabled,
        bool $isPublic, 
        ?CreatorDTO $creator,
        int $timestamp, 
        array $reactions,
        ?ReactionDTO $requesterReaction,
        array $reactionsCount, 
        ?SharedDTO $shared,
        array $comments,
        int $commentsCount,
        array $attachments,
        bool $addType = false
    ) {
        if($addType) {
            $this->type = 'user_post';
        }
        $this->id = $id;
        $this->text = $text;
        $this->commentingIsDisabled = $commentsAreDisabled;
        $this->reactionsAreDisabled = $reactionsAreDisabled;
        $this->isPublic = $isPublic;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
        $this->reactions = $reactions;
        $this->requesterReaction = $requesterReaction;
        $this->reactionsCount = $reactionsCount;
        $this->shared = $shared;
        $this->comments = $comments;
        $this->commentsCount = $commentsCount;
        $this->attachments = $attachments;
    }


}
