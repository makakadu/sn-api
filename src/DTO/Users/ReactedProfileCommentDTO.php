<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Pages\PageSmallDTO;
use App\DTO\Common\AttachmentDTO;
use App\DTO\Common\CommentedDTO;
use App\DTO\Users\ProfileSmallDTO;
use App\DTO\Common\ReactedCommentDTO;

class ReactedProfileCommentDTO implements ReactedCommentDTO {
    
    public string $id;
    public string $text;
    public ?AttachmentDTO $attachment;
    public ProfileSmallDTO $owner;
    public ?CreatorDTO $creator;
    public ?PageSmallDTO $on_behalf_of_page;
    /** @var array<string, int> $reactionsCount */
    public array $reactionsCount;
    public int $timestamp;
    public CommentedDTO $commentedDTO;
    
    /**
     * @param array<string, int> $reactionsCount
     */
    function __construct(
        string $id, 
        string $text,
        ?AttachmentDTO $attachment,
        ProfileSmallDTO $owner,
        ?CreatorDTO $creator,
        ?PageSmallDTO $on_behalf_of_page,
        array $reactionsCount, 
        int $timestamp,
        CommentedDTO $commentedDTO
    ) {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->owner = $owner;
        $this->commentedDTO = $commentedDTO;
        $this->text = $text;
        $this->creator = $creator;
        $this->attachment = $attachment;
        $this->on_behalf_of_page = $on_behalf_of_page;
        $this->reactionsCount = $reactionsCount;
    }


}
