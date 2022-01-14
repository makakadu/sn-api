<?php
declare(strict_types=1);
namespace App\Domain\Model\Users\User;

class Settings {
    const LIGHT = 0;
    const DARK = 1;
    
    const EN = 'en';
    const RU = 'ru';
    const UK = 'uk';
    const DE = 'de';
    
    private string $id;
    private string $language;
    private int $theme;
    private User $user;
    //private bool $wallCommentsAreDisabled;
    private bool $showSavedInNewsFeed = true;

    function __construct(string $language, User $user) {
        $this->changeLanguage($language);
        $this->theme = self::DARK;
        $this->user = $user;
        //$this->wallCommentsAreDisabled = false;
    }
    
    function changeLanguage(string $language): void {
        //echo $language;exit();
        \Assert\Assertion::inArray(\strtolower($language), [self::EN, self::RU, self::UK, self::DE], "Incorrect language");
        $this->language = $language;
    }
    
    function language(): string {
        return $this->language;
    }
    
    function changeTheme(int $theme): void {
        \Assert\Assertion::between($theme, 0, 1, "Theme should be 0(light) or 1(dark)");
        $this->theme = $theme;
    }
    
    function theme(): int {
        return $this->theme;
    }
    
//    function changeWallCommentsAreDisabled(bool $wallCommentsAreDisabled): void {
//        $this->wallCommentsAreDisabled = $wallCommentsAreDisabled;
//    }
//    
//    function wallCommentsAreDisabled(): bool {
//        return $this->wallCommentsAreDisabled;
//    }
}
