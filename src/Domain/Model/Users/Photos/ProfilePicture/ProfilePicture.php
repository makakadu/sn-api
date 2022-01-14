<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\Photos\ProfilePicture;

use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Photos\ProfilePicture\Comment\Comment;
use App\Domain\Model\Saveable;
use App\Domain\Model\Common\Reactable;
use App\Domain\Model\Common\Shares\Shareable;
use App\Domain\Model\Users\Comments\ProfileComment;

class ProfilePicture extends \App\Domain\Model\Users\Photos\Photo implements Saveable, Shareable, Reactable {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Common\PictureTrait;
    
    public \DateTime $updatedAt;
    
    /** @var Collection<string, Comment> $comments */
    private Collection $comments;
    
    /**
     * @param array<string> $versions
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
    
    function getUpdatedAt(): \DateTime { // Без этого геттера с именно таким названием иногда происходит ошибка в Doctrine
        return $this->updatedAt;
    }
    
    /** @return Collection<string, ProfileComment> */
    function comments(): Collection {
        /** @var Collection<string, ProfileComment> $comments */
        $comments = $this->comments;
        return $comments;
    }
    
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

    public function acceptSaveableVisitor(\App\Domain\Model\SaveableVisitor $visitor) {
        return $visitor->visitProfilePicture($this);
    }

    public function acceptReactableVisitor(\App\Domain\Model\Common\ReactableVisitor $visitor) {
        return $visitor->visitProfilePicture($this);
    }

}