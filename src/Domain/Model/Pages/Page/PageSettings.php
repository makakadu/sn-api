<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Page;

class PageSettings {
    private bool $videosSection = true;
    private bool $photosSection = true;
    private bool $discussionSection = true;
    
    public function videosSection(): bool {
        return $this->videosSection;
    }

    public function photosSection(): bool {
        return $this->photosSection;
    }

    public function discussionSection(): bool {
        return $this->discussionSection;
    }
}