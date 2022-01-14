<?php
declare(strict_types=1);
namespace App\DTO\Common;

use App\DTO\Common\CommentedDTO;

class UnaccessibleReactedCommentDTO implements ReactedCommentDTO {
    public string $id;
    public int $timestamp;
    public CommentedDTO $commentedDTO;
    
    public function __construct(string $id, int $timestamp, CommentedDTO $commentedDTO) {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->commentedDTO = $commentedDTO;
    }
}