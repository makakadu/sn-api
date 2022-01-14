<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;

class UserAlbumPhotoDTO extends UserPhotoDTO {
    
    public string $album_id;
    public string $album_name;

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
        string $album_id,
        string $album_name,
        CreatorDTO $creator,
        int $timestamp,
        array $reactions, 
        array $reactions_count, 
        array $comments
    ) {
        parent::__construct($id, $versions, $description, $creator, $timestamp, $reactions, $reactions_count, $comments);
        $this->album_id = $album_id;
        $this->album_name = $album_name;
    }

}
