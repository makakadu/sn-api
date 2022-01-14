<?php
declare(strict_types=1);
namespace App\Domain\Model\DeepInheritance;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class ExtraChild1 extends Child1 {
    
    /** @var Collection<string, Boob> $boobs*/
    public Collection $boobs;
            
    function __construct(string $name) {
        $this->name = $name;
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->kek = 'extra child 1';
        $this->lol = $name;
        $this->boobs = new ArrayCollection();
        $this->boobs->add(new Boob($this, 'first'));
        $this->boobs->add(new Boob($this, 'second'));
    }
}
