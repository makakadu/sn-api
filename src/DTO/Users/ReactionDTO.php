<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;
use App\DTO\Pages\PageSmallDTO;
use App\DTO\GroupSmallDTO;

class ReactionDTO {
    
    public string $id;
    public int $type;
    public ?CreatorDTO $creator;
    //public ?PageSmallDTO $on_behalf_of_page;
    public int $timestamp;
    
    function __construct(string $id, int $type, ?CreatorDTO $creator, int $timestamp) {
        $this->id = $id;
        $this->type = $type;
        $this->creator = $creator;
        //$this->on_behalf_of_page = $on_behalf_of_page;
        $this->timestamp = $timestamp;
    }

}
