<?php
declare(strict_types=1);
namespace App\Domain\Model\UniqueIndex;

class Page {

    private int $id;
    private string $name;
    
    public function __construct(string $name) {
        $this->name = $name;
    }
    
    public function id(): int {
        return $this->id;
    }

}
