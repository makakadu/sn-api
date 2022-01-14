<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Photos\GroupPicture;

interface GroupPictureRepository extends \App\Domain\Repository {
    function getById(string $id): ?GroupPicture;
}