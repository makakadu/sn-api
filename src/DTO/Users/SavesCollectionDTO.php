<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;

class SavesCollectionDTO implements \App\DTO\Common\DTO {
    public string $id;
    public string $name;
    public string $description;

    public CreatorDTO $creator;
    public int $timestamp;
    public int $items_count;
    
    public function __construct(string $id, string $name, string $description, CreatorDTO $creator, int $timestamp, int $items_count) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
        $this->items_count = $items_count;
    }


}
