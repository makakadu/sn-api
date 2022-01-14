<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\Photos\PagePicture;

use App\Domain\Model\Pages\Page\Page;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Pages\Photos\PagePicture\Comment\Comment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Pages\Comments\PageComment;

class PagePicture extends \App\Domain\Model\Pages\Photos\Photo implements Saveable, Shareable, Reactable {
    use \App\Domain\Model\Common\PictureTrait;
    
    private \DateTime $updatedAt;
    
    /** @var Collection<string, PageComment> $comments */
    private Collection $comments;
    
    /**
     * @param array<string> $versions
     */
    function __construct(User $creator, Page $owningPage, array $versions) {
        parent::__construct($creator, $owningPage, $versions);
        $this->setVersions($versions);
        $this->setCroppedVersions($versions);
        $this->updatedAt = new \DateTime('now');
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }
    
    /**
     * @return Collection<string, PageComment>
     */
    function comments(): Collection {
        /** @var Collection<string, PageComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
    /**
     * @param array<string> $versions
     */
    function edit(array $versions): void {
        $this->setVersions($versions);
    }
    
    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitPagePicture($this);
    }

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitPagePicture($this);
    }
}