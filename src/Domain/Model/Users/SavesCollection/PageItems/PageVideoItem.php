<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\PageItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Pages\Videos\Video;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class PageVideoItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?Video $video;
    private ?Page $page;
    private bool $onBehalfOfPage;
    private ?User $creator;
            
    function __construct(SavesCollection $collection, Video $video) {
        parent::__construct($collection, $video->createdAt, 'video');
        $this->originalId = $video->id();
        $this->video = $video;
        $this->creator = $video->creator();
        $this->page = $video->owningPage();
        $this->onBehalfOfPage = $video->onBehalfOfPage();
        $this->originalCreatedAt = $video->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->video;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitPageVideoItem($this);
    }
    
    public function video(): ?Video {
        return $this->video;
    }

    public function owningPage(): ?Page {
        return $this->page;
    }

    public function onBehalfOfPage(): bool {
        return $this->onBehalfOfPage;
    }

    public function creator(): ?User {
        return $this->creator;
    }

    public function videoId(): string {
        return $this->videoId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }

}