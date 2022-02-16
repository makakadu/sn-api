<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

trait PhotoTrait {
    //private User $creator;
    
    private array $original;
    private array $small;
    private array $medium;
    
    function original(): array { return $this->original; }
    function small(): array { return $this->small; }
    function medium(): array { return $this->medium; }
    
//    function creator(): User {
//        return $this->creator;
//    }
    
    function width(): int {
        return 100;
    }
    
    function height(): int {
        return 100;
    }
    
    /** @param array<array> $versions */
    private function setVersions(array $versions): void {
        /* Нужно ли делать проверку массива $versions? Мне кажется, что нет, если нет какого-то ключа, то возникнет ошибка. Если будут лишние ключи, то ничего
         страшного.
         А вот проверку наличия файлов стоит сделать. Но так же я думаю, что это не обязательно, мне кажется, что стоит доверить классу PhotoService, который
         создаёт эти версии. Его делаю я, для него будут написаны тесты. + самое главное - это то, что работа с файлами это не задача классов,
         которые представляют фото. Короче говоря это излишне.
         * 
         */
        $this->original = $versions['original'];
        $this->small = $versions['small'];
        $this->medium = $versions['medium'];
    }
    
    /** @return array<mixed> */
    function versions(): array {
        return [
            $this->original,
            $this->medium,
            $this->small,
        ];
    }
}
/*
//    private function setAvatarVersions(array $avatarVersions) {
//        if(count($avatarVersions) !== 3) {
//            throw new \InvalidArgumentException("Incorrect count of avatar versions");
//        }
//
//        foreach ($avatarVersions as $version => $path) {
//            if(!isset($path)) {
//                throw new \InvalidArgumentException("Путь к {$version} изображения отсутствует");
//            } // } else if(!file_exists($path)) {
//            //     throw new \InvalidArgumentException("{$version} изображения по заданому пути отсутствует");
//            // }
//        }
//
//        //$this->checkAvatarSizes($avatarVersions);
//
//        $this->avatar = $avatarVersions['avatar'];
//        $this->miniAvatar = $avatarVersions['miniAvatar'];
//        $this->microAvatar = $avatarVersions['microAvatar'];
//    }
//
//    private function checkRequiredImageSizes($imageVersions) {
//        $avatarSize = getimagesize($avatarVersions['avatar']);
//        $miniAvatarSize = getimagesize($avatarVersions['miniAvatar']);
//        $microAvatarSize = getimagesize($avatarVersions['microAvatar']);
//
//        if($avatarSize[0] != 200 || ($avatarSize[1] < 200 || $avatarSize[1] > 300)) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина аватара должна быть 200px, высота от 200px до 300 px');
//        } else if($miniAvatarSize[0] != 100 || $miniAvatarSize[1] != 100) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина и ширина мини аватара должны быть 100px');
//        } else if($microAvatarSize[0] != 50 || $microAvatarSize[1] != 50) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина и ширина микро аватара должны быть 50px');
//        }
//    }
//
//    private function checkAvatarSizes($avatarVersions) {
//        $avatarSize = getimagesize($avatarVersions['avatar']);
//        $miniAvatarSize = getimagesize($avatarVersions['miniAvatar']);
//        $microAvatarSize = getimagesize($avatarVersions['microAvatar']);
//
//        if($avatarSize[0] != 200 || ($avatarSize[1] < 200 || $avatarSize[1] > 300)) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина аватара должна быть 200px, высота от 200px до 300 px');
//        } else if($miniAvatarSize[0] != 100 || $miniAvatarSize[1] != 100) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина и ширина мини аватара должны быть 100px');
//        } else if($microAvatarSize[0] != 50 || $microAvatarSize[1] != 50) {
//            throw new \InvalidArgumentException('Неправильные размеры изображения.'
//                    . ' Ширина и ширина микро аватара должны быть 50px');
//        }
//    }
//
//    private function failIfNotImage($path) : void {
//        $mime = $this->knowMIME($path);
//
//        if($mime != self::JPEG_MIME && $mime != self::GIF_MIME && $mime != self::PNG_MIME) {
//            throw new \InvalidArgumentException('По пути {$path} находится не изображение');
//        }
//    }
//
//    private function knowMIME(string $imagePath) : ?string {
//        $finfo = \finfo_open(FILEINFO_MIME_TYPE);
//        $mime = \finfo_file($finfo, $imagePath);
//        \finfo_close($finfo);
//
//        return $mime ?? null;
//    }
//
//    function move(string $imagesStorage, string $imagePath, int $ownerId) {
//        $extension = explode('/', $this->mime)[1];
//        $newName = '';
//        $newPath = $imagesStorage . "user{$ownerId}/avatar/" . "photoname.{$extension}";
//        move_uploaded_file($this->path, $newPath);
//        $this->path = $newPath;
//    }
 * 
 */