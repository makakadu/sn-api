<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Photo\Image;
use App\Domain\Model\Users\User\User;
use App\Application\SimpleImage;

class ImageService { //implements ImageServiceInterface{
    const JPEG_MIME = 'image/jpeg';
    const GIF_MIME = 'image/gif';
    const PNG_MIME = 'image/png';

    /** @var array<string> $errorsDesctiptions */
    private array $errorsDesctiptions = [
        '1' => 'Размер принятого файла превысил максимально допустимый размер разрешенный сервером',
        '2' => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме',
        '3' => 'Загружаемый файл был получен только частично',
        '4' => 'Файл не был загружен',
        '5' => 'Отсутствует временная папка',
        '6' => 'Не удалось записать файл на диск',
        '7' => 'PHP-расширение остановило загрузку файла',
        '8' => 'Файл не был загружен на сервер по неизвестной причине'
    ];

    /** @var array<array> $versionsSizes */
    private array $versionsSizes = [
        'original' => ['maxWidth' => 7000, 'maxHeight' => 7000],
        'extraSmall' => ['maxWidth' => 200, 'maxHeight' => 200],
        'small' => ['maxWidth' => 320, 'maxHeight' => 320],
        'medium' => ['maxWidth' => 400, 'maxHeight' => 400],
        'large' => ['maxWidth' => 604, 'maxHeight' => 604]
    ];
    
    private string $uploadError;
    private string $imagePath = '';
    /** @var array<string> $multipleImagesPaths */
    private array $multipleImagesPaths = [];
    private string $imageStorage;
    private SimpleImage $imageManager;

    function __construct(string $imageStorage) {
        $this->imageManager = new SimpleImage();
        $this->imageStorage = $imageStorage;

        if($this->checkImage()) {
            $this->imagePath = $_FILES['image']['tmp_name'];
        }
        if($this->checkMultipleImages()) {
            $this->multipleImagesPaths = $_FILES['images']['tmp_name'];
        }
    }
    
    function imagePath() : string {
        return $this->imagePath;
    }
    
    /**
     * @return array<string>
     */
    function imagesPaths() : array {
        return $this->multipleImagesPaths;
    }

    private function checkImage(): bool {
        if(!isset($_FILES['image'])) {
            //$this->uploadError = 'Image didn\'t load';
            return false;
        } else if($_FILES['image']['error'] > 0) {
            $this->uploadError = $this->errorsDesctiptions[$_FILES['image']['error']];
            return false;
        } else if(!is_uploaded_file($_FILES['image']['tmp_name'])) {
            $this->uploadError = $this->errorsDesctiptions[8];
            return false;
        }
        return true;
    }

    private function checkMultipleImages(): bool {
        if(!isset($_FILES['images'])) {
            //$this->uploadError = 'Images didn\'t load';
            return false;
        }

        foreach ($_FILES['images']['error'] as $key => $value) {
            if($value > 0) {
                $this->uploadError = $this->errorsDesctiptions[$value];
                return false;
            }
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $path) {
            if(!is_uploaded_file($path)) {
                $this->uploadError = $this->errorsDesctiptions[8];
                // нужно хорошо протестировать
                return false;
            }
        }

        return true;
    }

    function hasUploadError() : bool { return isset($this->uploadError); }

    function uploadError() : ?string { return $this->uploadError; }

    function generateNewImagePath(string $albumName, int $ownerId, string $newName, string $extension) : string {
        $newPath = "{$this->imageStorage}user_{$ownerId}/{$albumName}/{$newName}.{$extension}";
        return $newPath;
    }

    function generateNewImageName(string $albumName, int $ownerId) : string { return '';}

//    private function knowMIME(string $imagePath) : string {
//        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
//        $mime = \finfo_file($finfo, $imagePath);
//        \finfo_close($finfo);
//        return $mime ?? '';
//    }

    function imageStorage() : ?string { return $this->imageStorage; }

    /**
     * @return array<string>
     */
    function createStandardVersions(\App\Domain\Model\Users\User\User $owner, string $albumId, string $originalImagePath) : array {
        $ownerStorageCatalog = $this->findOutOwnerCatalog($owner);
        $pathToAlbum = "{$this->imageStorage}/images/{$ownerStorageCatalog}/{$albumId}";

        $resized = [];
        foreach ($this->versionsSizes as $name => $version) {
            $resized[$name] = $this->createVersion($originalImagePath, $name, 'photos');
        }
        return $resized;
    }
    
//    private $versionsSizes = [
//        'original' => ['maxWidth' => 7000, 'maxHeight' => 7000],
//        'extraSmall' => ['maxWidth' => 200, 'maxHeight' => 200],
//        'small' => ['maxWidth' => 320, 'maxHeight' => 320],
//        'medium' => ['maxWidth' => 400, 'maxHeight' => 400],
//        'large' => ['maxWidth' => 604, 'maxHeight' => 604]
//    ];
    
    /**
     * @return array<string>
     */
    function createStandardVersions2(\Psr\Http\Message\UploadedFileInterface $photo) : array {
        $photoMediaType = $photo->getClientMediaType();
        if(!$photoMediaType) {
            throw new \App\Application\Exceptions\UnprocessableRequestException(123, "\Psr\Http\Message\UploadedFileInterface returned null");
        }
        $ext = explode('/', $photoMediaType)[1];

        $originalPath = "../../public/images/lolkek/".uniqid()."_or.$ext";
        $extraSmallPath = "../../public/images/lolkek/".uniqid()."_es.$ext";
        $smallPath = "../../public/images/lolkek/".uniqid()."_sm.$ext";
        $mediumPath = "../../public/images/lolkek/".uniqid()."_md.$ext";
        $largePath = "../../public/images/lolkek/".uniqid()."_lg.$ext";
        
        $photo->moveTo($originalPath);
        $this->createVersion($originalPath, "extraSmall", $extraSmallPath);
        $this->createVersion($originalPath, "small", $smallPath);
        $this->createVersion($originalPath, "medium", $mediumPath);
        $this->createVersion($originalPath, "large", $largePath);
        
        return [
            "original" => $originalPath,
            "extraSmall" => $extraSmallPath,
            "small" => $smallPath,
            "medium" => $mediumPath,
            "large" => $largePath
        ];
    }

    function createVersion(string $imagePath, string $version, string $path): string {
        $versionInfo = $this->versionsSizes[$version];
        
        $imageManager = $this->imageManager;
        $imageManager->load($imagePath);
        
        if($imageManager->getWidth() > $versionInfo['maxWidth']) {
            $imageManager->resizeToWidth($versionInfo['maxWidth']);
        }
        if($imageManager->getHeight() > $versionInfo['maxHeight']) {
            $imageManager->resizeToHeight($versionInfo['maxHeight']);
        }

        $imageManager->save($path);
        return $path;
    }
    /**
     * @return array<string>
     */
    function createAvatarVersions(\App\Domain\Model\Users\User\User $owner, string $albumId, string $avatarPath): array {
        $inStorageAvatarVersions['small'] = $this->createVersion($avatarPath, 'extraSmall', 'avatars');
        $inStorageAvatarVersions['medium'] = $this->createVersion($avatarPath, 'medium', 'avatars');
        $inStorageAvatarVersions['large'] = $this->createVersion($avatarPath, 'large', 'avatars');

        return $inStorageAvatarVersions;
    }

    private function findOutOwnerCatalog(\App\Domain\Model\Users\User\User $owner): string {
        return "user_{$owner->id()}";
    }

}
