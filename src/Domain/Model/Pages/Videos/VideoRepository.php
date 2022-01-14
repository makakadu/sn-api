<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Videos;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;

interface VideoRepository extends \App\Domain\Repository {
    function getById(string $id): ?Video;
}
