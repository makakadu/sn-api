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
    private string $imgbbApiKey;
    
    /** @var array<array> $versionsSizes */
    private $versionsSizes = [
        'original' => ['maxWidth' => 7000, 'maxHeight' => 7000],
        'extraSmall' => ['maxWidth' => 200, 'maxHeight' => 200],
        'small' => ['maxWidth' => 320, 'maxHeight' => 320],
        'medium' => ['maxWidth' => 400, 'maxHeight' => 400],
        'large' => ['maxWidth' => 604, 'maxHeight' => 604]
    ];
    
    function __construct(string $imagesStorage, string $storageForPhotos, string $storageForVideos, string $imgbbApiKey) {
        $this->imageManager = new \App\Application\SimpleImage();
        $this->imagesStorage = $imagesStorage;
        $this->storageForPhotos = $storageForPhotos;
        $this->storageForVideos = $storageForVideos;
        $this->imgbbApiKey = $imgbbApiKey;
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
    
    function generateJPGPath(): string {
        return $this->imagesStorage.'temp/'. (string)\Ulid\Ulid::generate(true);
    }
    
    /** @return array<string> */
    function createPhotoVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo) : array {        
        if($photo->getError() !== 0) {
            throw new \Exception('Photo upload error');
        }
        $tempPhotoPath = $this->generateJPGPath();
        $this->toJPEG($photo, $tempPhotoPath);
        $storage = $this->storageForPhotos;
        $versions = $this->createPhotoVersions($tempPhotoPath, $storage);
        \unlink($tempPhotoPath);
        return $versions;
    }
    
    /** @return array<string> */
    function createPictureVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo, int $x, int $y, int $width) : array {
        $ext = $photo->getClientMediaType();
        $jpgPath = $this->generateJPGPath();
        if($ext === 'image/jpeg') {
            $photo->moveTo($jpgPath);
        } else {
            $this->toJPEG($photo, $jpgPath);
        }
        $sizes = getimagesize($jpgPath);
        if(($x + $width) > $sizes[0]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'x' and 'width' cannot be bigger than image width"]);
        } elseif(($y + $width) > $sizes[1]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'y' and 'width' cannot be bigger than image height"]);
        }
        $storage = $this->storageForPhotos;
        $standardVersions = $this->createPhotoVersions($jpgPath, $storage);
        $croppedVersions = $this->createCroppedVersions($jpgPath, $x, $y, $width, $storage);
        \unlink($jpgPath);
        
        $merged = \array_merge($standardVersions, $croppedVersions);
        return $merged;
    }
    
    /** @return array<string> */
    function createCroppedVersions(string $originalPath, int $x, int $y, int $width, string $storage): array {
        $name = uniqid();
        $versions['cropped_or'] = $storage.$name."_crlg.jpg";
        $versions['cropped_sm'] = $storage.$name."_crsm.jpg";
        
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
        $this->imageManager->save($versions['cropped_or']);
        $originalCropped = $this->upload(uniqid(), $versions['cropped_or']);
        \unlink($versions['cropped_or']); 
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(70);
        $this->imageManager->save($versions['cropped_sm']);
        $smallCropped = $this->upload(uniqid(), $versions['cropped_sm']);
        \unlink($versions['cropped_sm']);
        
        \unlink($temp);
        
        $created = [
            'cropped_original' => $originalCropped,
            'cropped_small' => $smallCropped
        ];
        return $created;
    }
    
    /** @return array<string> */
    function createCoverCroppedVersions(string $originalPath, int $x, int $y, float $width, float $height, string $storage): array {
        $name = uniqid();
        $versions['cropped_original'] = $storage.$name."_cror.jpg";
        $versions['cropped_small'] = $storage.$name."_crsm.jpg";

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
        $this->imageManager->save($versions['cropped_original']);
        $originalCropped = $this->upload(uniqid(), $versions['cropped_original']);
        \unlink($versions['cropped_original']); 
        
        $this->imageManager->load($temp);
        $this->imageManager->resizeToWidth(200);
        $this->imageManager->save($versions['cropped_small']);
        $smallCropped = $this->upload(uniqid(), $versions['cropped_small']);
        \unlink($versions['cropped_small']);
        
        \unlink($temp);
        
        $created = [
            'cropped_original' => $originalCropped,
            'cropped_small' => $smallCropped
        ];
        return $created;
    }
    
    /** @return array<string> */
    function createCoverVersionsFromUploaded(\Psr\Http\Message\UploadedFileInterface $photo, int $x, int $y, float $width) : array {        
        if($photo->getError() !== 0) {
            throw new \Exception('Photo upload error');
        }
        $ext = $photo->getClientMediaType();
        $jpgPath = $this->generateJPGPath();
        if($ext === 'image/jpeg') {
            $photo->moveTo($jpgPath);
        } else {
            $this->toJPEG($photo, $jpgPath);
        }
        $sizes = getimagesize($jpgPath);
        
        if(($x + $width) > $sizes[0]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'x' and 'width' cannot be bigger than image width"]);
        }
        $height = ($width / 100) * 33;
        if(($y + $height) > $sizes[1]) {
            throw new \App\Domain\Model\DomainExceptionAlt(['code' => 228, 'message' => "Sum of 'y' and 33% of 'width' cannot be bigger than image height"]);
        }
        $storage = $this->storageForPhotos;
        $standardVersions = $this->createPhotoVersions($jpgPath, $storage);
        $croppedVersions = $this->createCoverCroppedVersions($jpgPath, $x, $y, $width, $height, $storage);
        \unlink($jpgPath);
        $merged = \array_merge($standardVersions, $croppedVersions);
        return $merged;
    }
    
    /** @return array<string> */
    function createPhotoVersionsBasedOnPhoto(\App\Domain\Model\Common\PhotoInterface $photo): array {
        $original = $this->storageForPhotos.$photo->original();
        return $this->createPhotoVersions($original, $this->storageForPhotos);
    }
    
    /** @return array<string> */
    function createPhotoVersions(string $originalPath, string $storage): array {
        $photoName = uniqid();
        //echo $storage;exit();

        $versions = [
            "original" => $storage.$photoName.'_or.jpg',
            "medium" => $storage.$photoName."_md.jpg",
            "small" => $storage.$photoName."_sm.jpg",
        ];
        
        $this->createVersion($originalPath, "medium", $versions['medium']);
        $mediumLink = $this->upload(uniqid(), $versions['medium']);
        $this->createVersion($originalPath, "small", $versions['small']);
        $smallLink = $this->upload(uniqid(), $versions['small']);
        $this->imageManager->load($originalPath);
        $this->imageManager->save($versions['original']);
        $originalLink = $this->upload(uniqid(), $versions['original']);
        
        $originalDimensions = getimagesize($versions['original']);
        $mediumDimensions = getimagesize($versions['medium']);
        $smallDimensions = getimagesize($versions['small']);
        
        $created = [
            "original" => [
                'src' => $originalLink,
                'width' => $originalDimensions[0],
                'height' => $originalDimensions[1],
            ],
            "medium" => [
                'src' => $mediumLink,
                'width' => $mediumDimensions[0],
                'height' => $mediumDimensions[1],
            ],  
            "small" => [
                'src' => $smallLink,
                'width' => $smallDimensions[0],
                'height' => $smallDimensions[1],
            ],
        ];
        
        \unlink($versions['medium']);
        \unlink($versions['small']);
        \unlink($versions['original']);
        return $created;
    }
//    
//    function upload(string $name, string $path) {
//	$data = [
//            'image' => base64_encode(file_get_contents($path)),
//            'name' => $name
//        ];
//	$ch = curl_init();
//	curl_setopt($ch, CURLOPT_URL, 'https://api.imgbb.com/1/upload?key='.$this->imgbbApiKey);
//	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($ch, CURLOPT_POST, true);
//	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
////        print_r($data);exit();
//	$result = curl_exec($ch);
//        echo $result;exit();
//        return json_decode($result)->data->image->url;        
//    }
    
    function upload(string $name, string $path) {
        $file = base64_encode(file_get_contents($path));
        $client_id = '88b06657664e24d';
        $url = 'https://api.imgur.com/3/image.json';
        $headers = array("Authorization: Client-ID $client_id");
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => $file]);
	$result = curl_exec($ch);
        return json_decode($result)->data->link;
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