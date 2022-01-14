<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\VideoPlaylist;

use Doctrine\Common\Collections\Collection;
use App\Domain\Model\Users\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use App\Domain\Model\Users\Videos\Video;

class VideoPlaylist {
    use \App\Domain\Model\EntityTrait;

    /** @var Collection<string, Video> $videos */
    private Collection $videos;
    private User $user;
    private ?\DateTime $deletedAt = null;
    private string $name;
    private string $description;
    private PrivacySettings $privacy;
    
    /**
     * @param array<mixed> $privacy
     */
    function __construct(User $user, string $name, string $description, array $privacy) {
        $this->id = (string) \Ulid\Ulid::generate(true);
        $this->videos = new ArrayCollection();
        $this->user = $user;
        $this->name = $name;
        $this->description = $description;
        $this->createdAt = new \DateTime("now");
        $this->privacy = new PrivacySettings($this, $privacy['access_level'], $privacy['lists']);
    }

    
    function addVideo(Video $video): void {
        $video->addPlaylist($this);
        $this->videos->add($video);
    }
    
    function user(): User {
        return $this->user;
    }

    function name(): string {
        return $this->name;
    }

    function description(): string {
        return $this->description;
    }


    
}
