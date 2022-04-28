<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\ProfilePicture;

use App\Domain\Model\Users\User\User;

// Этот класс нужен для сокращения количества запросов в БД. Дело в том, что из-за наследования ProfilePicture извлекается только лениво(LAZY), то есть нужен
// отдельный запрос, если нужно извлечь множество пользователей, например 20, то это + 20 запросов. А если использовать этот класс, то всё будет загружаться
// вместе с User.
class CurrentProfilePicture {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\PictureTrait;
    use \App\Domain\Model\Common\PhotoTrait;
    
    public \DateTime $originalUpdatedAt;
    public \DateTime $originalCreatedAt;
    private User $user;
    private string $originalPictureId;

    function update(ProfilePicture $picture): void {
        $this->originalPictureId = $picture->id();
        $this->setVersions($picture->versions());
        $this->setCroppedVersions($picture->versions());
        $this->originalCreatedAt = $picture->createdAt();
        $this->originalUpdatedAt = $picture->getUpdatedAt();
    }

    function __construct(ProfilePicture $picture) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->originalPictureId = $picture->id();
        $this->setVersions($picture->versions());
        $this->setCroppedVersions($picture->versions());
        $this->originalCreatedAt = $picture->createdAt();
        $this->originalUpdatedAt = $picture->getUpdatedAt();
        $this->user = $picture->user();
    }
    
    /** @return array<mixed> */
    function versions(): array {
        return [
            'original' => $this->original(),
            'medium' => $this->medium(),
            'small' => $this->small(),
            'cropped_original' => $this->croppedOriginal(),
            'cropped_small' => $this->croppedSmall(),
        ];
    }
    
    function createdAt(): \DateTime {
        return $this->originalCreatedAt;
    }

}