<?php
declare(strict_types=1);
namespace App\Domain\Model\Groups\Group;

use Assert\Assertion;

// Неважно приватная группа или публичная, никто кроме участников и менеджеров не может создавать какой-либо контент, максимум - реакции
// 
// Если группа публичная, то:
// 1. Видеть контент может кто угодно
// 2. Лайкать может кто угодно
// 3. Создавать посты, комменты, фото и видео могут только участники и менеджеры, можно запретить участникам создавать что-либо

class SectionsSettings {
    const DISABLED = 0;
    const CLOSED = 1;
    const RESTRICTED = 2;
    const OPENED = 3;
    
    private string $id;
    private Group $group;
    
    private int $wallSection = 3;
    private int $videosSections = 3;
    private int $photosSections = 3;
    
    public function __construct(Group $group) {
        $this->group = $group;
    }

    public function wallSection(): int {
        return $this->wallSection;
    }

    public function videosSection(): int {
        return $this->videosSections;
    }

    public function photosSection(): int {
        return $this->photosSections;
    }


}