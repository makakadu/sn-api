<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

use App\Domain\Model\Users\User\User;

trait PictureTrait {

    private string $croppedSmall;
    private string $croppedOriginal;
    
    /** @param array<mixed> $versions */
    private function setCroppedVersions(array $versions): void {
        $this->croppedOriginal = $versions['cropped_original'];
        $this->croppedSmall = $versions['cropped_small'];
    }

    function croppedSmall(): string { return $this->croppedSmall; }
    function croppedOriginal(): string { return $this->croppedOriginal; }
}