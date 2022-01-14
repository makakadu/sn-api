<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Group;

use Assert\Assertion;
use App\Domain\Model\Users\User\User;

// Неважно приватная группа или публичная, никто кроме участников и менеджеров не может создавать какой-либо контент, максимум - реакции
// 
// Если группа публичная, то:
// 1. Видеть контент может кто угодно
// 2. Лайкать может кто угодно
// 3. Создавать посты, комменты, фото и видео могут только участники и менеджеры, можно запретить участникам создавать что-либо

class Settings {
    const DISABLED = 0;
    const CLOSED = 1;
    const RESTRICTED = 2;
    const OPENED = 3;
    
    const MEMBERS_AND_MANAGERS = 2;
    const MANAGERS = 1;
    //const PAGES_AND_PROFILES = 3;
    //const PROFILES = 4;
    
    const ASC = 1;
    const DESC = 2;
    const MOST_INTERESTING = 3;
    
    private int $id;
    private Group $_group;
    
    private bool $autoApprovals = false; /* Если автоподтверждение включают, то все существующие запросы НЕ подтверждаются, только новые.
     В фейсбуке можно настроить каких пользователей можно принимать автоматически, но я пока что не буду использовать такое, пусть останется на будущее. */
    /*
     * Допустим есть группа, в которой админы не часто активны, а пользователь хочет вступить туда, чтобы что-то написать. Если менеджеры
     * долго не будут его принимать, то это не круто. Можно дать возможность вступать без подтверждения менеджерами, то есть, чтобы пользователю можно было создать какой-то контент в группе,
     * ему достаточно нажать на кнопку "Вступить в группу". Это формальность, это почти то же самое, что и дать не участнику создавать контент.
     * 
     * Мне кажется, что стоит дать возможность админам разрешить не участникам создавать контент, просто дать возможность, пускай админы сами решают как лучше.
     * Также я думаю, что нужно разрешить вступать в группу без подтверждения менеджера, в этом есть логика.
     * 
     * Самым оптимальным вариантом будет свойство $private, если оно false, то все могут стать участниками группы и создавать контент даже не будучи участником. Запрещать создавать
     * контент, когда пользователь не является участником, нет смысла, если можно вступить в группу без подтверждения.
     */
    private int $commentsSorting = self::MOST_INTERESTING;
    private bool $visible; // Группу могут найти только участники
    private bool $private;
    //private bool $messagesAreDisabled;
    //private bool $commentsFromCommunitiesAreDisabled;
    private int $wallSection = self::OPENED;
    private int $videosSection = self::OPENED;
    private int $photosSection = self::OPENED;
    //private int $discussionSection;

    function __construct(Group $group, bool $private, bool $visible) {
        $this->_group = $group;
        $this->private = $private;
        $this->visible = $visible;
    }
    
    function autoApprovalsAreEnabled(): bool {
        return $this->autoApprovals;
    }
    
    function enableAutoApprovals(User $initiator): void {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to enable auto approvals");
        $this->autoApprovals = true;
    }
    
    function disableAutoApprovals(User $initiator): void {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to disable auto approvals");
        $this->autoApprovals = false;
    }
    
    function changeCommentsSorting(User $initiator, int $sortingType): void {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to make change comments sorting");
        Assertion::inArray($sortingType, [self::ASC, self::DESC, self::MOST_INTERESTING], "Incorrect group comments sorting type");
        $this->commentsSorting = $sortingType;
    }
    
    function hide(User $initiator) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to hide group");
        $this->visible = false;
    }
    
    function unhide(User $initiator) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to unhide group");
        $this->visible = true;
    }

    function isVisible(): bool {
        return $this->visible;
    }
    
    function makePrivate(User $initiator) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to make group private");
        $this->private = true;
    }
    
    function makePublic(User $initiator) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to make group public");
        $this->private = false;
    }

    function isPrivate(): bool {
        return $this->private;
    }

    function editWallSection(User $initiator, int $type) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to change type of wall section");
        Assertion::inArray($type, [self::DISABLED, self::CLOSED, self::RESTRICTED, self::OPENED], "Incorrect wall section type");
        $this->wallSection = $type;
    }
    
    function editPhotosSection(User $initiator, int $type) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to change type of photos section");
        Assertion::inArray($type, [self::DISABLED, self::CLOSED, self::OPENED], "Incorrect photos section type");
        $this->photosSection = $type;
    }
    
    function editVideosSection(User $initiator, int $type) {
        $this->failIfCannotEditGroupSettings($initiator, "No rights to change type of videos section");
        Assertion::inArray($type, [self::DISABLED, self::CLOSED, self::OPENED], "Incorrect videos section type");
        $this->videosSection = $type;
    }
    
    function wallSection(): int {
        return $this->wallSection;
    }

    function videosSection(): int {
        return $this->videosSection;
    }

    function photosSection(): int {
        return $this->photosSection;
    }

    function failIfCannotEditGroupSettings(User $initiator, string $failMessage): void {
        if(!$this->_group->isAdmin($initiator) && !$this->_group->owner()->equals($initiator)) {
            throw new \App\Domain\Model\DomainException($failMessage);
        }
    }
}