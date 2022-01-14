<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Application\Exceptions\UnprocessableRequestException;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\Photos\Photo;

class PhotoService {
    
    private \App\Application\SimpleImage $imageManager;
    private string $imagesStorage;
    private string $storageForPhotos;
    private string $storageForVideos;
    
    /** @var array<array> $versionsSizes */
    private $versionsSizes = [
        'original' => ['maxWidth' => 7000, 'maxHeight' => 7000],
        'extraSmall' => ['maxWidth' => 200, 'maxHeight' => 200],
        'small' => ['maxWidth' => 320, 'maxHeight' => 320],
        'medium' => ['maxWidth' => 400, 'maxHeight' => 400],
        'large' => ['maxWidth' => 604, 'maxHeight' => 604]
    ];
    
    function __construct(string $imagesStorage, string $storageForPhotos, string $storageForVideos) {
        $this->imageManager = new \App\Application\SimpleImage();
        $this->imagesStorage = $imagesStorage;
        $this->storageForPhotos = $storageForPhotos;
        $this->storageForVideos = $storageForVideos;
    }
    
    function imagesStorage(): string {
        return $this->imagesStorage;
    }
    
//    function createCopyFor(User $user, Photo $photo) {
//        $versions = $this->imageService->createStandardVersions($user, $user->id().'saved_photos', $photo->original());
//        $copy = new Photo($user, $versions);
//        return $copy;
//    }
    
    /**
     * @param array<array> $versions
     */
    function deleteFiles(array $versions): void {
        // к файлу, то файл удаляется
        foreach($versions as $version) {
            if(is_array($version)) {
                if(\file_exists($this->storageForPhotos . $version['src'])) {
                    \unlink($this->storageForPhotos . $version['src']);
                }
            } else {
                if(\file_exists($this->storageForPhotos . $version)) {
                    \unlink($this->storageForPhotos . $version);
                }
            }

        }
    }
    
//    function createCopyFor(User $user, \App\Domain\Model\Common\PhotoInterface $photo): \App\Domain\Model\Users\Photos\Photo {
//        $original = $photo->original();
//        $this->imageManager->load($original);
//        
//        $storage = $this->storage.$this->forPhotosFolderName;
//        
//        $versions = [
//            "original" => $storage.uniqid()."_or.jpg",
//            "extraSmall" => $storage.uniqid()."_es.jpg",
//            "small" => $storage.uniqid()."_sm.jpg",
//            "medium" => $storage.uniqid()."_md.jpg",
//            "large" => $storage.uniqid()."_lg.jpg"
//        ];
//        $this->imageManager->save($versions['original']);
//        $this->createVersion($versions['original'], "extraSmall", $versions['extraSmall']);
//        $this->createVersion($versions['original'], "small", $versions['small']);
//        $this->createVersion($versions['original'], "medium", $versions['medium']);
//        $this->createVersion($versions['original'], "large", $versions['large']);
//        
//        return new \App\Domain\Model\Users\Photos\Photo($user, $versions);
//    }
    
    /** @return array<string> */
    function createPhotoVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo) : array {        
        if($photo->getError() !== 0) {
            throw new \Exception('Photo upload error');
        }
        $tempPhotoPath = $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
        $this->toJPEG($photo, $tempPhotoPath);
        $storage = $this->storageForPhotos;
        $versions = $this->createPhotoVersions($tempPhotoPath, $storage);
        \unlink($tempPhotoPath);
        return $versions;
    }
    
    /** @return array<string> */
    function createPictureVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo, int $x, int $y, int $width) : array {        
        if($photo->getError() !== 0) {
            throw new \Exception('Photo upload error');
        }
        $tempPhotoPath = $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
        $this->toJPEG($photo, $tempPhotoPath);
        
        $sizes = getimagesize($tempPhotoPath);

        if(($x + $width) > $sizes[0]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'x' and 'width' cannot be bigger than image width"]);
        } elseif(($y + $width) > $sizes[1]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'y' and 'width' cannot be bigger than image height"]);
        }
        $storage = $this->storageForPhotos;
        $standardVersions = $this->createPhotoVersions($tempPhotoPath, $storage);
        $croppedVersions = $this->createCroppedVersions($tempPhotoPath, $x, $y, $width, $storage);
        \unlink($tempPhotoPath);
        
        return \array_merge($standardVersions, $croppedVersions);
    }
    
    /** @return array<string> */
    function createCoverVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo, int $x, int $y, float $width) : array {        
        if($photo->getError() !== 0) {
            throw new \Exception('Photo upload error');
        }
        $tempPhotoPath = $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
        $this->toJPEG($photo, $tempPhotoPath);
        
        $sizes = getimagesize($tempPhotoPath);
        
        if(($x + $width) > $sizes[0]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'x' and 'width' cannot be bigger than image width"]);
        }
        $height = ($width / 100) * 33;
        if(($y + $height) > $sizes[1]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'y' and 33% of 'width' cannot be bigger than image height"]);
        }
        $storage = $this->storageForPhotos;
        $standardVersions = $this->createPhotoVersions($tempPhotoPath, $storage);
        $croppedVersions = $this->createCoverCroppedVersions($tempPhotoPath, $x, $y, $width, $height, $storage);
        \unlink($tempPhotoPath);
        
        return \array_merge($standardVersions, $croppedVersions);
    }
    
    /** @return array<string> */
    function createPhotoVersionsBasedOnPhoto(\App\Domain\Model\Common\PhotoInterface $photo): array {
        $original = $this->storageForPhotos.$photo->original();
        return $this->createPhotoVersions($original, $this->storageForPhotos);
    }
    
    /** @return array<string> */
    function createPhotoVersions(string $path, string $storage): array {
        $photoName = uniqid();
        //echo $storage;exit();

        $versions = [
            "original" => $storage.$photoName.'_or.jpg',
            "large" => $storage.$photoName."_lg.jpg",
            "medium" => $storage.$photoName."_md.jpg",
            "small" => $storage.$photoName."_sm.jpg",
            "extraSmall" => $storage.$photoName."_es.jpg",
        ];
        $this->createVersion($path, "original", $versions['original']);
        $this->createVersion($path, "large", $versions['large']);
        $this->createVersion($path, "medium", $versions['medium']);
        $this->createVersion($path, "small", $versions['small']);
        $this->createVersion($path, "extraSmall", $versions['extraSmall']);
        
        $originalDimensions = getimagesize($versions['original']);
        $largeDimensions = getimagesize($versions['large']);
        $mediumDimensions = getimagesize($versions['medium']);
        $smallDimensions = getimagesize($versions['small']);
        $extraSmallDimensions = getimagesize($versions['extraSmall']);
        
        $versions = [
            "original" => [
                'src' => $photoName.'_or.jpg',
                'width' => $originalDimensions[0],
                'height' => $originalDimensions[1],
            ],  
            "large" => [
                'src' => $photoName.'_lg.jpg',
                'width' => $largeDimensions[0],
                'height' => $largeDimensions[1],
            ],  
            "medium" => [
                'src' => $photoName.'_md.jpg',
                'width' => $mediumDimensions[0],
                'height' => $mediumDimensions[1],
            ],  
            "small" => [
                'src' => $photoName.'_sm.jpg',
                'width' => $smallDimensions[0],
                'height' => $smallDimensions[1],
            ],  
            "extraSmall" => [
                'src' => $photoName.'_es.jpg',
                'width' => $extraSmallDimensions[0],
                'height' => $extraSmallDimensions[1],
            ],  
        ];
        
        return $versions;
    }
    
    /** @return array<string> */
    function createVideoPreviews(string $videoId, string $photoUrl): array {
        $name = \uniqid();
        $tempPath = $this->imagesStorage."temp/{$videoId}_{$name}.jpg";
        \file_put_contents($tempPath, \file_get_contents($photoUrl));
        $storage = $this->storageForVideos;
        return $this->createPhotoVersions($tempPath, $storage);
    }
    
    function createVersion(string $imagePath, string $version, string $destination): void {
        //echo $destination;exit();
        $versionInfo = $this->versionsSizes[$version];
        $imageManager = $this->imageManager;
        $imageManager->load($imagePath);
        
        if($imageManager->getWidth() > $versionInfo['maxWidth']) {
            $imageManager->resizeToWidth($versionInfo['maxWidth']);
        }
        if($imageManager->getHeight() > $versionInfo['maxHeight']) {
            $imageManager->resizeToHeight($versionInfo['maxHeight']);
        }
        $imageManager->save($destination);
    }
    
    /** @return array<string> */
    function createCoverCroppedVersions(string $originalPath, int $x, int $y, float $width, float $height, string $storage): array {
        $name = uniqid();
        $versions['cropped_large'] = $name."_crlg.jpg";
        $versions['cropped_medium'] = $name."_crmd.jpg";
        $versions['cropped_small'] = $name."_crsm.jpg";

        $image = imagecreatefromjpeg($originalPath);
        if(!$image) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => 'Invalid image']);
        }
        $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
        if(!$cropped) {
            throw new \Exception('Failed to crop photo');
        }
        imagedestroy($image);
        $temp = $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
        imagejpeg($cropped, $temp);
        imagedestroy($cropped);

        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth($width);
        $this->imageManager->save($storage.$versions['cropped_large']);
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(140);
        $this->imageManager->save($storage.$versions['cropped_medium']);
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(70);
        $this->imageManager->save($storage.$versions['cropped_small']);
        
        \unlink($temp);
        
        return $versions;
    }
    
    /** @return array<string> */
    function createCroppedVersions(string $originalPath, int $x, int $y, int $width, string $storage): array {
        $name = uniqid();
        $versions['cropped_large'] = $name."_crlg.jpg";
        $versions['cropped_medium'] = $name."_crmd.jpg";
        $versions['cropped_small'] = $name."_crsm.jpg";
        //print_r($storage);exit();
        
        //echo $originalPath;exit();
        $image = imagecreatefromjpeg($originalPath);
        if(!$image) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => 'Invalid image']);
        }
        $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $width]);
        if(!$cropped) {
            throw new \Exception('Failed to crop photo');
        }
        imagedestroy($image);
        $temp = $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
        imagejpeg($cropped, $temp);
        imagedestroy($cropped);

        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth($width);
        $this->imageManager->save($storage.$versions['cropped_large']);
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(140);
        $this->imageManager->save($storage.$versions['cropped_medium']);
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(70);
        $this->imageManager->save($storage.$versions['cropped_small']);
        
        \unlink($temp);
        
        return $versions;
    }
    
    function toJPEG(\Psr\Http\Message\UploadedFileInterface $photo, string $name): void {
        $ext = explode('/', $photo->getClientMediaType())[1];
        
        $tempPath = $this->imagesStorage . 'temp/' . uniqid();
        $photo->moveTo($tempPath);
        $this->deleteAndFailIfInvalidImage($tempPath, $ext);        
        
        if($ext === 'png') {
            $image = imagecreatefrompng($tempPath);
            imagejpeg($image, $name, 100);
            imagedestroy($image);
        } elseif($ext === "gif") {
            $image = imagecreatefromgif($tempPath);
            imagejpeg($image, $name, 100);
            imagedestroy($image);
        } elseif($ext === 'jpeg') {
            $image = imagecreatefromjpeg($tempPath);
            imagejpeg($image, $name, 100);
            imagedestroy($image);
        }
        unlink($tempPath);
    }
    
//    function copyPhoto(SimplePhoto $photo): void {
//        $versions = [
//            "original" => $this->storage.uniqid()."_es.jpg",
//            "extraSmall" => $this->storage.uniqid()."_es.jpg",
//            "small" => $this->storage.uniqid()."_sm.jpg",
//            "medium" => $this->storage.uniqid()."_md.jpg",
//            "large" => $this->storage.uniqid()."_lg.jpg"
//        ];
//    }
    
    function deleteAndFailIfInvalidImage(string $path, string $ext): void {
        try {
            $result = null;
            if($ext === "jpeg") {
                $result = \imagecreatefromjpeg($path);
            } elseif($ext === "png") {
                $result = \imagecreatefrompng($path);
            } elseif($ext === "gif") {
                $result = \imagecreatefromgif($path);
            }
            if(!$result) {
                unlink($path);
                throw new UnprocessableRequestException(12333, "Invalid image");
            }
        } catch (\Throwable $ex) {
            throw new UnprocessableRequestException(2222, $ex->getMessage());
        }
    }
    
    function failIfInappropriateExtension(\Psr\Http\Message\UploadedFileInterface $photo): void {
        $mediaType = $photo->getClientMediaType();
        if($mediaType != "image/jpeg" && $mediaType != "image/png" && $mediaType != "image/gif") {
            throw new \RuntimeException('File should be in jpg, png or gif format');
        }
    }
    
}