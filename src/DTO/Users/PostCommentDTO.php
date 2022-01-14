<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Pages\PageSmallDTO;
use App\DTO\Common\AttachmentDTO;
use App\DTO\Users\ReactionDTO;

class PostCommentDTO {
    
    public string $id;
    public string $text;
    public ?AttachmentDTO $attachment;
    public ?string $video;
    public ?string $rootId;
    public ?self $replied;
    public ?CreatorDTO $creator;
    public ?PageSmallDTO $onBehalfOfPage;
    
    /** @var array<int, PostCommentDTO> $replies */
    public array $replies;
    public int $repliesCount;
    
    public ?ReactionDTO $requesterReaction;
    
    /** @var array<int, ReactionDTO> $reactions */
    public array $reactions;
    /** @var array<string, int> $reactionsCount */
    public array $reactionsCount;
    public int $timestamp;
//    public bool $isDeleted;
//    public bool $isDeletedByManager;
    
    /**
     * @var array<int, PostCommentDTO> $replies
     * @param array<int, ReactionDTO> $reactions
     * @param array<string, int> $reactionsCount
     */
    function __construct(
        string $id, 
        string $text,
        ?string $root_id,
        ?self $replied,
        ?AttachmentDTO $attachment,
        ?CreatorDTO $creator,
        ?PageSmallDTO $on_behalf_of_page,
        array $replies,
        int $replies_count,
        array $reactions,
        array $reactionsCount, 
        int $timestamp,
        ?ReactionDTO $requesterReaction
//        bool $isDeleted, 
//        bool $isDeletedByManager
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->rootId = $root_id;
        $this->replied = $replied;
        $this->creator = $creator;
        $this->attachment = $attachment;
        $this->onBehalfOfPage = $on_behalf_of_page;
        $this->reactions = $reactions;
        $this->replies = $replies;
        $this->repliesCount = $replies_count;
        $this->reactionsCount = $reactionsCount;
        $this->timestamp = $timestamp;
        $this->requesterReaction = $requesterReaction;
//        $this->isDeleted = $isDeleted;
//        $this->isDeletedByManager = $isDeletedByManager;
    }

}
