<?php
declare(strict_types=1);
namespace App\DTO\Common;

use App\DTO\CreatorDTO;

class PhotoAttachmentDTO extends AttachmentDTO {
   
    public string $id;
    /** @var array<int, string> $versions */
    public array $versions;
    public CreatorDTO $creator;
    public int $timestamp;
    public ?string $commentId;
    /**
     * @param array<int, string> $versions
     */
    function __construct(
        string $id, 
        array $versions,
        CreatorDTO $creator, 
        int $timestamp,
        ?string $commentId
    ) {
        $this->id = $id;
        $this->versions = $versions;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
        $this->commentId = $commentId;
    }
    
}
