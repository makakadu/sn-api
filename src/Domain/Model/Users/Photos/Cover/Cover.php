<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\Cover;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class Cover extends \App\Domain\Model\Users\Photos\Photo  {
    use \App\Domain\Model\EntityTrait;

    private string $croppedSmall;
    private string $croppedMedium;
    private string $croppedLarge;
    
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
        $this->croppedLarge = $versions['cropped_large'];
        $this->croppedMedium = $versions['cropped_medium'];
        $this->croppedSmall = $versions['cropped_small'];
    }
    
    function croppedSmall(): string { return $this->croppedSmall; }
    function croppedMedium(): string { return $this->croppedMedium; }
    function croppedLarge(): string { return $this->croppedLarge; }
    
    /** @return array<mixed> */
    function versions(): array {
        return [
            'original' => $this->original(),
            'small' => $this->small(),
            'extra_small' => $this->extraSmall(),
            'medium' => $this->medium(),
            'large' => $this->large(),
            'cropped_small' => $this->croppedSmall(),
            'cropped_medium' => $this->croppedMedium(),
            'cropped_large' => $this->croppedLarge()
        ];
    }
}