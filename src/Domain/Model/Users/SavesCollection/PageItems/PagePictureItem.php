<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\SavesCollection\PageItems;

use App\Domain\Model\Users\SavesCollection\SavedItem;
use App\Domain\Model\Pages\Photos\PagePicture\PagePicture;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\SavesCollection\SavesCollection;

class PagePictureItem extends SavedItem {

    /*
     * свойства $photo, $owningGroup и $creator являются nullable, потому что фото, группа и создатель могут быть удалены. $creator также nullable из-за того, что фото может быть
     * создано от имени группы
     */
    private ?PagePicture $picture;
    private ?Page $page;
            
    function __construct(SavesCollection $collection, PagePicture $picture) {
        parent::__construct($collection, $picture->createdAt, 'photo');
        $this->originalId = $picture->id();
        $this->picture = $picture;
        $this->page = $picture->owningPage();
        $this->originalCreatedAt = $picture->createdAt();
    }

    public function saved(): ?\App\Domain\Model\Saveable {
        return $this->picture;
    }

    public function acceptItemVisitor(\App\Domain\Model\Users\SavesCollection\SavedItemVisitor $visitor) {
        return $visitor->visitPagePictureItem($this);
    }
    
    public function picture(): ?PagePicture {
        return $this->picture;
    }

    public function owningPage(): ?Page {
        return $this->page;
    }

    public function pictureId(): string {
        return $this->pictureId;
    }

    public function originalCreatedAt(): \DateTime {
        return $this->originalCreatedAt;
    }

}