<?php

declare(strict_types=1);

namespace App\Application;

interface ImageServiceInterface {
    
    /**
     * @return array<string>
     */
    function getMultipleImagePaths() : array;
    function hasUploadError() : bool;
    function getUploadError() : ?string;
    function generateNewImagePath(string $albumName, int $ownerId, string $newName, string $extension) : string;
    function generateNewImageName(string $albumName, int $ownerId) : string;
    function getImageStorage() : ?string;
}