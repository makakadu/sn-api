<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\ProfilePicture;

interface ProfilePictureRepository extends \App\Domain\Repository {
    function getById(string $id): ?ProfilePicture;
}