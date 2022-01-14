<?php
declare(strict_types=1);
namespace App\Domain\Model\DeepInheritance;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Boob {
    public string $id;
    public string $name;
    public ExtraChild1 $ec;
            
    function __construct(ExtraChild1 $ec, string $name) {
        $this->name = $name;
        $this->ec = $ec;
        $this->id = (string)\Ulid\Ulid::generate(true);
    }
}
