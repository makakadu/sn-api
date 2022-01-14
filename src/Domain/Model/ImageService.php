<?php
declare(strict_types=1);
namespace App\Domain\Model;

use App\Domain\Model\Users\User\User;

interface ImageService {
    const JPEG_MIME = 'image/jpeg';
    const GIF_MIME = 'image/gif';
    const PNG_MIME = 'image/png';
    
    function imagePath() : string;
    
    /**
     * @return array<mixed>
     */
    function imagesPaths() : array;
    
    function hasUploadError() : bool;

    function uploadError() : ?string;

    function imageStorage() : ?string;

    /** @return array<mixed> */
    function createVersion(string $imagePath, string $version, string $path): array;
    
    /**
     * @return array<string>
     */
    function createStandardVersions(string $originalImagePath) : array;

    /**
     * @return array<string>
     */
    function createAvatarVersions(User $owner, string $albumId, string $avatarPath): array;
}
