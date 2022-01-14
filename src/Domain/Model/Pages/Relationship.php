<?php
declare(strict_types=1);
namespace App\Domain\Model\Pages;

use App\Domain\Model\Pages\Page\Page;

class Relationship {
    
    private Page $page;
    private User $user;
    private bool $subscribed;
    private ?\DateTime $bannedAt = null;
    private ?\DateTime $banEnd = null;
    private ?string $position;
    
    public function __construct(Page $page, User $user, bool $subscribed, ?string $position) {
        $this->page = $page;
        $this->user = $user;
        $this->subscribed = $subscribed;
        $this->position = $position;
    }

}

/* 
 * Чтобы всё пошло не по плану нужно, чтобы процесс с созданием бана начался раньше, до сохранения Manager в БД, и закончился позже, после сохранения Manager в БД.
 * Если он начнётся после сохранения Manager в БД, то бан создан не будет, потому что нельзя банить менеджера.
 * Если бан сохранится раньше, чем Manager, то в процессе с созданием менеджера этот бан будет найден и деактивирован.
 * Я не знаю прав ли я, но мне кажется, что вероятность такого события крайне мала
 */