<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

trait VideoTrait {
    //private string $name;
    private string $link;
    private string $hosting;
    
    //private bool $offComments;
    
    private string $smallPreview;
    private string $mediumPreview;
    private string $largePreview;
    private string $originalPreview;
    
    public function hosting(): string {
        return $this->hosting;
    }
    
    function previewSmall(): string {
        return $this->smallPreview;
    }

    function previewMedium(): string {
        return $this->mediumPreview;
    }

    function previewLarge(): string {
        return $this->largePreview;
    }
    
    function previewOriginal(): string {
        return $this->originalPreview;
    }

    function link(): string {
        return $this->link;
    }
    
    /** @param array<string> $previews */
    function setPreviews(array $previews): void {
        $this->smallPreview = $previews['small'];
        $this->mediumPreview = $previews['medium'];
        $this->largePreview = $previews['large'];
        $this->originalPreview = $previews['original'];
    }
}
