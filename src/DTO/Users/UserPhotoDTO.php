<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Common\PhotoDTO;

class UserPhotoDTO implements PhotoDTO {
    
    public string $id;
    /** @var array<int, string> $versions */
    public array $versions;
    public string $description;

    public CreatorDTO $creator;

    public int $timestamp;
    
    /** @var array<int, ReactionDTO> $reactions */
    public array $reactions;
    
    /** @var array<int> $reactions_count */
    public array $reactions_count;
    
//    public bool $isDeleted;
//    public bool $isDeletedByManager;
    
    /** @var array<int, CommentDTO> $comments */
    public array $comments = [];

    /**
     * @param array<int, string> $versions
     * @param array<int, ReactionDTO> $reactions
     * @param array<string, int> $reactions_count
     * @param array<int, CommentDTO> $comments
     */

    function __construct(
        string $id, 
        array $versions,
        string $description,
        CreatorDTO $creator, 
        int $timestamp,
        array $reactions, 
        array $reactions_count, 
        array $comments
    ) {
        $this->id = $id;
        $this->versions = $versions;
        $this->description = $description;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
        $this->reactions = $reactions;
        $this->reactions_count = $reactions_count;
        $this->comments = $comments;
    }

}
