<?php
declare(strict_types=1);
namespace App\Application\Users\ProfilePicture\GetComments;

use App\Application\BaseResponse;
use App\Domain\Model\Users\ProfilePicture\Comments\Comment;

class GetCommentsResponse implements BaseResponse {
    /** @var array<array> $comments */
    public array $comments = [];
    
    /** @param array<Comment> $comments */
    public function __construct(array $comments) {
        
        foreach ($comments as $comment) {
            $creator = $comment->creator();
            
            $this->comments[] = [
                'id' => $comment->id(),
                'text' => $comment->text(),
                'creator' => [
                    'id' => $creator->id(),
                    'firstname' => $creator->firstName(),
                    'lastname' => $creator->lastName(),
                ],
                'date' => $comment->createdAt()->getTimestamp()
            ];
        }
    }
}
