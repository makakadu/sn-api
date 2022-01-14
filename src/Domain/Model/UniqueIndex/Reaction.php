<?php
declare(strict_types=1);
namespace App\Domain\Model\UniqueIndex;

class Reaction {
    private int $id;
    private int $type;
    private ?Page $asPage;
    private int $pageId;
    private User $creator;
    private Comment $comment;
    
    public function __construct(Comment $comment, User $creator, int $type, ?Page $asPage) {
        $this->comment = $comment;
        $this->type = $type;
        $this->asPage = $asPage;
        $this->creator = $creator;
        $this->pageId = $asPage ? $asPage->id() : 0;
    }
    
}
