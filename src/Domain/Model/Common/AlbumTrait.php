<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use Doctrine\Common\Collections\Collection;
use Assert\Assertion;

trait AlbumTrait {
    protected string $name;
    protected string $description;
    protected \DateTime $deletedAt;
}
