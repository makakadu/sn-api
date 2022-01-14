<?php
declare(strict_types=1);
namespace App\Domain\Model\TempPhoto;

use App\Domain\Model\Common\PhotoInterface;
use App\Domain\Model\Common\PhotoTrait;
use App\Domain\Model\Common\PhotoVisitor;
use App\Domain\Model\Common\PhotoVisitorVisitable;
use App\Domain\Model\EntityTrait;
use App\Domain\Model\Users\User\User;

class TempPhoto implements PhotoInterface, PhotoVisitorVisitable {
    use EntityTrait;
    use PhotoTrait;
            
    /** @param array<string> $versions */
    function __construct(User $creator, array $versions) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->creator = $creator;
        $this->setVersions($versions);
        $this->createdAt = new \DateTime("now");
    }

    public function accept(PhotoVisitor $visitor) {
        
    }

}