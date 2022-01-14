<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Pages\PageSmallDTO;
use App\DTO\Common\AttachmentDTO;

class CommentDTO {
    
    public string $id;
    public string $text;
    public ?AttachmentDTO $attachment;
    public ?string $video;
    public ?string $rootId;
    public ?string $repliedId;
    public ?CreatorDTO $creator;
    //public ?PageSmallDTO $page;
    public int $repliesCount;
    /** @var array<string, int> $reactionsCount */
    public array $reactionsCount;
    public int $timestamp;
//    public bool $isDeleted;
//    public bool $isDeletedByManager;
    
    /**
     * @param array<string, int> $reactionsCount
     */
    function __construct(
        string $id, 
        string $text,
        ?string $rootId, 
        ?string $repliedId,
        ?AttachmentDTO $attachment,
        CreatorDTO $creator,
        //?PageSmallDTO $page,
        int $repliesCount, 
        array $reactionsCount, 
        int $timestamp
//        bool $isDeleted, 
//        bool $isDeletedByManager
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->rootId = $rootId;
        $this->repliedId = $repliedId;
        $this->creator = $creator;
        $this->attachment = $attachment;
        //$this->page = $page;
        $this->repliesCount = $repliesCount;
        $this->reactionsCount = $reactionsCount;
        $this->timestamp = $timestamp;
//        $this->isDeleted = $isDeleted;
//        $this->isDeletedByManager = $isDeletedByManager;
    }

}
