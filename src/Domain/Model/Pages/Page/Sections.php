<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Page;

class Sections {
    private bool $videos = true;
    private bool $photos = true;
    private bool $discussion = true;
    
    function openVideosSection(): void {
        
    }
    
    public function videosSection(): bool {
        return $this->videos;
    }

    public function photosSection(): bool {
        return $this->photos;
    }

    public function discussionSection(): bool {
        return $this->discussion;
    }

}