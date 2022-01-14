<?php
declare(strict_types=1);
namespace App\Domain\Model\UniqueIndex;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class User {
    
    private int $id;
    private string $name;
    private Collection $comments;
    
    public function __construct(string $name) {
        $this->name = $name;
        $this->comments = new ArrayCollection();
    }

    public function createComment(string $text): void {
        $comment = new Comment($text, $this);
        $this->comments->add($comment);
    }
}
