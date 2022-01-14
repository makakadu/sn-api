<?php

declare(strict_types=1);

namespace App\Infrastructure\Domain\Model;

use App\Domain\Model\Photo\Image;
use App\Domain\Model\Users\User\User;
use App\Application\SimpleImage;
use App\Domain\Model\ImageService;

class SimpleImageImageService implements ImageService {
//    const JPEG_MIME = 'image/jpeg';
//    const GIF_MIME = 'image/gif';
//    const PNG_MIME = 'image/png';

    private $errorsDesctiptions = [
        '1' => 'Размер принятого файла превысил максимально допустимый размер разрешенный сервером',
        '2' => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме',
        '3' => 'Загружаемый файл был получен только частично',
        '4' => 'Файл не был загружен',
        '5' => 'Отсутствует временная папка',
        '6' => 'Не удалось записать файл на диск',
        '7' => 'PHP-расширение остановило загрузку файла',
        '8' => 'Файл не был загружен на сервер по неизвестной причине'
    ];

    private $versionsSizes = [
        'original' => ['maxWidth' => 7000, 'maxHeight' => 7000],
        'extraSmall' => ['maxWidth' => 200, 'maxHeight' => 200],
        'small' => ['maxWidth' => 320, 'maxHeight' => 320],
        'medium' => ['maxWidth' => 400, 'maxHeight' => 400],
        'large' => ['maxWidth' => 604, 'maxHeight' => 604]
    ];
    
    private $uploadError;
    private string $imagePath = '';
    private array $multipleImagesPaths = [];
    private $imageStorage;
    private SimpleImage $imageManager;

    function __construct(string $imageStorage) {
        //print_r($_FILES);exit();
        $this->imageManager = new SimpleImage();
        $this->imageStorage = $imageStorage;

        if($this->checkImage()) {
            $this->imagePath = $_FILES['image']['tmp_name'];
        }
        if($this->checkMultipleImages()) {
            $this->multipleImagesPaths = $_FILES['images']['tmp_name'];
        }
    }
        
    function saveBase64AsFile($base64_string, $output_file) {
        $ifp = fopen( $output_file, 'wb' ); 
        fwrite( $ifp, base64_decode( $base64_string ) );
        fclose( $ifp );
    }
    
    function imagePath() : string {
        return $this->imagePath;
    }
    
    function imagesPaths() : array {
        return $this->multipleImagesPaths;
    }

    private function checkImage() {
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

    private function checkMultipleImages() {
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
        $newPath = "{$this->imageStorage}user_{$onwerId}/{$albumName}/{$newName}.{$extension}";
    }

    function generateNewImageName(string $albumName, int $ownerId) : string {}

    private function knowMIME(string $imagePath) : string {
        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
        $mime = \finfo_file($finfo, $imagePath);
        \finfo_close($finfo);
        return $mime ?? '';
    }

    function imageStorage() : ?string { return $this->imageStorage; }

    function imagesFromBase64(array $decodedFiles): array {
        $encoded = [];
        
        foreach($decodedFiles as $decoded) {
            $exploded = explode(',', $decoded);
            $string = count($exploded) > 1 ? $exploded[1] : $exploded[0];
            
            $firstSymbol = substr($string, 0, 1);
            switch ($firstSymbol) {
                case "/": $ext = "jpg"; break;
                case "i": $ext = "png"; break;
                default: throw new UnprocessableRequestException(['code' => 777, 'message' => 'invalid type, only jpg and png images allowed']);
            }
            $path = "/var/www/withSlim/public/images/storage/" . uniqid() . ".$ext";
            $this->saveBase64AsFile($string, $path);
            
            if($ext === 'jpg' && !imagecreatefromjpeg($path)) {
                unlink($path);
                throw new UnprocessableRequestException(['code' => 666, 'message' => 'image is corrupted']);
            } elseif($ext === 'png' && !imagecreatefrompng($path)) {
                unlink($path);
                throw new UnprocessableRequestException(['code' => 666, 'message' => 'image is corrupted']);
            }
            
            $encoded[] = $this->createStandardVersions($path);
        }
        
        return $encoded;
    }
    
    function createStandardVersions(string $originalImagePath) : array {
        $resized = [];
        foreach ($this->versionsSizes as $name => $version) {
            $resized[$name] = $this->createVersion($originalImagePath, $name, 'photos');
        }
        return $resized;
    }
    
//    private function moveImage(string $imagePath, string $newPath) {
//        $this->imageManager->load($imagePath);
//        //$newAvatarPath = $pathToAlbum . uniqid() . '.jpg';
//        $this->imageManager->save($newPath);
//
//        return str_replace($this->imageStorage, '', $newAvatarPath);
//    }
    
    /** @return array<mixed> */
    function createVersion(string $imagePath, string $version, string $path): array {
        $versionInfo = $this->versionsSizes[$version];
        
        $imageManager = $this->imageManager;
        $imageManager->load($imagePath);
        
        if($imageManager->getWidth() > $versionInfo['maxWidth']) {
            $imageManager->resizeToWidth($versionInfo['maxWidth']);
        }
        if($imageManager->getHeight() > $versionInfo['maxHeight']) {
            $imageManager->resizeToHeight($versionInfo['maxHeight']);
        }
        $uniqueId = uniqid();
        $photoName = $uniqueId . '.jpg';
        $photoWidth = $imageManager->getWidth();
        $photoHeight = $imageManager->getHeight();
        $imageManager->save("{$this->imageStorage}/{$photoName}");

        return [
            'name' => $photoName,
            'width' => $photoWidth,
            'height' => $photoHeight
        ];
    }
    
    /**
     * @return array<string>
     */
    function createAvatarVersions(User $owner, string $albumId, string $avatarPath): array {
        $inStorageAvatarVersions['small'] = $this->createVersion($avatarPath, 'extraSmall', 'avatars');
        $inStorageAvatarVersions['medium'] = $this->createVersion($avatarPath, 'medium', 'avatars');
        $inStorageAvatarVersions['large'] = $this->createVersion($avatarPath, 'large', 'avatars');

        return $inStorageAvatarVersions;
    }
//
//    private function findOutOwnerCatalog(User $owner): string {
//        if($owner instanceof User) {
//            return "user_{$owner->id()}";
//        } // } else if($owner instanceof App\Domain\Model\Group\Group) {
//        //     return "group_{$owner->getId()}";
//        // }
//        return 'kek';
//    }

//    private function moveImage(string $tempPath, string $pathToAlbum): string {
//        $this->imageManager->load($tempPath);
//        $newAvatarPath = $pathToAlbum . uniqid() . '.jpg';
//        $this->imageManager->save($newAvatarPath);
//
//        return str_replace($this->imageStorage, '', $newAvatarPath);
//    }

//    private function resizeImageVersionAndSave(string $pathToAlbum, string $originalImagePath): void {
//        $pathToAlbum = $originalImagePath;
//        $originalImagePath = $pathToAlbum;
////        $images = [];
////
////        foreach($this->versionsInfo as $imageInfo) {
////            $this->imageManager->load($originalImagePath);
////
////            if($this->imageManager->getWidth() > $imageInfo['maxWidth']) {
////                $this->imageManager->resizeToWidth($imageInfo['maxWidth']);
////            }
////
////            if($this->imageManager->getHeight() > $imageInfo['maxHeight']) {
////                $this->imageManager->resizeToHeight($imageInfo['maxHeight']);
////            }
////
////            $newPath = "{$pathToAlbum}/{$imageInfo['version']}" . uniqid() . '.jpg';
////
////            $this->imageManager->save($newPath);
////            $images[$imageInfo['version']] = str_replace($this->imageStorage, '', $newPath);
////        }
////
////        return $images;
//    }
}
