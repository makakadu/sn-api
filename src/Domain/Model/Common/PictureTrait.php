<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

trait PictureTrait {

    private string $croppedSmall;
    private string $croppedMedium;
    private string $croppedLarge;
    
    /** @param array<mixed> $versions */
    private function setCroppedVersions(array $versions): void {
        $this->croppedLarge = $versions['cropped_large'];
        $this->croppedMedium = $versions['cropped_medium'];
        $this->croppedSmall = $versions['cropped_small'];
    }

    function croppedSmall(): string { return $this->croppedSmall; }
    function croppedMedium(): string { return $this->croppedMedium; }
    function croppedLarge(): string { return $this->croppedLarge; }
}