<?php
declare(strict_types=1);
namespace App\Domain\Model\UniqueIndex;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Comment {

    private int $id;
    private string $text;
    private User $creator;
    private Collection $reactions;
    
    public function __construct(string $text, User $creator) {
        $this->text = $text;
        $this->creator = $creator;
        $this->reactions = new ArrayCollection();
    }
    
    function react(User $creator, int $type, ?Page $asPage): void {
        $reaction = new Reaction($this, $creator, $type, $asPage);
        $this->reactions->add($reaction);
    }

}
