<?php
declare(strict_types=1);
namespace App\DTO\Users;

use App\DTO\CreatorDTO;

class PictureDTO implements \App\DTO\Common\PhotoDTO {
    
    public string $id;
    /** @var array<int, string> $versions */
    public array $versions;
    public ?CreatorDTO $creator;
    public int $timestamp;
    /**
     * @param array<int, string> $versions
     */
    function __construct(
        string $id, 
        array $versions,
        ?CreatorDTO $creator, 
        int $timestamp
    ) {
        $this->id = $id;
        $this->versions = $versions;
        $this->creator = $creator;
        $this->timestamp = $timestamp;
    }

    
}
