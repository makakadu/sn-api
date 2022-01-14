<?php
declare(strict_types=1);
namespace App\Domain\Model\Common\Comments;

use App\Domain\Model\Users\User\User;

trait CommentTrait {
    
    //private User $creator;
    protected string $text;
            
//    function creator(): User { // Возможно лучше возвращать объект типа Author?
//        return $this->creator;
//    }
    
    function text(): string {
        return $this->text;
    }

    function changeText(string $text): void {
        \Assert\Assertion::maxLength($text, self::MAX_TEXT_LENGTH, "Max length of comment text - 500 characters");
        $this->text = $text;
    }
}
