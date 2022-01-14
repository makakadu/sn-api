<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

interface ReactionsTypes {
    const LIKE = 1;
    const DISLIKE = 2;
    const WOW = 3;
    const HAHA = 4;
    const CRYING = 5;
    const ANGRY = 6;
    const SPUE = 7;
    const CRINGE = 8;
    const HEART_EYES = 9;
    const POKER = 10;
}
