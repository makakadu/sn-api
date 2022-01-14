<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\VideoPlaylist;

use App\Domain\Model\EntityTrait;
use App\Domain\Model\Groups\Group\Group;
use Assert\Assertion;
use Doctrine\Common\Collections\Collection;
use Ulid\Ulid;
use App\Domain\Model\Groups\Videos\Video;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\DomainException;
use Doctrine\Common\Collections\ArrayCollection;

class VideoPlaylist {
    use EntityTrait;
    use \App\Domain\Model\Groups\GroupEntity;
    
    const MAX_PHOTOS_COUNT = 5000;
    
    private User $creator;
    private string $name;
    private string $description;
    
    private bool $offComments;
    private bool $isRestricted; // Только админы и редакторы могут добавлять фото

    private bool $isDeleted = false;
    private ?\DateTime $deletedAt;
    
    /** @var Collection <string, Video> $videos */
    private Collection $videos;

    function __construct(User $creator, Group $group, string $name, string $description, bool $offComments, bool $isRestricted) {
        Assertion::maxLength($name, 50);
        Assertion::maxLength($description, 300);
        $this->changeName($name);
        $this->changeDescription($description);
        $this->id = (string)Ulid::generate(true);
        $this->offComments = $offComments;
        $this->isRestricted = $isRestricted;
        $this->owningGroup = $group;
        $this->creator = $creator;
        $this->videos = new ArrayCollection();
    }
    
    function commentsAreDisabled(): bool {
        return $this->offComments;
    }
    
    function changeDescription(string $description): void {
        Assertion::maxLength($description, 300);
        $this->description = $description;
    }

    function changeName(string $name): void {
        Assertion::maxLength($name, 50);
        $this->name = $name;
    }

    function description(): string {
        return $this->description;
    }
    /**
     * @param array<int, string> $previews
     * @throws DomainException
     */
    function addVideo(User $creator, string $link, string $hosting, string $name, string $description, array $previews, bool $asGroup): void {
        if($asGroup && !$this->owningGroup->isManager($creator)) {
            throw new DomainException("Cannot add on behalf of group");
        }
        if($this->isRestricted && !$asGroup) { // Если добавить фото в альбом могут только менеджеры, то $asGroup должен быть true
            throw new DomainException("If album is restricted then video can be added only on behalf of group");
        }
        $video = new Video($this->owningGroup, $creator, $link, $name, $description, $hosting, $previews, $asGroup);
        $this->videos->add($video);
    }

}
