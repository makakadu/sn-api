<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages\VideoPlaylist;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Pages\Videos\Video;
use App\Domain\Model\Pages\Page\Page;

class VideoPlaylist {
    use \App\Domain\Model\EntityTrait;
    use \App\Domain\Model\Pages\PageEntity;
    
    /** @var Collection<string, Video> $videos */
    private Collection $videos;
    private User $creator;
    private ?\DateTime $deletedAt = null;
    private string $name;
    private string $description;
    
    function __construct(User $creator, Page $owningPage, string $name, string $description) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->videos = new ArrayCollection();
        $this->creator = $creator;
        $this->owningPage = $owningPage;
        $this->name = $name;
        $this->description = $description;
        $this->createdAt = new \DateTime("now");
    }
    
    function addVideo(Video $video): void {
        //$video->addPlaylist($this);
        $this->videos->add($video);
    }
    
    function creator(): User {
        return $this->creator;
    }

    function name(): string {
        return $this->name;
    }

    function description(): string {
        return $this->description;
    }
    
}
