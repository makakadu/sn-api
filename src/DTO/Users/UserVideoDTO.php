<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Common\VideoDTO;

class UserVideoDTO implements VideoDTO {
    
    public string $type = 'user_video';
    public string $id;
    public string $preview;
    public string $name;
    public string $description;
    public string $link;

    public CreatorDTO $creator;

    public int $timestamp;
    
    /** @var array<int, ReactionDTO> $reactions */
    public array $reactions;
    
    /** @var array<int> $reactions_count */
    public array $reactions_count;
    
    /** @var array<int, CommentDTO> $comments */
    public array $comments = [];

    public int $comments_count;
    
    /**
     * @param array<int, ReactionDTO> $reactions
     * @param array<string, int> $reactions_count
     * @param array<int, CommentDTO> $comments
     */

    function __construct(
        string $id,
        string $preview,
        string $name,
        string $description,
        string $link,
        CreatorDTO $creator,
        int $timestamp,
        array $reactions,
        array $reactions_count,
        array $comments,
        int $comments_count
    ) {
        $this->id = $id;
        $this->preview = $preview;
        $this->name = $name;
        $this->description = $description;
        $this->link = $link;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
        $this->reactions = $reactions;
        $this->reactions_count = $reactions_count;
        $this->comments = $comments;
        $this->comments_count = $comments_count;
    }

}
