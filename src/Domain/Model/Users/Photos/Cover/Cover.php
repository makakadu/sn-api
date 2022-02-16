<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\Cover;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Cover extends \App\Domain\Model\Users\Photos\Photo  {
    use \App\Domain\Model\EntityTrait;

    private string $croppedSmall;
    private string $croppedOriginal;
    
    /**
     * @param array<mixed> $versions
     */
    function __construct(User $user, array $versions) {
        parent::__construct($user, $versions);
        $this->id = (string) \Ulid\Ulid::generate(true);
        //$this->user = $user;
        $this->setCroppedVersions($versions);
        $this->createdAt = new \DateTime('now');
        $this->updatedAt = new \DateTime('now');
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    /** @param array<mixed> $versions */
    private function setCroppedVersions(array $versions): void {
        $this->croppedOriginal = $versions['cropped_original'];
        $this->croppedSmall = $versions['cropped_small'];
    }
    
    function croppedSmall(): string { return $this->croppedSmall; }
    function croppedOriginal(): string { return $this->croppedOriginal; }
    
    /** @return array<mixed> */
    function versions(): array {
        return [
            'original' => $this->original(),
            'small' => $this->small(),
            'medium' => $this->medium(),
            'cropped_small' => $this->croppedSmall(),
            'cropped_original' => $this->croppedOriginal()
        ];
    }
}