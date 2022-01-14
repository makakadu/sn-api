<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos\PagePicture;

interface PagePictureRepository extends \App\Domain\Repository {
    function getById(string $id): ?PagePicture;
}