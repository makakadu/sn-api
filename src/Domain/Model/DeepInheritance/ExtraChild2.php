<?php
declare(strict_types=1);
namespace App\Domain\Model\DeepInheritance;

class ExtraChild2 extends Child1 {
            
    function __construct(string $name) {
        $this->name = $name;
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->kek = 'extra child 2';
        $this->lol = null;
    }
}
